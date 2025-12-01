<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DomainVerification;
use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\ListingImage;
use App\Models\ListingMetric;
use App\Models\MarketplaceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'My Listings';
        $user = auth()->user();

        $listings = Listing::where('user_id', $user->id)
            ->with(['listingCategory', 'images', 'domainVerification'])
            ->when($request->status, function ($q, $status) {
                return $q->where('status', $status);
            })
            ->when($request->business_type, function ($q, $type) {
                return $q->where('business_type', $type);
            })
            ->when($request->search, function ($q, $search) {
                return $q->search($search);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());

        return view('Template::user.listing.index', compact('pageTitle', 'listings'));
    }

    public function create()
    {
        $pageTitle = 'Create New Listing';
        $categories = Category::active()->get();
        $listingCategories = ListingCategory::active()->orderBy('sort_order')->get();
        $businessTypes = $this->getBusinessTypes();
        $platforms = $this->getPlatforms();
        $marketplaceSettings = MarketplaceSetting::getAllSettings();

        return view('Template::user.listing.create', compact(
            'pageTitle',
            'categories',
            'listingCategories',
            'businessTypes',
            'platforms',
            'marketplaceSettings'
        ));
    }

    public function store(Request $request)
    {
        // Check marketplace settings first
        $businessType = $request->business_type;
        $saleType = $request->sale_type;

        // Check if business type is allowed
        if (!MarketplaceSetting::allowBusinessType($businessType)) {
            $notify[] = ['error', 'Selling ' . str_replace('_', ' ', $businessType) . 's is currently disabled'];
            return back()->withInput()->withNotify($notify);
        }

        // Check if sale type is allowed
        if ($saleType === 'auction' && !MarketplaceSetting::allowAuctions()) {
            $notify[] = ['error', 'Auctions are currently disabled'];
            return back()->withInput()->withNotify($notify);
        }

        if ($saleType === 'fixed_price' && !MarketplaceSetting::allowFixedPrice()) {
            $notify[] = ['error', 'Fixed price sales are currently disabled'];
            return back()->withInput()->withNotify($notify);
        }

        // Validate auction duration
        $maxAuctionDays = MarketplaceSetting::maxAuctionDays();
        $minAuctionDays = MarketplaceSetting::minAuctionDays();
        $minDescription = MarketplaceSetting::minListingDescription();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:' . $minDescription,
            'business_type' => 'required|in:domain,website,social_media_account,mobile_app,desktop_app',
            'sale_type' => 'required|in:fixed_price,auction',
            'asking_price' => 'required_if:sale_type,fixed_price|nullable|numeric|min:1',
            'starting_bid' => 'required_if:sale_type,auction|nullable|numeric|min:1',
            'reserve_price' => 'nullable|numeric|min:0',
            'buy_now_price' => 'nullable|numeric|min:0',
            'bid_increment' => 'nullable|numeric|min:1',
            'auction_duration' => 'required_if:sale_type,auction|nullable|integer|min:' . $minAuctionDays . '|max:' . $maxAuctionDays,
            'listing_category_id' => 'nullable|exists:listing_categories,id',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = auth()->user();

        // Check if domain/website verification is required
        $requiresVerification = false;
        if ($businessType === 'domain' && MarketplaceSetting::requireDomainVerification()) {
            $requiresVerification = true;
        }
        if ($businessType === 'website' && MarketplaceSetting::requireWebsiteVerification()) {
            $requiresVerification = true;
        }

        $listing = new Listing();
        $listing->listing_number = getTrx();
        $listing->user_id = $user->id;
        $listing->title = $request->title;
        $listing->slug = Str::slug($request->title) . '-' . Str::random(8);
        $listing->tagline = $request->tagline;
        $listing->description = $request->description;
        $listing->business_type = $request->business_type;
        $listing->sale_type = $request->sale_type;
        $listing->listing_category_id = $request->listing_category_id;

        // Pricing
        if ($request->sale_type === 'fixed_price') {
            $listing->asking_price = $request->asking_price;
        } else {
            $listing->starting_bid = $request->starting_bid;
            $listing->reserve_price = $request->reserve_price ?? 0;
            $listing->buy_now_price = $request->buy_now_price ?? 0;
            $listing->bid_increment = $request->bid_increment ?? 1;
            // Don't start auction until verified and approved
            $listing->auction_duration_days = $request->auction_duration;
        }

        // Business-specific fields
        $this->fillBusinessTypeFields($listing, $request);

        // Financials
        $listing->monthly_revenue = $request->monthly_revenue ?? 0;
        $listing->monthly_profit = $request->monthly_profit ?? 0;
        $listing->yearly_revenue = $request->yearly_revenue ?? 0;
        $listing->yearly_profit = $request->yearly_profit ?? 0;

        // Traffic
        $listing->monthly_visitors = $request->monthly_visitors ?? 0;
        $listing->monthly_page_views = $request->monthly_page_views ?? 0;
        $listing->traffic_sources = $request->traffic_sources;
        $listing->monetization_methods = $request->monetization_methods;
        $listing->assets_included = $request->assets_included;

        // SEO
        $listing->meta_title = $request->meta_title ?? $request->title;
        $listing->meta_description = $request->meta_description ?? Str::limit(strip_tags($request->description), 160);

        // Status - if requires verification, set to draft first
        if ($requiresVerification) {
            $listing->status = Status::LISTING_DRAFT;
            $listing->requires_verification = true;
        } else {
            $listing->status = Status::LISTING_PENDING;
            $listing->requires_verification = false;
            $listing->is_verified = true; // No verification needed
        }

        $listing->save();

        // Handle images
        if ($request->hasFile('images')) {
            $this->uploadImages($listing, $request->file('images'));
        }

        // Update user stats
        $user->increment('total_listings');

        // If requires verification, create verification record and redirect
        if ($requiresVerification) {
            $verificationMethod = $request->verification_method ?? 'txt_file';
            $verification = DomainVerification::createForListing($listing, $verificationMethod);
            
            if ($verification) {
                $notify[] = ['info', 'Listing created! Please verify domain ownership to proceed.'];
                return redirect()->route('user.verification.show', $verification->id)->withNotify($notify);
            } else {
                // Verification couldn't be created (no valid URL/domain found)
                // Change status to pending without verification requirement
                $listing->requires_verification = false;
                $listing->is_verified = false;
                $listing->status = Status::LISTING_PENDING;
                $listing->save();
                
                $notify[] = ['warning', 'Listing created but domain verification could not be initiated. Please ensure you entered a valid URL.'];
                return redirect()->route('user.listing.index')->withNotify($notify);
            }
        }

        $notify[] = ['success', 'Listing created successfully and pending admin approval'];
        return redirect()->route('user.listing.index')->withNotify($notify);
    }

    public function edit($id)
    {
        $pageTitle = 'Edit Listing';
        $listing = Listing::where('user_id', auth()->id())
            ->whereIn('status', [Status::LISTING_DRAFT, Status::LISTING_PENDING, Status::LISTING_REJECTED])
            ->with(['images', 'metrics'])
            ->findOrFail($id);

        $categories = Category::active()->get();
        $listingCategories = ListingCategory::active()->orderBy('sort_order')->get();
        $businessTypes = $this->getBusinessTypes();
        $platforms = $this->getPlatforms();

        return view('Template::user.listing.edit', compact(
            'pageTitle',
            'listing',
            'categories',
            'listingCategories',
            'businessTypes',
            'platforms'
        ));
    }

    public function update(Request $request, $id)
    {
        $listing = Listing::where('user_id', auth()->id())
            ->whereIn('status', [Status::LISTING_DRAFT, Status::LISTING_PENDING, Status::LISTING_REJECTED])
            ->findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:100',
            'asking_price' => 'required_if:sale_type,fixed_price|nullable|numeric|min:1',
            'starting_bid' => 'required_if:sale_type,auction|nullable|numeric|min:1',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $listing->title = $request->title;
        $listing->tagline = $request->tagline;
        $listing->description = $request->description;
        $listing->listing_category_id = $request->listing_category_id;

        // Pricing
        if ($listing->sale_type === 'fixed_price') {
            $listing->asking_price = $request->asking_price;
        } else {
            $listing->starting_bid = $request->starting_bid;
            $listing->reserve_price = $request->reserve_price ?? 0;
            $listing->buy_now_price = $request->buy_now_price ?? 0;
            $listing->bid_increment = $request->bid_increment ?? 1;
        }

        // Business-specific fields
        $this->fillBusinessTypeFields($listing, $request);

        // Financials
        $listing->monthly_revenue = $request->monthly_revenue ?? 0;
        $listing->monthly_profit = $request->monthly_profit ?? 0;
        $listing->yearly_revenue = $request->yearly_revenue ?? 0;
        $listing->yearly_profit = $request->yearly_profit ?? 0;

        // Traffic
        $listing->monthly_visitors = $request->monthly_visitors ?? 0;
        $listing->monthly_page_views = $request->monthly_page_views ?? 0;
        $listing->traffic_sources = $request->traffic_sources;
        $listing->monetization_methods = $request->monetization_methods;
        $listing->assets_included = $request->assets_included;

        // SEO
        $listing->meta_title = $request->meta_title ?? $request->title;
        $listing->meta_description = $request->meta_description;

        // Re-submit for approval if was rejected
        if ($listing->status == Status::LISTING_REJECTED) {
            $listing->status = Status::LISTING_PENDING;
            $listing->rejection_reason = null;
        }

        $listing->save();

        // Handle new images
        if ($request->hasFile('images')) {
            $this->uploadImages($listing, $request->file('images'));
        }

        $notify[] = ['success', 'Listing updated successfully'];
        return redirect()->route('user.listing.index')->withNotify($notify);
    }

    public function show($id)
    {
        $pageTitle = 'Listing Details';
        $listing = Listing::where('user_id', auth()->id())
            ->with(['images', 'metrics', 'bids.user', 'offers.buyer', 'questions.asker', 'watchlist'])
            ->findOrFail($id);

        $stats = [
            'total_views' => $listing->view_count,
            'total_watchers' => $listing->watchlist_count,
            'total_bids' => $listing->total_bids,
            'total_offers' => $listing->offers()->count(),
            'total_questions' => $listing->questions()->count(),
        ];

        return view('Template::user.listing.show', compact('pageTitle', 'listing', 'stats'));
    }

    public function cancel($id)
    {
        $listing = Listing::where('user_id', auth()->id())
            ->whereIn('status', [Status::LISTING_DRAFT, Status::LISTING_PENDING, Status::LISTING_ACTIVE])
            ->findOrFail($id);

        // Check if auction has bids
        if ($listing->sale_type === 'auction' && $listing->total_bids > 0) {
            $notify[] = ['error', 'Cannot cancel listing with active bids'];
            return back()->withNotify($notify);
        }

        $listing->status = Status::LISTING_CANCELLED;
        $listing->save();

        $notify[] = ['success', 'Listing cancelled successfully'];
        return back()->withNotify($notify);
    }

    public function deleteImage($id)
    {
        $image = ListingImage::whereHas('listing', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($id);

        // Delete file
        $path = getFilePath('listing') . '/' . $image->image;
        if (file_exists($path)) {
            unlink($path);
        }

        $image->delete();

        return response()->json(['success' => true, 'message' => 'Image deleted']);
    }

    public function setPrimaryImage($id)
    {
        $image = ListingImage::whereHas('listing', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($id);

        // Remove primary from other images
        ListingImage::where('listing_id', $image->listing_id)
            ->where('id', '!=', $id)
            ->update(['is_primary' => false]);

        $image->is_primary = true;
        $image->save();

        return response()->json(['success' => true, 'message' => 'Primary image set']);
    }

    public function addMetrics(Request $request, $id)
    {
        $listing = Listing::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'period_date' => 'required|date',
            'period_type' => 'required|in:monthly,weekly,daily',
            'revenue' => 'nullable|numeric|min:0',
            'expenses' => 'nullable|numeric|min:0',
            'visitors' => 'nullable|integer|min:0',
            'page_views' => 'nullable|integer|min:0',
        ]);

        ListingMetric::updateOrCreate(
            [
                'listing_id' => $listing->id,
                'period_date' => $request->period_date,
                'period_type' => $request->period_type,
            ],
            [
                'revenue' => $request->revenue ?? 0,
                'expenses' => $request->expenses ?? 0,
                'profit' => ($request->revenue ?? 0) - ($request->expenses ?? 0),
                'visitors' => $request->visitors ?? 0,
                'page_views' => $request->page_views ?? 0,
                'unique_visitors' => $request->unique_visitors ?? 0,
                'followers' => $request->followers ?? 0,
                'subscribers' => $request->subscribers ?? 0,
                'downloads' => $request->downloads ?? 0,
                'email_subscribers' => $request->email_subscribers ?? 0,
                'notes' => $request->notes,
            ]
        );

        $notify[] = ['success', 'Metrics added successfully'];
        return back()->withNotify($notify);
    }

    // Answer a question on the listing
    public function answerQuestion(Request $request, $id)
    {
        $listing = Listing::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'question_id' => 'required|exists:listing_questions,id',
            'answer' => 'required|string|max:2000',
        ]);

        $question = $listing->questions()->findOrFail($request->question_id);
        $question->answer = $request->answer;
        $question->answered_at = now();
        $question->status = Status::QUESTION_ANSWERED;
        $question->save();

        // Notify the user who asked the question
        if ($question->user) {
            notify($question->user, 'QUESTION_ANSWERED', [
                'listing_title' => $listing->title,
                'question' => Str::limit($question->question, 100),
                'answer' => Str::limit($question->answer, 200),
            ]);
        }

        $notify[] = ['success', 'Question answered successfully'];
        return back()->withNotify($notify);
    }

    private function fillBusinessTypeFields($listing, $request)
    {
        switch ($request->business_type) {
            case 'domain':
                $listing->domain_name = $request->domain_name;
                $listing->domain_extension = $request->domain_extension;
                $listing->domain_registrar = $request->domain_registrar;
                $listing->domain_expiry = $request->domain_expiry;
                $listing->domain_age_years = $request->domain_age_years ?? 0;
                // Set URL from domain name for verification purposes
                $listing->url = 'https://' . $request->domain_name;
                break;

            case 'website':
                $listing->url = $request->website_url;
                $listing->niche = $request->niche;
                $listing->tech_stack = $request->tech_stack;
                $listing->domain_registrar = $request->domain_registrar;
                $listing->domain_expiry = $request->domain_expiry;
                break;

            case 'social_media_account':
                $listing->platform = $request->platform;
                $listing->niche = $request->niche;
                $listing->url = $request->social_url;
                $listing->followers_count = $request->followers_count ?? 0;
                $listing->subscribers_count = $request->subscribers_count ?? 0;
                $listing->engagement_rate = $request->engagement_rate ?? 0;
                break;

            case 'mobile_app':
                $listing->app_store_url = $request->app_store_url;
                $listing->play_store_url = $request->play_store_url;
                $listing->downloads_count = $request->downloads_count ?? 0;
                $listing->app_rating = $request->app_rating ?? 0;
                $listing->tech_stack = $request->tech_stack;
                break;

            case 'desktop_app':
                $listing->url = $request->desktop_url;
                $listing->downloads_count = $request->downloads_count ?? 0;
                $listing->tech_stack = $request->tech_stack;
                break;
        }
    }

    private function uploadImages($listing, $files)
    {
        $path = getFilePath('listing');
        $size = getFileSize('listing');

        foreach ($files as $index => $file) {
            $filename = fileUploader($file, $path, $size);

            ListingImage::create([
                'listing_id' => $listing->id,
                'image' => $filename,
                'is_primary' => $listing->images()->count() === 0 && $index === 0,
                'sort_order' => $index,
            ]);
        }
    }

    private function getBusinessTypes()
    {
        $allTypes = [
            'domain' => 'Domain Name',
            'website' => 'Website',
            'social_media_account' => 'Social Media Account',
            'mobile_app' => 'Mobile App',
            'desktop_app' => 'Desktop App',
        ];

        // Filter by marketplace settings
        $allowedTypes = [];
        foreach ($allTypes as $key => $label) {
            if (MarketplaceSetting::allowBusinessType($key)) {
                $allowedTypes[$key] = $label;
            }
        }

        return $allowedTypes;
    }

    private function getPlatforms()
    {
        return [
            'instagram' => 'Instagram',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok',
            'twitter' => 'Twitter/X',
            'facebook' => 'Facebook',
            'linkedin' => 'LinkedIn',
            'pinterest' => 'Pinterest',
            'snapchat' => 'Snapchat',
            'twitch' => 'Twitch',
        ];
    }
}

