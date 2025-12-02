<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Escrow;
use App\Models\Listing;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'My Offers';
        $user = auth()->user();

        $offers = Offer::where('buyer_id', $user->id)
            ->with(['listing.images', 'seller'])
            ->when($request->status, function ($q, $status) {
                return $q->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());

        return view('Template::user.offer.index', compact('pageTitle', 'offers'));
    }

    public function received(Request $request)
    {
        $pageTitle = 'Received Offers';
        $user = auth()->user();

        $offers = Offer::where('seller_id', $user->id)
            ->with(['listing.images', 'buyer'])
            ->when($request->status, function ($q, $status) {
                return $q->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());

        return view('Template::user.offer.received', compact('pageTitle', 'offers'));
    }

    public function make(Request $request, $listingId)
    {
        $user = auth()->user();
        $listing = Listing::active()
            ->fixedPrice()
            ->with('seller')
            ->findOrFail($listingId);

        // Cannot make offer on own listing
        if ($listing->user_id === $user->id) {
            $notify[] = ['error', 'You cannot make an offer on your own listing'];
            return back()->withNotify($notify);
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'message' => 'nullable|string|max:1000',
        ]);

        // Check for pending offer from same user
        $existingOffer = Offer::where('listing_id', $listing->id)
            ->where('buyer_id', $user->id)
            ->whereIn('status', [Status::OFFER_PENDING, Status::OFFER_COUNTERED])
            ->first();

        if ($existingOffer) {
            $notify[] = ['error', 'You already have a pending offer on this listing'];
            return back()->withNotify($notify);
        }

        $offer = new Offer();
        $offer->offer_number = getTrx();
        $offer->listing_id = $listing->id;
        $offer->buyer_id = $user->id;
        $offer->seller_id = $listing->user_id;
        $offer->amount = $request->amount;
        $offer->message = $request->message;
        $offer->status = Status::OFFER_PENDING;
        $offer->expires_at = now()->addDays(7);
        $offer->save();

        // Notify seller
        notify($listing->seller, 'NEW_OFFER_RECEIVED', [
            'listing_title' => $listing->title,
            'offer_amount' => showAmount($request->amount),
            'asking_price' => showAmount($listing->asking_price),
            'buyer' => $user->username,
            'message' => $request->message ?? 'No message',
        ]);

        $notify[] = ['success', 'Offer submitted successfully'];
        return back()->withNotify($notify);
    }

    public function accept($id)
    {
        try {
            $user = auth()->user();
            
            // Lock offer and listing to prevent concurrent acceptances
            $offer = Offer::lockForUpdate()
                ->where('seller_id', $user->id)
                ->whereIn('status', [Status::OFFER_PENDING, Status::OFFER_COUNTERED])
                ->with(['listing' => function($q) {
                    $q->lockForUpdate();
                }, 'buyer'])
                ->findOrFail($id);

            $listing = $offer->listing;

            // Check if listing is already sold
            if ($listing->status === Status::LISTING_SOLD) {
                $notify[] = ['error', 'This listing has already been sold'];
                return back()->withNotify($notify);
            }

            DB::beginTransaction();
            
            try {
                // Reject other pending offers
                Offer::where('listing_id', $listing->id)
                    ->where('id', '!=', $offer->id)
                    ->whereIn('status', [Status::OFFER_PENDING, Status::OFFER_COUNTERED])
                    ->update([
                        'status' => Status::OFFER_REJECTED,
                        'rejection_reason' => 'Another offer was accepted',
                        'responded_at' => now(),
                    ]);

                // Accept this offer
                $offer->status = Status::OFFER_ACCEPTED;
                $offer->responded_at = now();

                // Update listing
                $finalAmount = $offer->counter_amount > 0 ? $offer->counter_amount : $offer->amount;
                $listing->status = Status::LISTING_SOLD;
                $listing->winner_id = $offer->buyer_id;
                $listing->final_price = $finalAmount;
                $listing->sold_at = now();

                // Create escrow
                $escrow = $this->createEscrow($listing, $offer->buyer, $finalAmount);
                $listing->escrow_id = $escrow->id;
                $listing->save();

                $offer->escrow_id = $escrow->id;
                $offer->save();

                // Update user stats
                $user->increment('total_sales');
                $user->increment('total_sales_value', $finalAmount);
                $offer->buyer->increment('total_purchases');

                DB::commit();

                // Notify buyer (outside transaction)
                notify($offer->buyer, 'OFFER_ACCEPTED', [
                    'listing_title' => $listing->title,
                    'offer_amount' => showAmount($finalAmount),
                ]);

                $notify[] = ['success', 'Offer accepted. Escrow has been created.'];
                return redirect()->route('user.escrow.details', $escrow->id)->withNotify($notify);
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Offer acceptance failed: ' . $e->getMessage(), [
                    'offer_id' => $id,
                    'user_id' => $user->id,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Offer acceptance error: ' . $e->getMessage());
            $notify[] = ['error', 'An error occurred while accepting the offer. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    public function reject(Request $request, $id)
    {
        $user = auth()->user();
        $offer = Offer::where('seller_id', $user->id)
            ->whereIn('status', [Status::OFFER_PENDING, Status::OFFER_COUNTERED])
            ->with(['listing', 'buyer'])
            ->findOrFail($id);

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $offer->status = Status::OFFER_REJECTED;
        $offer->rejection_reason = $request->reason;
        $offer->responded_at = now();
        $offer->save();

        // Notify buyer
        notify($offer->buyer, 'OFFER_REJECTED', [
            'listing_title' => $offer->listing->title,
            'offer_amount' => showAmount($offer->amount),
            'reason' => $request->reason ?? 'No reason provided',
        ]);

        $notify[] = ['success', 'Offer rejected'];
        return back()->withNotify($notify);
    }

    public function counter(Request $request, $id)
    {
        $user = auth()->user();
        $offer = Offer::where('seller_id', $user->id)
            ->where('status', Status::OFFER_PENDING)
            ->with(['listing', 'buyer'])
            ->findOrFail($id);

        $request->validate([
            'counter_amount' => 'required|numeric|min:1',
            'counter_message' => 'nullable|string|max:1000',
        ]);

        $offer->status = Status::OFFER_COUNTERED;
        $offer->counter_amount = $request->counter_amount;
        $offer->counter_message = $request->counter_message;
        $offer->countered_at = now();
        $offer->expires_at = now()->addDays(7);
        $offer->save();

        // Notify buyer
        notify($offer->buyer, 'OFFER_COUNTERED', [
            'listing_title' => $offer->listing->title,
            'original_amount' => showAmount($offer->amount),
            'counter_amount' => showAmount($request->counter_amount),
            'message' => $request->counter_message ?? 'No message',
        ]);

        $notify[] = ['success', 'Counter offer sent'];
        return back()->withNotify($notify);
    }

    public function acceptCounter($id)
    {
        $user = auth()->user();
        $offer = Offer::where('buyer_id', $user->id)
            ->where('status', Status::OFFER_COUNTERED)
            ->with(['listing', 'seller'])
            ->findOrFail($id);

        $listing = $offer->listing;
        $finalAmount = $offer->counter_amount;

        // Accept counter offer
        $offer->status = Status::OFFER_ACCEPTED;
        $offer->responded_at = now();
        $offer->save();

        // Update listing
        $listing->status = Status::LISTING_SOLD;
        $listing->winner_id = $user->id;
        $listing->final_price = $finalAmount;
        $listing->sold_at = now();
        $listing->save();

        // Reject other offers
        Offer::where('listing_id', $listing->id)
            ->where('id', '!=', $offer->id)
            ->whereIn('status', [Status::OFFER_PENDING, Status::OFFER_COUNTERED])
            ->update([
                'status' => Status::OFFER_REJECTED,
                'rejection_reason' => 'Listing has been sold',
                'responded_at' => now(),
            ]);

        // Create escrow
        $escrow = $this->createEscrow($listing, $user, $finalAmount);
        $listing->escrow_id = $escrow->id;
        $listing->save();

        $offer->escrow_id = $escrow->id;
        $offer->save();

        // Update user stats
        $offer->seller->increment('total_sales');
        $offer->seller->increment('total_sales_value', $finalAmount);
        $user->increment('total_purchases');

        // Notify seller
        notify($offer->seller, 'COUNTER_OFFER_ACCEPTED', [
            'listing_title' => $listing->title,
            'amount' => showAmount($finalAmount),
            'buyer' => $user->username,
        ]);

        $notify[] = ['success', 'Counter offer accepted. Escrow has been created.'];
        return redirect()->route('user.escrow.details', $escrow->id)->withNotify($notify);
    }

    public function cancel($id)
    {
        $user = auth()->user();
        $offer = Offer::where('buyer_id', $user->id)
            ->whereIn('status', [Status::OFFER_PENDING, Status::OFFER_COUNTERED])
            ->findOrFail($id);

        $offer->status = Status::OFFER_CANCELLED;
        $offer->save();

        $notify[] = ['success', 'Offer cancelled'];
        return back()->withNotify($notify);
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

        // Create conversation
        $conversation = new Conversation();
        $conversation->escrow_id = $escrow->id;
        $conversation->buyer_id = $buyer->id;
        $conversation->seller_id = $seller->id;
        $conversation->save();

        return $escrow;
    }
}

