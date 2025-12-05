<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingCategory;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'All Listings';
        $listings = $this->getListings($request)->paginate(getPaginate());
        return view('admin.listing.index', compact('pageTitle', 'listings'));
    }

    public function pending(Request $request)
    {
        $pageTitle = 'Pending Listings';
        $listings = $this->getListings($request)->where('status', Status::LISTING_PENDING);
        $listings = $listings->paginate(getPaginate());
        return view('admin.listing.index', compact('pageTitle', 'listings'));
    }

    public function active(Request $request)
    {
        $pageTitle = 'Active Listings';
        $listings = $this->getListings($request)->where('status', Status::LISTING_ACTIVE);
        $listings = $listings->paginate(getPaginate());
        return view('admin.listing.index', compact('pageTitle', 'listings'));
    }

    public function sold(Request $request)
    {
        $pageTitle = 'Sold Listings';
        $listings = $this->getListings($request)->where('status', Status::LISTING_SOLD);
        $listings = $listings->paginate(getPaginate());
        return view('admin.listing.index', compact('pageTitle', 'listings'));
    }

    public function rejected(Request $request)
    {
        $pageTitle = 'Rejected Listings';
        $listings = $this->getListings($request)->where('status', Status::LISTING_REJECTED);
        $listings = $listings->paginate(getPaginate());
        return view('admin.listing.index', compact('pageTitle', 'listings'));
    }

    public function expired(Request $request)
    {
        $pageTitle = 'Expired Listings';
        $listings = $this->getListings($request)->where('status', Status::LISTING_EXPIRED);
        $listings = $listings->paginate(getPaginate());
        return view('admin.listing.index', compact('pageTitle', 'listings'));
    }

    public function details($id)
    {
        $pageTitle = 'Listing Details';
        $listing = Listing::with([
            'user',
            'images',
            'listingCategory',
            'metrics',
            'bids.user',
            'offers.buyer',
            'questions.asker',
            'escrow',
            'winner',
            'domainVerification',
        ])->findOrFail($id);

        return view('admin.listing.details', compact('pageTitle', 'listing'));
    }

    public function approve($id)
    {
        $listing = Listing::where('status', Status::LISTING_PENDING)->findOrFail($id);


        $listing->status = Status::LISTING_ACTIVE;
        $listing->approved_at = now();

        // Set auction times if it's an auction
        if ($listing->sale_type === 'auction') {
            $listing->auction_start = now();
            // Use stored duration or default to 7 days
            $duration = $listing->auction_duration_days ?? 7;
            $listing->auction_end = now()->addDays($duration);
        }

        $listing->save();

        // Notify seller
        notify($listing->user, 'LISTING_APPROVED', [
            'listing_title' => $listing->title,
            'listing_number' => $listing->listing_number,
        ]);

        $notify[] = ['success', 'Listing approved successfully'];
        return back()->withNotify($notify);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $listing = Listing::where('status', Status::LISTING_PENDING)->findOrFail($id);

        $listing->status = Status::LISTING_REJECTED;
        $listing->rejection_reason = $request->reason;
        $listing->save();

        // Notify seller
        notify($listing->user, 'LISTING_REJECTED', [
            'listing_title' => $listing->title,
            'listing_number' => $listing->listing_number,
            'reason' => $request->reason,
        ]);

        $notify[] = ['success', 'Listing rejected'];
        return back()->withNotify($notify);
    }

    public function feature(Request $request, $id)
    {
        $listing = Listing::active()->findOrFail($id);

        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $listing->is_featured = true;
        $listing->featured_until = now()->addDays($request->days);
        $listing->save();

        $notify[] = ['success', 'Listing featured for ' . $request->days . ' days'];
        return back()->withNotify($notify);
    }

    public function unfeature($id)
    {
        $listing = Listing::findOrFail($id);

        $listing->is_featured = false;
        $listing->featured_until = null;
        $listing->save();

        $notify[] = ['success', 'Listing unfeatured'];
        return back()->withNotify($notify);
    }

    public function verify(Request $request, $id)
    {
        $listing = Listing::findOrFail($id);

        $listing->is_verified = $request->boolean('is_verified', true);
        $listing->revenue_verified = $request->boolean('revenue_verified');
        $listing->traffic_verified = $request->boolean('traffic_verified');
        $listing->verification_notes = $request->verification_notes;
        $listing->save();

        $notify[] = ['success', 'Listing verification updated'];
        return back()->withNotify($notify);
    }

    public function extendAuction(Request $request, $id)
    {
        $listing = Listing::activeAuctions()->findOrFail($id);

        $request->validate([
            'hours' => 'required|integer|min:1|max:720',
        ]);

        $listing->auction_end = $listing->auction_end->addHours($request->hours);
        $listing->save();

        $notify[] = ['success', 'Auction extended by ' . $request->hours . ' hours'];
        return back()->withNotify($notify);
    }

    public function cancel($id)
    {
        $listing = Listing::whereIn('status', [Status::LISTING_ACTIVE, Status::LISTING_PENDING])
            ->findOrFail($id);

        $listing->status = Status::LISTING_CANCELLED;
        $listing->save();

        // Notify seller
        notify($listing->user, 'LISTING_CANCELLED_ADMIN', [
            'listing_title' => $listing->title,
            'listing_number' => $listing->listing_number,
        ]);

        $notify[] = ['success', 'Listing cancelled'];
        return back()->withNotify($notify);
    }

    private function getListings($request)
    {
        return Listing::with(['user', 'listingCategory', 'images', 'domainVerification'])
            ->when($request->search, function ($q, $search) {
                return $q->where(function ($query) use ($search) {
                    $query->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('listing_number', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('username', 'LIKE', "%{$search}%")
                                ->orWhere('email', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->when($request->business_type, function ($q, $type) {
                return $q->where('business_type', $type);
            })
            ->when($request->sale_type, function ($q, $type) {
                return $q->where('sale_type', $type);
            })
            ->orderBy('created_at', 'desc');
    }
}

