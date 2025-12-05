<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Category;
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

        // Check if user just submitted a listing successfully (via session flag)
        // If so, clear any existing draft to start fresh
        if (session()->has('listing_submitted_successfully')) {
            session()->forget([
                'listing_draft',
                'listing_draft_stage',
                'listing_draft_updated_at',
                'listing_submitted_successfully'
            ]);
        }

        // Restore draft data from session
        $draftData = session('listing_draft', []);
        $currentStage = session('listing_draft_stage', 1);

        return view('Template::user.listing.create', compact(
            'pageTitle',
            'categories',
            'listingCategories',
            'businessTypes',
            'platforms',
            'marketplaceSettings',
            'draftData',
            'currentStage'
        ));
    }

    /**
     * Save draft listing data to session
     */
    public function saveDraft(Request $request)
    {
        $user = auth()->user();
        
        // Get all form data except files and CSRF token
        $draftData = $request->except(['_token', 'images', '_method']);
        $currentStage = $request->input('current_stage', 1);

        // Store in session
        session([
            'listing_draft' => $draftData,
            'listing_draft_stage' => (int)$currentStage,
            'listing_draft_updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Draft saved successfully',
            'stage' => $currentStage
        ]);
    }

    /**
     * Clear draft listing data
     */
    public function clearDraft()
    {
        session()->forget([
            'listing_draft',
            'listing_draft_stage',
            'listing_draft_updated_at'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Draft cleared successfully'
        ]);
    }

    public function store(Request $request)
    {
        try {
            $businessType = $request->business_type;
            $saleType = $request->sale_type;
            $user = auth()->user();

        // Basic validations
        if (!MarketplaceSetting::allowBusinessType($businessType)) {
            $notify[] = ['error', 'Selling ' . str_replace('_', ' ', $businessType) . 's is currently disabled'];
            return back()->withInput()->withNotify($notify);
        }

        if ($saleType === 'auction' && !MarketplaceSetting::allowAuctions()) {
            $notify[] = ['error', 'Auctions are currently disabled'];
            return back()->withInput()->withNotify($notify);
        }

        if ($saleType === 'fixed_price' && !MarketplaceSetting::allowFixedPrice()) {
            $notify[] = ['error', 'Fixed price sales are currently disabled'];
            return back()->withInput()->withNotify($notify);
        }

        $minDescription = MarketplaceSetting::minListingDescription();
        $maxAuctionDays = MarketplaceSetting::maxAuctionDays();
        $minAuctionDays = MarketplaceSetting::minAuctionDays();

        // Normalize URLs
        if ($request->has('domain_name') && $request->domain_name) {
            $request->merge(['domain_name' => normalizeUrl($request->domain_name)]);
        }
        if ($request->has('website_url') && $request->website_url) {
            $request->merge(['website_url' => normalizeUrl($request->website_url)]);
        }

        // Rate limiting for listing creation
        $recentListings = Listing::where('user_id', $user->id)
            ->where('created_at', '>', now()->subHours(24))
            ->count();

        if ($recentListings >= 10) { // Max 10 listings per 24 hours
            $notify[] = ['error', 'You have reached the maximum number of listings you can create per day. Please try again tomorrow.'];
            return back()->withInput()->withNotify($notify);
        }

        // Sanitize and validate input data
        $this->sanitizeListingInput($request);

        // Comprehensive validation with business logic
        $request->validate([
            'title' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9\s\-\.\,\&\(\)\[\]]+$/',
            'tagline' => 'nullable|string|max:200',
            'description' => 'required|string|min:' . $minDescription . '|max:10000',
            'business_type' => 'required|in:domain,website,social_media_account,mobile_app,desktop_app',
            'sale_type' => 'required|in:fixed_price,auction',
            'asking_price' => 'required_if:sale_type,fixed_price|nullable|numeric|min:1|max:999999999',
            'starting_bid' => 'required_if:sale_type,auction|nullable|numeric|min:1|max:999999999',
            'reserve_price' => 'nullable|numeric|min:0|max:999999999',
            'buy_now_price' => 'nullable|numeric|min:0|max:999999999',
            'bid_increment' => 'nullable|numeric|min:1|max:999999',
            'auction_duration' => 'required_if:sale_type,auction|nullable|integer|min:' . $minAuctionDays . '|max:' . $maxAuctionDays,
            'listing_category_id' => 'nullable|exists:listing_categories,id',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'domain_name' => 'required_if:business_type,domain|nullable|url|regex:/^https?:\/\/.+/i|max:500',
            'website_url' => 'required_if:business_type,website|nullable|url|regex:/^https?:\/\/.+/i|max:500',
            'monthly_revenue' => 'nullable|numeric|min:0|max:999999999',
            'monthly_profit' => 'nullable|numeric|min:0|max:999999999',
            'monthly_visitors' => 'nullable|numeric|min:0|max:999999999',
            'is_confidential' => 'nullable|boolean',
            'requires_nda' => 'nullable|boolean',
            'confidential_reason' => 'nullable|string|max:1000',
        ]);

        // Business logic validation
        $this->validateListingBusinessLogic($request, $user);

        // Check if verification is required for this business type
        $requiresVerification = false;
        $domain = null;
        $socialAccount = null;

        if ($businessType === 'domain' && MarketplaceSetting::requireDomainVerification()) {
            $requiresVerification = true;
        } elseif ($businessType === 'website' && MarketplaceSetting::requireWebsiteVerification()) {
            $requiresVerification = true;
        } elseif ($businessType === 'social_media_account' && MarketplaceSetting::requireSocialMediaVerification()) {
            $requiresVerification = true;
        }

        // If verification is required, check if it was completed
        if ($requiresVerification) {
            if ($businessType === 'domain') {
                $domain = $this->extractDomain($request->domain_name);
                $cacheKey = 'verified_domain_' . auth()->id() . '_' . $domain;
                $verifiedData = \Illuminate\Support\Facades\Cache::get($cacheKey);

                if (!$verifiedData) {
                    $notify[] = ['error', 'You must verify ownership of your domain before submitting the listing.'];
                    return back()->withInput()->withNotify($notify);
                }

                // Store verification details in request for processing
                $request->merge([
                    'domain_verified' => '1',
                    'verification_token' => $verifiedData['token'],
                    'verification_method' => $verifiedData['method'],
                ]);
            } elseif ($businessType === 'website') {
                $domain = $this->extractDomain($request->website_url);
                $cacheKey = 'verified_domain_' . auth()->id() . '_' . $domain;
                $verifiedData = \Illuminate\Support\Facades\Cache::get($cacheKey);

                if (!$verifiedData) {
                    $notify[] = ['error', 'You must verify ownership of your website before submitting the listing.'];
                    return back()->withInput()->withNotify($notify);
                }

                // Store verification details in request for processing
                $request->merge([
                    'domain_verified' => '1',
                    'verification_token' => $verifiedData['token'],
                    'verification_method' => $verifiedData['method'],
                ]);
            } elseif ($businessType === 'social_media_account') {
                $socialAccount = $request->social_media_username;
                $cacheKey = 'verified_social_' . auth()->id() . '_' . $request->social_media_platform . '_' . $socialAccount;
                $verifiedData = \Illuminate\Support\Facades\Cache::get($cacheKey);

                if (!$verifiedData) {
                    $notify[] = ['error', 'You must verify ownership of your social media account before submitting the listing.'];
                    return back()->withInput()->withNotify($notify);
                }

                // Store verification details in request for processing
                $request->merge([
                    'social_verified' => '1',
                    'verification_token' => $verifiedData['token'],
                    'verification_method' => 'post_verification',
                ]);
            }
        }

        // Extract domain/website info
        $domain = null;
        $url = null;

        if ($businessType === 'domain') {
            $url = $request->domain_name;
            $domain = extractDomain($url);
            if (!$domain) {
                $notify[] = ['error', 'Invalid domain format.'];
                return back()->withInput()->withNotify($notify);
            }
            // Check duplicates
            $existing = Listing::where('domain_name', $domain)
                ->where('user_id', '!=', $user->id)
                ->whereIn('status', [Status::LISTING_ACTIVE, Status::LISTING_PENDING])
                ->first();
            if ($existing) {
                $notify[] = ['error', 'A listing for this domain already exists.'];
                return back()->withInput()->withNotify($notify);
            }
        }

        if ($businessType === 'website') {
            $url = $request->website_url;
            $domain = extractDomain($url);
            if (!$domain) {
                $notify[] = ['error', 'Invalid website URL format.'];
                return back()->withInput()->withNotify($notify);
            }
            // Check duplicates
            $existing = Listing::where('url', $url)
                ->where('user_id', '!=', $user->id)
                ->whereIn('status', [Status::LISTING_ACTIVE, Status::LISTING_PENDING])
                ->first();
            if ($existing) {
                $notify[] = ['error', 'A listing for this website already exists.'];
                return back()->withInput()->withNotify($notify);
            }
        }

        // Generate title
        $title = $this->generateTitle($request, $domain);

        // Create listing
        $listing = new Listing();
        $listing->listing_number = getTrx();
        $listing->user_id = $user->id;
        $listing->title = $title;
        $listing->slug = Str::slug($title) . '-' . Str::random(8);
        $listing->tagline = $request->tagline;
        $listing->description = $request->description;
        $listing->business_type = $businessType;
        $listing->sale_type = $saleType;
        $listing->listing_category_id = $request->listing_category_id;
        $listing->is_confidential = $request->has('is_confidential') ? (bool)$request->is_confidential : false;
        $listing->requires_nda = $request->has('requires_nda') ? (bool)$request->requires_nda : false;
        $listing->confidential_reason = $request->confidential_reason ?? null;

        // Pricing
        if ($saleType === 'fixed_price') {
            $listing->asking_price = $request->asking_price;
        } else {
            $listing->starting_bid = $request->starting_bid;
            $listing->reserve_price = $request->reserve_price ?? 0;
            $listing->buy_now_price = $request->buy_now_price ?? 0;
            $listing->bid_increment = $request->bid_increment ?? 1;
            $listing->auction_duration_days = $request->auction_duration;
        }

        // Business fields
        $this->fillBusinessTypeFields($listing, $request);

        // Financials & Traffic
        $listing->monthly_revenue = $request->monthly_revenue ?? 0;
        $listing->monthly_profit = $request->monthly_profit ?? 0;
        $listing->yearly_revenue = $request->yearly_revenue ?? 0;
        $listing->yearly_profit = $request->yearly_profit ?? 0;
        $listing->monthly_visitors = $request->monthly_visitors ?? 0;
        $listing->monthly_page_views = $request->monthly_page_views ?? 0;
        $listing->traffic_sources = $request->traffic_sources;
        $listing->monetization_methods = $request->monetization_methods;
        $listing->assets_included = $request->assets_included;

        // SEO
        $listing->meta_title = $request->meta_title ?? $title;
        $listing->meta_description = $request->meta_description ?? Str::limit(strip_tags($request->description), 160);

        // Set status based on verification requirements and completion
        if ($requiresVerification && $request->domain_verified == '1') {
            // Verification was completed during creation - set to pending
            $listing->status = Status::LISTING_PENDING;
            $listing->requires_verification = false;
            $listing->is_verified = true;

            // Store verification details
            $listing->verification_token = $request->verification_token;
            $listing->verification_method = $request->verification_method ?? 'file';
            $listing->verification_filename = $request->verification_filename;
            $listing->verification_dns_name = $request->verification_dns_name;
            $listing->verified_at = now();

            \Log::info('Listing created with verification completed', [
                'listing_id' => $listing->id,
                'domain' => $domain,
                'verification_method' => $listing->verification_method
            ]);
        } elseif ($requiresVerification) {
            // This shouldn't happen since we check verification above, but just in case
            $notify[] = ['error', 'Verification is required but was not completed.'];
            return back()->withInput()->withNotify($notify);
        } else {
            // No verification required - can go directly to pending
            $listing->status = Status::LISTING_PENDING;
            $listing->requires_verification = false;
            $listing->is_verified = false;
        }

        $listing->save();

        // Log listing creation
        \Log::info('Listing created', [
            'listing_id' => $listing->id,
            'listing_number' => $listing->listing_number,
            'user_id' => $user->id,
            'username' => $user->username,
            'title' => $listing->title,
            'business_type' => $listing->business_type,
            'sale_type' => $listing->sale_type,
            'asking_price' => $listing->asking_price,
            'status' => $listing->status,
            'requires_verification' => $listing->requires_verification,
            'verification_required' => $requiresVerification,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Handle images
        if ($request->hasFile('images')) {
            $this->uploadImages($listing, $request->file('images'));
        }

        $user->increment('total_listings');

        // Clear draft data after successful submission
        session()->forget([
            'listing_draft',
            'listing_draft_stage',
            'listing_draft_updated_at'
        ]);
        
        // Set flag to indicate successful submission (so create page knows to clear draft on next visit)
        session()->put('listing_submitted_successfully', true);

        $notify[] = ['success', 'Listing created successfully and submitted for review!'];
        if ($requiresVerification) {
            $notify[] = ['info', 'Domain verification was completed successfully.'];
        }
        return redirect()->route('user.listing.index')->withNotify($notify);
        } catch (\Exception $e) {
            \Log::error('Listing creation failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'business_type' => $request->business_type ?? null,
                'sale_type' => $request->sale_type ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            $notify[] = ['error', 'An error occurred while creating your listing. Please try again.'];
            return back()->withInput()->withNotify($notify);
        }
    }

    private function generateTitle($request, $domain = null)
    {
        switch ($request->business_type) {
            case 'domain':
                return $domain ?: extractDomain($request->domain_name) ?: 'Domain Listing';
            case 'website':
                return $domain ?: extractDomain($request->website_url) ?: 'Website Listing';
            case 'social_media_account':
                $username = $request->social_username ?? '';
                if ($username) {
                    return '@' . $username;
                }
                return ucfirst($request->platform ?? 'Social Media Account');
            default:
                return ucfirst(str_replace('_', ' ', $request->business_type));
        }
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
        try {
            $listing = Listing::where('user_id', auth()->id())
                ->whereIn('status', [Status::LISTING_DRAFT, Status::LISTING_PENDING, Status::LISTING_REJECTED])
                ->findOrFail($id);

        // Normalize URLs if being updated
        if ($request->has('domain_name') && $request->domain_name) {
            $request->merge(['domain_name' => normalizeUrl($request->domain_name)]);
        }
        
        if ($request->has('website_url') && $request->website_url) {
            $request->merge(['website_url' => normalizeUrl($request->website_url)]);
        }

        $minDescription = MarketplaceSetting::minListingDescription();
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:' . $minDescription,
            'asking_price' => 'required_if:sale_type,fixed_price|nullable|numeric|min:1',
            'starting_bid' => 'required_if:sale_type,auction|nullable|numeric|min:1',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'domain_name' => 'required_if:business_type,domain|nullable|url|regex:/^https?:\/\/.+/i',
            'website_url' => 'required_if:business_type,website|nullable|url|regex:/^https?:\/\/.+/i',
        ], [
            'domain_name.url' => 'Please enter a valid domain URL (e.g., https://example.com)',
            'website_url.url' => 'Please enter a valid website URL (e.g., https://example.com)',
        ]);
        
        // Check for duplicate domains/websites (excluding current listing)
        if ($listing->business_type === 'domain' && $request->domain_name) {
            $domain = extractDomain($request->domain_name);
            $existingListing = Listing::where('domain_name', $domain)
                ->where('id', '!=', $listing->id)
                ->where('user_id', '!=', auth()->id())
                ->whereIn('status', [Status::LISTING_ACTIVE, Status::LISTING_PENDING])
                ->first();
            
            if ($existingListing) {
                $notify[] = ['error', 'A listing for this domain already exists. Each domain can only be listed once.'];
                return back()->withInput()->withNotify($notify);
            }
        }
        
        if ($listing->business_type === 'website' && $request->website_url) {
            $url = normalizeUrl($request->website_url);
            $existingListing = Listing::where('url', $url)
                ->where('id', '!=', $listing->id)
                ->where('user_id', '!=', auth()->id())
                ->whereIn('status', [Status::LISTING_ACTIVE, Status::LISTING_PENDING])
                ->first();
            
            if ($existingListing) {
                $notify[] = ['error', 'A listing for this website already exists. Each website can only be listed once.'];
                return back()->withInput()->withNotify($notify);
            }
        }

        $listing->title = $request->title;
        $listing->tagline = $request->tagline;
        $listing->description = $request->description;
        $listing->listing_category_id = $request->listing_category_id;

        // Confidential & NDA Settings
        $listing->is_confidential = $request->has('is_confidential') ? (bool)$request->is_confidential : false;
        $listing->requires_nda = $request->has('requires_nda') ? (bool)$request->requires_nda : false;
        $listing->confidential_reason = $request->confidential_reason ?? null;

        // Pricing with validation
        if ($listing->sale_type === 'fixed_price') {
            $listing->asking_price = $request->asking_price;
        } else {
            $listing->starting_bid = $request->starting_bid;
            $listing->reserve_price = $request->reserve_price ?? 0;
            $listing->buy_now_price = $request->buy_now_price ?? 0;
            $listing->bid_increment = $request->bid_increment ?? 1;
            
            // Common sense validations (same as create)
            if ($listing->buy_now_price > 0 && $listing->reserve_price > $listing->buy_now_price) {
                $notify[] = ['error', 'Reserve price cannot be higher than Buy Now price'];
                return back()->withInput()->withNotify($notify);
            }
            
            if ($listing->reserve_price > 0 && $listing->reserve_price < $listing->starting_bid) {
                $notify[] = ['error', 'Reserve price cannot be lower than starting bid'];
                return back()->withInput()->withNotify($notify);
            }
            
            if ($listing->buy_now_price > 0 && $listing->buy_now_price < $listing->starting_bid) {
                $notify[] = ['error', 'Buy Now price cannot be lower than starting bid'];
                return back()->withInput()->withNotify($notify);
            }
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

        // Log listing update
        \Log::info('Listing updated', [
            'listing_id' => $listing->id,
            'listing_number' => $listing->listing_number,
            'user_id' => auth()->id(),
            'username' => auth()->user()->username,
            'title' => $listing->title,
            'business_type' => $listing->business_type,
            'sale_type' => $listing->sale_type,
            'asking_price' => $listing->asking_price,
            'status' => $listing->status,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Handle new images
        if ($request->hasFile('images')) {
            $this->uploadImages($listing, $request->file('images'));
        }

            $notify[] = ['success', 'Listing updated successfully'];
            return redirect()->route('user.listing.index')->withNotify($notify);
        } catch (\Exception $e) {
            \Log::error('Listing update failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'listing_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            $notify[] = ['error', 'An error occurred while updating your listing. Please try again.'];
            return back()->withInput()->withNotify($notify);
        }
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
                // Extract clean domain name using helper
                $domainName = extractDomain($request->domain_name);
                
                if (!$domainName) {
                    // Fallback to manual extraction
                    $domainName = $request->domain_name;
                    if (preg_match('/^https?:\/\/(.+)$/i', $domainName, $matches)) {
                        $domainName = $matches[1];
                    }
                    $domainName = preg_replace('/^www\./i', '', $domainName);
                    $domainName = explode('/', $domainName)[0];
                }
                
                $listing->domain_name = $domainName;
                $listing->domain_extension = $request->domain_extension;
                $listing->domain_registrar = $request->domain_registrar;
                $listing->domain_expiry = $request->domain_expiry;
                $listing->domain_age_years = $request->domain_age_years ?? 0;
                // Set URL from domain name for verification purposes (normalized)
                $listing->url = normalizeUrl($request->domain_name);
                break;

            case 'website':
                // Normalize website URL
                $listing->url = normalizeUrl($request->website_url);
                $listing->niche = $request->website_niche ?? $request->niche ?? null;
                $listing->tech_stack = $request->website_tech_stack ?? $request->tech_stack ?? null;
                $listing->domain_registrar = $request->website_domain_registrar ?? $request->domain_registrar ?? null;
                $listing->domain_expiry = $request->website_domain_expiry ?? $request->domain_expiry ?? null;
                // Also store domain name for easier searching
                $listing->domain_name = extractDomain($request->website_url);
                break;

            case 'social_media_account':
                $listing->platform = $request->platform;
                $listing->niche = $request->social_niche ?? $request->niche ?? null;
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
                $listing->tech_stack = $request->mobile_tech_stack ?? $request->tech_stack ?? null;
                break;

            case 'desktop_app':
                $listing->url = $request->desktop_url;
                $listing->downloads_count = $request->downloads_count ?? 0;
                $listing->tech_stack = $request->desktop_tech_stack ?? $request->tech_stack ?? null;
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

    /**
     * Sanitize listing input data
     */
    private function sanitizeListingInput(Request $request)
    {
        // Sanitize text inputs
        $textFields = ['title', 'tagline', 'description', 'confidential_reason'];
        foreach ($textFields as $field) {
            if ($request->has($field) && $request->$field) {
                // Remove potentially harmful HTML/script content
                $request->merge([$field => strip_tags($request->$field)]);
                // Trim whitespace
                $request->merge([$field => trim($request->$field)]);
            }
        }

        // Sanitize URLs
        if ($request->has('domain_name') && $request->domain_name) {
            $request->merge(['domain_name' => filter_var($request->domain_name, FILTER_SANITIZE_URL)]);
        }
        if ($request->has('website_url') && $request->website_url) {
            $request->merge(['website_url' => filter_var($request->website_url, FILTER_SANITIZE_URL)]);
        }

        // Ensure numeric fields are properly formatted
        $numericFields = ['asking_price', 'starting_bid', 'reserve_price', 'buy_now_price',
                         'bid_increment', 'monthly_revenue', 'monthly_profit', 'yearly_revenue',
                         'yearly_profit', 'monthly_visitors', 'yearly_visitors'];

        foreach ($numericFields as $field) {
            if ($request->has($field) && $request->$field !== null) {
                $value = floatval($request->$field);
                if ($value < 0) $value = 0;
                $request->merge([$field => $value]);
            }
        }
    }

    /**
     * Validate listing business logic
     */
    private function validateListingBusinessLogic(Request $request, $user)
    {
        $saleType = $request->sale_type;

        // Auction-specific validations
        if ($saleType === 'auction') {
            // Reserve price cannot be lower than starting bid
            if ($request->reserve_price > 0 && $request->reserve_price <= $request->starting_bid) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'reserve_price' => ['Reserve price must be higher than the starting bid']
                ]);
            }

            // Buy now price must be reasonable
            if ($request->buy_now_price > 0 && $request->buy_now_price <= $request->starting_bid) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'buy_now_price' => ['Buy now price must be higher than the starting bid']
                ]);
            }

            // Bid increment validation
            if ($request->bid_increment > $request->starting_bid * 0.5) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'bid_increment' => ['Bid increment cannot be more than 50% of the starting bid']
                ]);
            }
        }

        // Financial validation - profit cannot exceed revenue
        if ($request->monthly_profit > $request->monthly_revenue) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'monthly_profit' => ['Monthly profit cannot exceed monthly revenue']
            ]);
        }

        if ($request->yearly_profit > $request->yearly_revenue) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'yearly_profit' => ['Yearly profit cannot exceed yearly revenue']
            ]);
        }

        // Domain/website validation
        if ($request->business_type === 'domain' && $request->domain_name) {
            // Check for suspicious domains
            $suspiciousPatterns = ['/localhost/i', '/127\.0\.0\.1/i', '/\.local/i'];
            foreach ($suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $request->domain_name)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'domain_name' => ['Invalid domain name']
                    ]);
                }
            }
        }

        // User status validation
        if ($user->status !== \App\Constants\Status::USER_ACTIVE) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'user' => ['Your account must be active to create listings']
            ]);
        }
    }

    /**
     * Extract domain from URL
     */
    private function extractDomain($url)
    {
        if (!$url) return null;

        // Remove protocol
        $url = preg_replace('#^https?://#', '', $url);

        // Remove www
        $url = preg_replace('#^www\.#', '', $url);

        // Remove path and query
        $domain = parse_url('https://' . $url, PHP_URL_HOST);

        return $domain ?: null;
    }
}

