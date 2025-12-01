<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Conversation;
use App\Models\Escrow;
use App\Models\Listing;
use App\Models\Watchlist;
use Illuminate\Http\Request;

class BidController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'My Bids';
        $user = auth()->user();

        $bids = Bid::where('user_id', $user->id)
            ->with(['listing.images', 'listing.seller'])
            ->when($request->status, function ($q, $status) {
                return $q->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());

        return view('Template::user.bid.index', compact('pageTitle', 'bids'));
    }

    public function place(Request $request, $listingId)
    {
        $user = auth()->user();
        $listing = Listing::active()
            ->auction()
            ->where('auction_end', '>', now())
            ->findOrFail($listingId);

        // Cannot bid on own listing
        if ($listing->user_id === $user->id) {
            $notify[] = ['error', 'You cannot bid on your own listing'];
            return back()->withNotify($notify);
        }

        $request->validate([
            'amount' => 'required|numeric|min:' . $listing->minimum_bid,
            'max_bid' => 'nullable|numeric|min:' . $request->amount,
        ]);

        $amount = $request->amount;
        $maxBid = $request->max_bid ?? 0;

        // Validate bid amount
        if ($amount < $listing->minimum_bid) {
            $notify[] = ['error', 'Bid must be at least ' . showAmount($listing->minimum_bid)];
            return back()->withNotify($notify);
        }

        // Mark previous winning bid as outbid
        if ($listing->highest_bidder_id && $listing->highest_bidder_id !== $user->id) {
            Bid::where('listing_id', $listing->id)
                ->where('status', Status::BID_WINNING)
                ->update(['status' => Status::BID_OUTBID]);

            // Notify outbid user
            $outbidUser = $listing->highestBidder;
            if ($outbidUser) {
                notify($outbidUser, 'BID_OUTBID', [
                    'listing_title' => $listing->title,
                    'your_bid' => showAmount($listing->current_bid),
                    'new_bid' => showAmount($amount),
                ]);
            }
        }

        // Create bid
        $bid = new Bid();
        $bid->bid_number = getTrx();
        $bid->listing_id = $listing->id;
        $bid->user_id = $user->id;
        $bid->amount = $amount;
        $bid->max_bid = $maxBid;
        $bid->is_auto_bid = $maxBid > 0;
        $bid->status = Status::BID_WINNING;
        $bid->ip_address = $request->ip();
        $bid->save();

        // Update listing
        $listing->current_bid = $amount;
        $listing->highest_bidder_id = $user->id;
        $listing->total_bids = $listing->bids()->count();
        $listing->save();

        // Notify seller
        notify($listing->seller, 'NEW_BID_RECEIVED', [
            'listing_title' => $listing->title,
            'bid_amount' => showAmount($amount),
            'bidder' => $user->username,
            'current_highest' => showAmount($listing->current_bid),
        ]);

        // Notify watchlist users
        $watchlistUsers = Watchlist::where('listing_id', $listing->id)
            ->where('user_id', '!=', $user->id)
            ->where('notify_bid', true)
            ->with('user')
            ->get();

        foreach ($watchlistUsers as $watch) {
            if ($watch->user) {
                notify($watch->user, 'WATCHED_LISTING_NEW_BID', [
                    'listing_title' => $listing->title,
                    'bid_amount' => showAmount($amount),
                ]);
            }
        }

        $notify[] = ['success', 'Bid placed successfully'];
        return back()->withNotify($notify);
    }

    public function buyNow(Request $request, $listingId)
    {
        $user = auth()->user();
        $listing = Listing::active()
            ->where('buy_now_price', '>', 0)
            ->findOrFail($listingId);

        // Cannot buy own listing
        if ($listing->user_id === $user->id) {
            $notify[] = ['error', 'You cannot buy your own listing'];
            return back()->withNotify($notify);
        }

        // Create winning bid
        $bid = new Bid();
        $bid->bid_number = getTrx();
        $bid->listing_id = $listing->id;
        $bid->user_id = $user->id;
        $bid->amount = $listing->buy_now_price;
        $bid->status = Status::BID_WON;
        $bid->is_buy_now = true;
        $bid->ip_address = $request->ip();
        $bid->save();

        // Mark other bids as lost
        Bid::where('listing_id', $listing->id)
            ->where('id', '!=', $bid->id)
            ->whereIn('status', [Status::BID_ACTIVE, Status::BID_WINNING])
            ->update(['status' => Status::BID_LOST]);

        // Update listing as sold
        $listing->status = Status::LISTING_SOLD;
        $listing->winner_id = $user->id;
        $listing->final_price = $listing->buy_now_price;
        $listing->current_bid = $listing->buy_now_price;
        $listing->highest_bidder_id = $user->id;
        $listing->sold_at = now();
        $listing->save();

        // Create escrow for the transaction
        $escrow = $this->createEscrow($listing, $user, $listing->buy_now_price);
        $listing->escrow_id = $escrow->id;
        $listing->save();

        // Notify seller
        notify($listing->seller, 'LISTING_SOLD_BUY_NOW', [
            'listing_title' => $listing->title,
            'amount' => showAmount($listing->buy_now_price),
            'buyer' => $user->username,
        ]);

        // Notify buyer
        notify($user, 'PURCHASE_BUY_NOW', [
            'listing_title' => $listing->title,
            'amount' => showAmount($listing->buy_now_price),
        ]);

        $notify[] = ['success', 'Purchase successful! Please proceed to payment.'];
        return redirect()->route('user.escrow.details', $escrow->id)->withNotify($notify);
    }

    public function cancel($id)
    {
        $user = auth()->user();
        $bid = Bid::where('user_id', $user->id)
            ->whereIn('status', [Status::BID_ACTIVE, Status::BID_WINNING])
            ->with('listing')
            ->findOrFail($id);

        // Check if auction allows bid cancellation
        $listing = $bid->listing;
        if ($listing->auction_end && $listing->auction_end->diffInHours(now()) < 24) {
            $notify[] = ['error', 'Cannot cancel bid within 24 hours of auction end'];
            return back()->withNotify($notify);
        }

        $wasWinning = $bid->status === Status::BID_WINNING;

        $bid->status = Status::BID_CANCELLED;
        $bid->save();

        // If this was the winning bid, find new highest
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

        $notify[] = ['success', 'Bid cancelled successfully'];
        return back()->withNotify($notify);
    }

    public function wonAuctions()
    {
        $pageTitle = 'Won Auctions';
        $user = auth()->user();

        $bids = Bid::where('user_id', $user->id)
            ->where('status', Status::BID_WON)
            ->with(['listing.images', 'listing.seller', 'listing.escrow'])
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());

        return view('Template::user.bid.won', compact('pageTitle', 'bids'));
    }

    private function createEscrow($listing, $buyer, $amount)
    {
        $seller = $listing->seller;
        $general = gs();

        // Calculate charges
        $percentCharge = $general->percent_charge ?? 0;
        $fixedCharge = $general->fixed_charge ?? 0;
        $charge = ($amount * $percentCharge / 100) + $fixedCharge;

        if ($charge > ($general->charge_cap ?? 0) && $general->charge_cap > 0) {
            $charge = $general->charge_cap;
        }

        $escrow = new Escrow();
        $escrow->escrow_number = getTrx();
        $escrow->seller_id = $seller->id;
        $escrow->buyer_id = $buyer->id;
        $escrow->creator_id = $buyer->id;
        $escrow->amount = $amount;
        $escrow->charge = $charge;
        $escrow->buyer_charge = $charge;
        $escrow->seller_charge = 0;
        $escrow->charge_payer = Status::CHARGE_PAYER_BUYER;
        $escrow->title = 'Purchase: ' . $listing->title;
        $escrow->details = "Escrow for listing: {$listing->title}\nListing #: {$listing->listing_number}";
        $escrow->status = Status::ESCROW_ACCEPTED;
        $escrow->save();

        // Create conversation for escrow
        $conversation = new Conversation();
        $conversation->escrow_id = $escrow->id;
        $conversation->buyer_id = $buyer->id;
        $conversation->seller_id = $seller->id;
        $conversation->save();

        return $escrow;
    }
}

