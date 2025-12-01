<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Listing;
use Illuminate\Http\Request;

class BidController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'All Bids';
        $bids = $this->getBids($request)->paginate(getPaginate());
        return view('admin.bid.index', compact('pageTitle', 'bids'));
    }

    public function winning(Request $request)
    {
        $pageTitle = 'Winning Bids';
        $bids = $this->getBids($request)->where('status', Status::BID_WINNING)->paginate(getPaginate());
        return view('admin.bid.index', compact('pageTitle', 'bids'));
    }

    public function won(Request $request)
    {
        $pageTitle = 'Won Bids';
        $bids = $this->getBids($request)->where('status', Status::BID_WON)->paginate(getPaginate());
        return view('admin.bid.index', compact('pageTitle', 'bids'));
    }

    public function details($id)
    {
        $pageTitle = 'Bid Details';
        $bid = Bid::with(['listing.images', 'listing.seller', 'user'])->findOrFail($id);
        return view('admin.bid.details', compact('pageTitle', 'bid'));
    }

    public function cancel($id)
    {
        $bid = Bid::whereIn('status', [Status::BID_ACTIVE, Status::BID_WINNING])
            ->with('listing')
            ->findOrFail($id);

        $wasWinning = $bid->status === Status::BID_WINNING;
        $listing = $bid->listing;

        $bid->status = Status::BID_CANCELLED;
        $bid->save();

        // If was winning, find new highest
        if ($wasWinning) {
            $newHighest = Bid::where('listing_id', $listing->id)
                ->whereIn('status', [Status::BID_ACTIVE, Status::BID_OUTBID])
                ->orderBy('amount', 'desc')
                ->first();

            if ($newHighest) {
                $newHighest->status = Status::BID_WINNING;
                $newHighest->save();

                $listing->current_bid = $newHighest->amount;
                $listing->highest_bidder_id = $newHighest->user_id;
            } else {
                $listing->current_bid = 0;
                $listing->highest_bidder_id = 0;
            }

            $listing->total_bids = $listing->bids()->whereNotIn('status', [Status::BID_CANCELLED])->count();
            $listing->save();
        }

        // Notify bidder
        notify($bid->user, 'BID_CANCELLED_ADMIN', [
            'listing_title' => $listing->title,
            'bid_amount' => showAmount($bid->amount),
        ]);

        $notify[] = ['success', 'Bid cancelled successfully'];
        return back()->withNotify($notify);
    }

    public function processAuctionEnd($listingId)
    {
        $listing = Listing::activeAuctions()
            ->where('auction_end', '<=', now())
            ->findOrFail($listingId);

        $winningBid = Bid::where('listing_id', $listing->id)
            ->where('status', Status::BID_WINNING)
            ->with('user')
            ->first();

        if (!$winningBid) {
            // No bids - mark as expired
            $listing->status = Status::LISTING_EXPIRED;
            $listing->save();

            notify($listing->user, 'AUCTION_ENDED_NO_BIDS', [
                'listing_title' => $listing->title,
            ]);

            $notify[] = ['success', 'Auction ended with no bids'];
            return back()->withNotify($notify);
        }

        // Check if reserve was met
        if ($listing->reserve_price > 0 && $listing->current_bid < $listing->reserve_price) {
            $listing->status = Status::LISTING_EXPIRED;
            $listing->save();

            // Mark all bids as lost
            Bid::where('listing_id', $listing->id)
                ->whereIn('status', [Status::BID_ACTIVE, Status::BID_WINNING, Status::BID_OUTBID])
                ->update(['status' => Status::BID_LOST]);

            notify($listing->user, 'AUCTION_ENDED_RESERVE_NOT_MET', [
                'listing_title' => $listing->title,
                'highest_bid' => showAmount($listing->current_bid),
                'reserve_price' => showAmount($listing->reserve_price),
            ]);

            $notify[] = ['success', 'Auction ended - reserve not met'];
            return back()->withNotify($notify);
        }

        // Winner found
        $winningBid->status = Status::BID_WON;
        $winningBid->save();

        // Mark other bids as lost
        Bid::where('listing_id', $listing->id)
            ->where('id', '!=', $winningBid->id)
            ->whereIn('status', [Status::BID_ACTIVE, Status::BID_OUTBID])
            ->update(['status' => Status::BID_LOST]);

        // Update listing
        $listing->status = Status::LISTING_SOLD;
        $listing->winner_id = $winningBid->user_id;
        $listing->final_price = $winningBid->amount;
        $listing->sold_at = now();
        $listing->save();

        // Create escrow
        $escrow = $this->createEscrow($listing, $winningBid->user, $winningBid->amount);
        $listing->escrow_id = $escrow->id;
        $listing->save();

        // Notify winner
        notify($winningBid->user, 'AUCTION_WON', [
            'listing_title' => $listing->title,
            'winning_bid' => showAmount($winningBid->amount),
        ]);

        // Notify seller
        notify($listing->user, 'AUCTION_ENDED_SOLD', [
            'listing_title' => $listing->title,
            'final_price' => showAmount($winningBid->amount),
            'winner' => $winningBid->user->username,
        ]);

        // Update user stats
        $listing->user->increment('total_sales');
        $listing->user->increment('total_sales_value', $winningBid->amount);
        $winningBid->user->increment('total_purchases');

        $notify[] = ['success', 'Auction processed successfully'];
        return back()->withNotify($notify);
    }

    private function getBids($request)
    {
        return Bid::with(['listing', 'user'])
            ->when($request->search, function ($q, $search) {
                return $q->where(function ($query) use ($search) {
                    $query->where('bid_number', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('username', 'LIKE', "%{$search}%");
                        })
                        ->orWhereHas('listing', function ($q) use ($search) {
                            $q->where('title', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->orderBy('created_at', 'desc');
    }

    private function createEscrow($listing, $buyer, $amount)
    {
        $seller = $listing->seller;
        $general = gs();

        $percentCharge = $general->percent_charge ?? 0;
        $fixedCharge = $general->fixed_charge ?? 0;
        $charge = ($amount * $percentCharge / 100) + $fixedCharge;

        if ($charge > ($general->charge_cap ?? 0) && $general->charge_cap > 0) {
            $charge = $general->charge_cap;
        }

        $escrow = new \App\Models\Escrow();
        $escrow->escrow_number = getTrx();
        $escrow->seller_id = $seller->id;
        $escrow->buyer_id = $buyer->id;
        $escrow->creator_id = $buyer->id;
        $escrow->amount = $amount;
        $escrow->charge = $charge;
        $escrow->buyer_charge = $charge;
        $escrow->seller_charge = 0;
        $escrow->charge_payer = Status::CHARGE_PAYER_BUYER;
        $escrow->title = 'Auction Won: ' . $listing->title;
        $escrow->details = "Escrow for auction: {$listing->title}\nListing #: {$listing->listing_number}";
        $escrow->status = Status::ESCROW_ACCEPTED;
        $escrow->save();

        $conversation = new \App\Models\Conversation();
        $conversation->escrow_id = $escrow->id;
        $conversation->buyer_id = $buyer->id;
        $conversation->seller_id = $seller->id;
        $conversation->save();

        return $escrow;
    }
}

