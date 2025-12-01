<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\ListingQuestion;
use App\Models\ListingView;
use App\Models\Review;
use App\Models\Watchlist;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Marketplace - Buy & Sell Online Businesses';

        // Featured listings
        $featuredListings = Listing::active()
            ->featured()
            ->with(['images', 'seller', 'listingCategory'])
            ->orderBy('featured_until', 'desc')
            ->take(6)
            ->get();

        // Ending soon auctions
        $endingSoon = Listing::activeAuctions()
            ->endingSoon()
            ->with(['images', 'seller'])
            ->orderBy('auction_end')
            ->take(6)
            ->get();

        // Latest listings
        $latestListings = Listing::active()
            ->with(['images', 'seller', 'listingCategory'])
            ->orderBy('approved_at', 'desc')
            ->take(12)
            ->get();

        // Categories with counts
        $categories = ListingCategory::active()
            ->withCount(['listings' => function ($q) {
                $q->where('status', Status::LISTING_ACTIVE);
            }])
            ->orderBy('sort_order')
            ->get()
            ->groupBy('business_type');

        // Stats
        $stats = [
            'total_listings' => Listing::active()->count(),
            'total_auctions' => Listing::activeAuctions()->count(),
            'total_sold' => Listing::sold()->count(),
            'total_value_sold' => Listing::sold()->sum('final_price'),
        ];

        return view('Template::marketplace.index', compact(
            'pageTitle',
            'featuredListings',
            'endingSoon',
            'latestListings',
            'categories',
            'stats'
        ));
    }

    public function browse(Request $request)
    {
        $pageTitle = 'Browse Listings';

        $listings = Listing::active()
            ->with(['images', 'seller', 'listingCategory'])
            ->when($request->business_type, function ($q, $type) {
                return $q->where('business_type', $type);
            })
            ->when($request->sale_type, function ($q, $type) {
                return $q->where('sale_type', $type);
            })
            ->when($request->category, function ($q, $categoryId) {
                return $q->where('listing_category_id', $categoryId);
            })
            ->when($request->min_price, function ($q, $min) {
                return $q->where(function ($query) use ($min) {
                    $query->where('asking_price', '>=', $min)
                        ->orWhere('current_bid', '>=', $min);
                });
            })
            ->when($request->max_price, function ($q, $max) {
                return $q->where(function ($query) use ($max) {
                    $query->where('asking_price', '<=', $max)
                        ->orWhere('current_bid', '<=', $max);
                });
            })
            ->when($request->min_revenue, function ($q, $min) {
                return $q->where('monthly_revenue', '>=', $min);
            })
            ->when($request->max_revenue, function ($q, $max) {
                return $q->where('monthly_revenue', '<=', $max);
            })
            ->when($request->verified, function ($q) {
                return $q->where('is_verified', true);
            })
            ->when($request->search, function ($q, $search) {
                return $q->search($search);
            })
            ->when($request->sort, function ($q, $sort) {
                switch ($sort) {
                    case 'price_low':
                        return $q->orderByRaw('COALESCE(NULLIF(current_bid, 0), asking_price) ASC');
                    case 'price_high':
                        return $q->orderByRaw('COALESCE(NULLIF(current_bid, 0), asking_price) DESC');
                    case 'ending_soon':
                        return $q->whereNotNull('auction_end')
                            ->where('auction_end', '>', now())
                            ->orderBy('auction_end');
                    case 'most_bids':
                        return $q->orderBy('total_bids', 'desc');
                    case 'most_watched':
                        return $q->orderBy('watchlist_count', 'desc');
                    case 'newest':
                    default:
                        return $q->orderBy('approved_at', 'desc');
                }
            }, function ($q) {
                return $q->orderBy('approved_at', 'desc');
            })
            ->paginate(getPaginate());

        $categories = ListingCategory::active()->orderBy('sort_order')->get();
        $businessTypes = $this->getBusinessTypes();

        return view('Template::marketplace.browse', compact(
            'pageTitle',
            'listings',
            'categories',
            'businessTypes'
        ));
    }

    public function show($slug)
    {
        $listing = Listing::where('slug', $slug)
            ->where('status', '!=', Status::LISTING_DRAFT)
            ->with([
                'images',
                'seller',
                'listingCategory',
                'metrics' => function ($q) {
                    $q->orderBy('period_date', 'desc')->take(12);
                },
                'questions' => function ($q) {
                    $q->where('is_public', true)
                        ->where('status', Status::QUESTION_ANSWERED)
                        ->orderBy('is_featured', 'desc')
                        ->orderBy('answered_at', 'desc');
                },
                'bids' => function ($q) {
                    $q->orderBy('amount', 'desc')->take(10);
                },
            ])
            ->firstOrFail();

        // Only show active, sold listings or own listings
        if (!in_array($listing->status, [Status::LISTING_ACTIVE, Status::LISTING_SOLD])) {
            if (!auth()->check() || auth()->id() !== $listing->user_id) {
                abort(404);
            }
        }

        $pageTitle = $listing->title;

        // Track view
        $this->trackView($listing);

        // Get seller info
        $seller = $listing->seller;
        $sellerStats = [
            'total_sales' => $seller->total_sales,
            'avg_rating' => $seller->avg_rating,
            'total_reviews' => $seller->total_reviews,
            'member_since' => $seller->created_at->format('M Y'),
            'active_listings' => $seller->activeListings()->count(),
        ];

        // Seller reviews
        $sellerReviews = Review::where('reviewed_user_id', $seller->id)
            ->approved()
            ->with('reviewer')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Similar listings
        $similarListings = Listing::active()
            ->where('id', '!=', $listing->id)
            ->where('business_type', $listing->business_type)
            ->with(['images', 'seller'])
            ->inRandomOrder()
            ->take(4)
            ->get();

        // Check if user is watching
        $isWatching = false;
        if (auth()->check()) {
            $isWatching = Watchlist::where('user_id', auth()->id())
                ->where('listing_id', $listing->id)
                ->exists();
        }

        return view('Template::marketplace.show', compact(
            'pageTitle',
            'listing',
            'seller',
            'sellerStats',
            'sellerReviews',
            'similarListings',
            'isWatching'
        ));
    }

    public function askQuestion(Request $request, $id)
    {
        if (!auth()->check()) {
            $notify[] = ['error', 'Please login to ask a question'];
            return back()->withNotify($notify);
        }

        $listing = Listing::active()->findOrFail($id);

        $request->validate([
            'question' => 'required|string|max:1000',
        ]);

        // Cannot ask question on own listing
        if ($listing->user_id === auth()->id()) {
            $notify[] = ['error', 'You cannot ask questions on your own listing'];
            return back()->withNotify($notify);
        }

        $question = new ListingQuestion();
        $question->listing_id = $listing->id;
        $question->user_id = auth()->id();
        $question->question = $request->question;
        $question->status = Status::QUESTION_PENDING;
        $question->save();

        // Notify seller
        notify($listing->seller, 'NEW_QUESTION_RECEIVED', [
            'listing_title' => $listing->title,
            'question' => $request->question,
            'asker' => auth()->user()->username,
        ]);

        $notify[] = ['success', 'Your question has been submitted'];
        return back()->withNotify($notify);
    }

    public function sellerProfile($username)
    {
        $seller = \App\Models\User::where('username', $username)
            ->active()
            ->firstOrFail();

        $pageTitle = $seller->fullname . ' - Seller Profile';

        $listings = Listing::active()
            ->where('user_id', $seller->id)
            ->with(['images', 'listingCategory'])
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());

        $stats = [
            'total_sales' => $seller->total_sales,
            'total_listings' => $seller->total_listings,
            'avg_rating' => $seller->avg_rating,
            'total_reviews' => $seller->total_reviews,
            'member_since' => $seller->created_at,
            'is_verified' => $seller->is_verified_seller,
        ];

        $reviews = Review::where('reviewed_user_id', $seller->id)
            ->approved()
            ->with(['reviewer', 'listing'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('Template::marketplace.seller', compact(
            'pageTitle',
            'seller',
            'listings',
            'stats',
            'reviews'
        ));
    }

    public function category($slug)
    {
        $category = ListingCategory::where('slug', $slug)->active()->firstOrFail();

        $pageTitle = $category->name;

        $listings = Listing::active()
            ->where('listing_category_id', $category->id)
            ->with(['images', 'seller'])
            ->orderBy('approved_at', 'desc')
            ->paginate(getPaginate());

        return view('Template::marketplace.category', compact('pageTitle', 'category', 'listings'));
    }

    public function businessType($type)
    {
        $businessTypes = $this->getBusinessTypes();

        if (!isset($businessTypes[$type])) {
            abort(404);
        }

        $pageTitle = $businessTypes[$type];

        $listings = Listing::active()
            ->where('business_type', $type)
            ->with(['images', 'seller', 'listingCategory'])
            ->orderBy('approved_at', 'desc')
            ->paginate(getPaginate());

        $categories = ListingCategory::active()
            ->where('business_type', $type)
            ->withCount(['listings' => function ($q) {
                $q->where('status', Status::LISTING_ACTIVE);
            }])
            ->orderBy('sort_order')
            ->get();

        return view('Template::marketplace.business_type', compact(
            'pageTitle',
            'type',
            'listings',
            'categories',
            'businessTypes'
        ));
    }

    public function auctions(Request $request)
    {
        $pageTitle = 'Live Auctions';

        $listings = Listing::activeAuctions()
            ->with(['images', 'seller', 'listingCategory'])
            ->when($request->business_type, function ($q, $type) {
                return $q->where('business_type', $type);
            })
            ->when($request->ending === 'soon', function ($q) {
                return $q->where('auction_end', '<=', now()->addHours(24));
            })
            ->orderBy('auction_end')
            ->paginate(getPaginate());

        $businessTypes = $this->getBusinessTypes();

        return view('Template::marketplace.auctions', compact(
            'pageTitle',
            'listings',
            'businessTypes'
        ));
    }

    private function trackView($listing)
    {
        $ipAddress = request()->ip();
        $userId = auth()->id() ?? 0;

        // Check for recent view from same IP/user
        $recentView = ListingView::where('listing_id', $listing->id)
            ->where(function ($q) use ($userId, $ipAddress) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('ip_address', $ipAddress);
                }
            })
            ->where('created_at', '>', now()->subHour())
            ->exists();

        if (!$recentView) {
            ListingView::create([
                'listing_id' => $listing->id,
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'user_agent' => request()->userAgent(),
                'referrer' => request()->header('referer'),
            ]);

            $listing->incrementViews();
        }
    }

    private function getBusinessTypes()
    {
        return [
            'domain' => 'Domain Names',
            'website' => 'Websites',
            'social_media_account' => 'Social Media Accounts',
            'mobile_app' => 'Mobile Apps',
            'desktop_app' => 'Desktop Apps',
        ];
    }
}

