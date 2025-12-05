@extends($activeTemplate . 'layouts.frontend')
@section('content')
<section class="section bg--light">
<div class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                {{-- Progress Steps --}}
                <div class="listing-progress mb-4">
                    <div class="progress-steps">
                        <div class="step active" data-step="1">
                            <span class="step-number">1</span>
                            <span class="step-text">@lang('Domain & Verification')</span>
                        </div>
                        <div class="step" data-step="2">
                            <span class="step-number">2</span>
                            <span class="step-text">@lang('Details')</span>
                        </div>
                        <div class="step" data-step="3">
                            <span class="step-number">3</span>
                            <span class="step-text">@lang('Pricing')</span>
                        </div>
                        <div class="step" data-step="4">
                            <span class="step-number">4</span>
                            <span class="step-text">@lang('Media')</span>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="las la-plus-circle text--base me-2"></i>
                                @lang('Create New Listing')
                            </h5>
                            @if(!empty($draftData))
                                <div class="draft-indicator">
                                    <span class="badge bg-info">
                                        <i class="las la-save me-1"></i>
                                        @lang('Draft Saved')
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="clearDraftBtn">
                                        <i class="las la-trash"></i> @lang('Clear Draft')
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('user.listing.store') }}" method="POST" enctype="multipart/form-data" id="listingForm">
                            @csrf
                            
                            {{-- ============================================
                                 STEP 1: Business Type, Domain/Website Entry & Verification
                                 ============================================ --}}
                            <div class="form-step" data-step="1">
                                <div class="step-header mb-4">
                                    <h5 class="fw-bold mb-1">@lang('What are you selling?')</h5>
                                    <p class="text-muted mb-0">@lang('Select the type of online business and verify ownership')</p>
                                </div>
                                
                                <div class="business-type-grid mb-4">
                                    @php
                                        $typeIcons = [
                                            'domain' => ['icon' => 'las la-globe', 'color' => '#3b82f6'],
                                            'website' => ['icon' => 'las la-laptop-code', 'color' => '#10b981'],
                                            'social_media_account' => ['icon' => 'las la-users', 'color' => '#8b5cf6'],
                                            'mobile_app' => ['icon' => 'las la-mobile-alt', 'color' => '#f59e0b'],
                                            'desktop_app' => ['icon' => 'las la-desktop', 'color' => '#ef4444'],
                                        ];
                                    @endphp
                                    
                                    @foreach($businessTypes as $key => $name)
                                        @if(($marketplaceSettings['allow_' . $key] ?? '1') == '1')
                                            <label class="business-type-card">
                                                <input type="radio" name="business_type" value="{{ $key }}" 
                                                       {{ old('business_type', $draftData['business_type'] ?? '') == $key ? 'checked' : '' }} required>
                                                <div class="card-inner">
                                                    <div class="type-icon" style="background: {{ $typeIcons[$key]['color'] ?? '#6b7280' }}20; color: {{ $typeIcons[$key]['color'] ?? '#6b7280' }}">
                                                        <i class="{{ $typeIcons[$key]['icon'] ?? 'las la-box' }}"></i>
                                                    </div>
                                                    <h6 class="type-name">{{ $name }}</h6>
                                                    <span class="check-mark"><i class="las la-check"></i></span>
                                                </div>
                                            </label>
                                        @endif
                                    @endforeach
                                </div>
                                
                                {{-- Domain Input Section --}}
                                <div id="domainInputSection" class="mb-4" style="display: none;">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary bg-opacity-10">
                                            <h6 class="mb-0">
                                                <i class="las la-globe me-2"></i>@lang('Domain Information')
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">@lang('Domain Name') <span class="text-danger">*</span></label>
                                                <div class="input-group input-group-lg">
                                                    <span class="input-group-text bg-light"><i class="las la-globe"></i></span>
                                                    <input type="text" name="domain_name" id="domainNameInput" class="form-control" 
                                                           value="{{ old('domain_name', $draftData['domain_name'] ?? '') }}" placeholder="https://example.com">
                                                </div>
                                                <small class="text-muted d-block mt-1">
                                                    <i class="las la-info-circle"></i> 
                                                    <span id="domainHelpText">@lang('Enter domain with http:// or https:// (e.g., https://example.com)')</span>
                                                </small>
                                                <div id="domainProtocolWarning" class="alert alert-warning alert-sm mt-2 mb-0" style="display: none;">
                                                    <i class="las la-exclamation-triangle"></i> 
                                                    <strong>@lang('Protocol Required'):</strong> 
                                                    @lang('Please start with http:// or https://')
                                                </div>
                                            </div>
                                            
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">@lang('Domain Registrar')</label>
                                                    <input type="text" name="domain_registrar" class="form-control" 
                                                           value="{{ old('domain_registrar', $draftData['domain_registrar'] ?? '') }}" placeholder="@lang('e.g., GoDaddy, Namecheap')">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">@lang('Expiry Date')</label>
                                                    <input type="date" name="domain_expiry" class="form-control" value="{{ old('domain_expiry', $draftData['domain_expiry'] ?? '') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Website Input Section --}}
                                <div id="websiteInputSection" class="mb-4" style="display: none;">
                                    <div class="card border-success">
                                        <div class="card-header bg-success bg-opacity-10">
                                            <h6 class="mb-0">
                                                <i class="las la-laptop-code me-2"></i>@lang('Website Information')
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">@lang('Website URL') <span class="text-danger">*</span></label>
                                                <div class="input-group input-group-lg">
                                                    <span class="input-group-text bg-light"><i class="las la-link"></i></span>
                                                    <input type="url" name="website_url" id="websiteUrlInput" class="form-control" 
                                                           value="{{ old('website_url', $draftData['website_url'] ?? '') }}" placeholder="https://example.com">
                                                </div>
                                                <small class="text-muted d-block mt-1">
                                                    <i class="las la-info-circle"></i> 
                                                    <span id="websiteHelpText">@lang('Enter full URL starting with http:// or https://')</span>
                                                </small>
                                                <div id="websiteProtocolWarning" class="alert alert-warning alert-sm mt-2 mb-0" style="display: none;">
                                                    <i class="las la-exclamation-triangle"></i> 
                                                    <strong>@lang('Protocol Required'):</strong> 
                                                    @lang('Please start with http:// or https://')
                                                </div>
                                            </div>
                                            
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">@lang('Website Niche')</label>
                                                    <input type="text" name="website_niche" class="form-control" 
                                                           value="{{ old('website_niche', $draftData['website_niche'] ?? '') }}" placeholder="@lang('e.g., Technology, Health, Finance')">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">@lang('Tech Stack / Platform')</label>
                                                    <input type="text" name="website_tech_stack" class="form-control" 
                                                           value="{{ old('website_tech_stack', $draftData['website_tech_stack'] ?? '') }}" placeholder="@lang('e.g., WordPress, Shopify, Laravel')">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">@lang('Domain Registrar')</label>
                                                    <input type="text" name="website_domain_registrar" class="form-control" 
                                                           value="{{ old('website_domain_registrar', $draftData['website_domain_registrar'] ?? '') }}" placeholder="@lang('e.g., GoDaddy, Namecheap')">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">@lang('Domain Expiry')</label>
                                                    <input type="date" name="website_domain_expiry" class="form-control" value="{{ old('website_domain_expiry', $draftData['website_domain_expiry'] ?? '') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Website/Domain Verification --}}
                                @include($activeTemplate . 'user.listing.partials.website-verification')
                                
                                <div class="step-actions mt-4 d-flex justify-content-between">
                                    <div></div>
                                    <button type="button" class="btn btn--base btn-next" data-next="2" id="step1ContinueBtn">
                                        @lang('Continue') <i class="las la-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>
                            
                            {{-- ============================================
                                 STEP 2: Business Details
                                 ============================================ --}}
                            <div class="form-step d-none" data-step="2">
                                <div class="step-header mb-4">
                                    <h5 class="fw-bold mb-1">@lang('Business Details')</h5>
                                    <p class="text-muted mb-0">@lang('Provide information about your business')</p>
                                </div>
                                
                                {{-- Social Media Fields --}}
                                <div class="business-fields social_media_account-fields d-none">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">@lang('Platform') <span class="text-danger">*</span></label>
                                            <select name="platform" class="form-select form-select-lg">
                                                <option value="">@lang('Select Platform')</option>
                                                @foreach($platforms as $key => $name)
                                                    <option value="{{ $key }}" {{ old('platform', $draftData['platform'] ?? '') == $key ? 'selected' : '' }}>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">@lang('Username / Handle') <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text bg-light">@</span>
                                                <input type="text" name="social_username" class="form-control" 
                                                       value="{{ old('social_username') }}" placeholder="username">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">@lang('Account URL')</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="las la-link"></i></span>
                                                <input type="url" name="social_url" class="form-control" 
                                                       value="{{ old('social_url') }}" placeholder="https://instagram.com/username">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">@lang('Followers')</label>
                                            <input type="number" name="followers_count" class="form-control" 
                                                   value="{{ old('followers_count') }}" placeholder="0" min="0">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">@lang('Engagement Rate') (%)</label>
                                            <input type="number" name="engagement_rate" class="form-control" 
                                                   value="{{ old('engagement_rate') }}" step="0.01" min="0" max="100" placeholder="0.00">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">@lang('Account Niche')</label>
                                            <input type="text" name="social_niche" class="form-control" 
                                                   value="{{ old('social_niche', $draftData['social_niche'] ?? '') }}" placeholder="@lang('e.g., Fashion, Gaming')">
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Mobile App Fields --}}
                                <div class="business-fields mobile_app-fields d-none">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">@lang('App Name') <span class="text-danger">*</span></label>
                                            <input type="text" name="app_name" class="form-control form-control-lg" 
                                                   value="{{ old('app_name') }}" placeholder="@lang('Your App Name')">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">@lang('Tech Stack')</label>
                                            <input type="text" name="mobile_tech_stack" class="form-control form-control-lg" 
                                                   value="{{ old('mobile_tech_stack', $draftData['mobile_tech_stack'] ?? '') }}" placeholder="@lang('e.g., React Native, Flutter')">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">@lang('App Store URL (iOS)')</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="lab la-apple"></i></span>
                                                <input type="url" name="app_store_url" class="form-control" 
                                                       value="{{ old('app_store_url') }}" placeholder="https://apps.apple.com/...">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">@lang('Play Store URL (Android)')</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="lab la-google-play"></i></span>
                                                <input type="url" name="play_store_url" class="form-control" 
                                                       value="{{ old('play_store_url') }}" placeholder="https://play.google.com/...">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">@lang('Total Downloads')</label>
                                            <input type="number" name="downloads_count" class="form-control" 
                                                   value="{{ old('downloads_count') }}" placeholder="0" min="0">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">@lang('Rating') (0-5)</label>
                                            <input type="number" name="app_rating" class="form-control" 
                                                   value="{{ old('app_rating') }}" step="0.1" min="0" max="5" placeholder="4.5">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">@lang('Active Users')</label>
                                            <input type="number" name="active_users" class="form-control" 
                                                   value="{{ old('active_users') }}" placeholder="0" min="0">
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Desktop App Fields --}}
                                <div class="business-fields desktop_app-fields d-none">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">@lang('App Name') <span class="text-danger">*</span></label>
                                            <input type="text" name="app_name" class="form-control form-control-lg" 
                                                   value="{{ old('app_name') }}" placeholder="@lang('Your App Name')">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">@lang('Tech Stack')</label>
                                            <input type="text" name="desktop_tech_stack" class="form-control form-control-lg" 
                                                   value="{{ old('desktop_tech_stack', $draftData['desktop_tech_stack'] ?? '') }}" placeholder="@lang('e.g., Electron, .NET, Java')">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">@lang('Download / Website URL')</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="las la-download"></i></span>
                                                <input type="url" name="desktop_url" class="form-control" 
                                                       value="{{ old('desktop_url') }}" placeholder="https://yourapp.com/download">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">@lang('Total Downloads')</label>
                                            <input type="number" name="downloads_count" class="form-control" 
                                                   value="{{ old('downloads_count') }}" placeholder="0" min="0">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">@lang('Active Users')</label>
                                            <input type="number" name="active_users" class="form-control" 
                                                   value="{{ old('active_users') }}" placeholder="0" min="0">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">@lang('Supported Platforms')</label>
                                            <input type="text" name="supported_platforms" class="form-control" 
                                                   value="{{ old('supported_platforms') }}" placeholder="@lang('Windows, Mac, Linux')">
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Common Fields for All Types --}}
                                <hr class="my-4">
                                
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">@lang('Category')</label>
                                        <select name="listing_category_id" class="form-select form-select-lg" id="listingCategory">
                                            <option value="">@lang('Select Category')</option>
                                            @foreach($listingCategories as $category)
                                                <option value="{{ $category->id }}" data-type="{{ $category->business_type }}" 
                                                        {{ old('listing_category_id', $draftData['listing_category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">@lang('Description') <span class="text-danger">*</span></label>
                                        <textarea name="description" class="form-control" rows="6" data-step-required="2"
                                                  placeholder="@lang('Describe your business in detail. Include information about traffic sources, monetization methods, growth potential, and what is included in the sale...')">{{ old('description', $draftData['description'] ?? '') }}</textarea>
                                        <small class="text-muted">@lang('Minimum 100 characters. Be detailed to attract serious buyers.')</small>
                                    </div>
                                </div>
                                
                                {{-- Financials Section (Hidden for domain type) --}}
                                <div class="mt-4 p-3 bg-light rounded financial-section">
                                    <h6 class="fw-bold mb-3"><i class="las la-chart-line me-2"></i>@lang('Financial Information')</h6>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">@lang('Monthly Revenue')</label>
                                            <div class="input-group">
                                                <span class="input-group-text">{{ gs()->cur_sym }}</span>
                                                <input type="number" name="monthly_revenue" class="form-control" 
                                                       value="{{ old('monthly_revenue') }}" step="0.01" min="0" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">@lang('Monthly Profit')</label>
                                            <div class="input-group">
                                                <span class="input-group-text">{{ gs()->cur_sym }}</span>
                                                <input type="number" name="monthly_profit" class="form-control" 
                                                       value="{{ old('monthly_profit') }}" step="0.01" min="0" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">@lang('Monthly Visitors')</label>
                                            <input type="number" name="monthly_visitors" class="form-control" 
                                                   value="{{ old('monthly_visitors') }}" min="0" placeholder="0">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">@lang('Page Views/Month')</label>
                                            <input type="number" name="monthly_page_views" class="form-control" 
                                                   value="{{ old('monthly_page_views') }}" min="0" placeholder="0">
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Confidential & NDA Section --}}
                                <div class="mt-4 p-3 border rounded">
                                    <h6 class="fw-bold mb-3"><i class="las la-shield-alt me-2"></i>@lang('Confidentiality & NDA Settings')</h6>
                                    <p class="text-muted small mb-3">@lang('Protect sensitive information by making your listing confidential and requiring an NDA before buyers can view details.')</p>
                                    
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_confidential" id="isConfidential" 
                                                       value="1" {{ old('is_confidential', $draftData['is_confidential'] ?? '') ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="isConfidential">
                                                    @lang('Make this listing confidential')
                                                </label>
                                                <small class="text-muted d-block mt-1">
                                                    @lang('Confidential listings hide sensitive details from unauthorized users.')
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12" id="ndaSection" style="display: none;">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="requires_nda" id="requiresNda" 
                                                       value="1" {{ old('requires_nda', $draftData['requires_nda'] ?? '') ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="requiresNda">
                                                    @lang('Require NDA before viewing details')
                                                </label>
                                                <small class="text-muted d-block mt-1">
                                                    @lang('Buyers must sign a Non-Disclosure Agreement before they can view confidential details of your listing.')
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12" id="confidentialReasonSection" style="display: none;">
                                            <label class="form-label fw-semibold">@lang('Reason for Confidentiality')</label>
                                            <textarea name="confidential_reason" class="form-control" rows="3" 
                                                      placeholder="@lang('Explain why this listing is confidential (e.g., sensitive financial data, proprietary technology, etc.)')">{{ old('confidential_reason', $draftData['confidential_reason'] ?? '') }}</textarea>
                                            <small class="text-muted">@lang('This information helps buyers understand why an NDA is required.')</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="step-actions mt-4 d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary btn-prev" data-prev="1">
                                        <i class="las la-arrow-left me-1"></i> @lang('Back')
                                    </button>
                                    <button type="button" class="btn btn--base btn-next" data-next="3">
                                        @lang('Continue') <i class="las la-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>
                            
                            {{-- ============================================
                                 STEP 3: Sale Type & Pricing
                                 ============================================ --}}
                            <div class="form-step d-none" data-step="3">
                                <div class="step-header mb-4">
                                    <h5 class="fw-bold mb-1">@lang('How do you want to sell?')</h5>
                                    <p class="text-muted mb-0">@lang('Choose your sale method and set your price')</p>
                                </div>
                                
                                {{-- Sale Type Selection --}}
                                <div class="sale-type-grid mb-4">
                                    @if(($marketplaceSettings['allow_fixed_price'] ?? '1') == '1')
                                        <label class="sale-type-card">
                                            <input type="radio" name="sale_type" value="fixed_price" 
                                                   {{ old('sale_type', $draftData['sale_type'] ?? 'fixed_price') == 'fixed_price' ? 'checked' : '' }} required>
                                            <div class="card-inner">
                                                <div class="sale-icon">
                                                    <i class="las la-tag"></i>
                                                </div>
                                                <div class="sale-info">
                                                    <h6 class="mb-1">@lang('Fixed Price')</h6>
                                                    <p class="small text-muted mb-0">@lang('Set a specific price for your business')</p>
                                                </div>
                                                <span class="check-mark"><i class="las la-check"></i></span>
                                            </div>
                                        </label>
                                    @endif
                                    
                                    @if(($marketplaceSettings['allow_auctions'] ?? '1') == '1')
                                        <label class="sale-type-card">
                                            <input type="radio" name="sale_type" value="auction" 
                                                   {{ old('sale_type', $draftData['sale_type'] ?? '') == 'auction' ? 'checked' : '' }}>
                                            <div class="card-inner">
                                                <div class="sale-icon auction">
                                                    <i class="las la-gavel"></i>
                                                </div>
                                                <div class="sale-info">
                                                    <h6 class="mb-1">@lang('Auction')</h6>
                                                    <p class="small text-muted mb-0">@lang('Let buyers bid for your business')</p>
                                                </div>
                                                <span class="check-mark"><i class="las la-check"></i></span>
                                            </div>
                                        </label>
                                    @endif
                                </div>
                                
                                {{-- Fixed Price Fields --}}
                                <div class="pricing-fields fixed-price-fields">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">@lang('Asking Price') <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text bg-light">{{ gs()->cur_sym }}</span>
                                                <input type="number" name="asking_price" class="form-control" 
                                                       value="{{ old('asking_price', $draftData['asking_price'] ?? '') }}" step="0.01" min="1" placeholder="0.00">
                                            </div>
                                            <small class="text-muted">@lang('The price you want to sell for')</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">@lang('Allow Offers?')</label>
                                            <select name="allow_offers" class="form-select form-select-lg">
                                                <option value="1" {{ old('allow_offers', $draftData['allow_offers'] ?? '1') == '1' ? 'selected' : '' }}>@lang('Yes, accept offers from buyers')</option>
                                                <option value="0" {{ old('allow_offers', $draftData['allow_offers'] ?? '') == '0' ? 'selected' : '' }}>@lang('No, fixed price only')</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Auction Fields --}}
                                <div class="pricing-fields auction-fields d-none">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">@lang('Starting Bid') <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text bg-light">{{ gs()->cur_sym }}</span>
                                                <input type="number" name="starting_bid" class="form-control" 
                                                       value="{{ old('starting_bid', $draftData['starting_bid'] ?? '') }}" step="0.01" min="1" placeholder="0.00">
                                            </div>
                                            <small class="text-muted">@lang('Minimum bid to start the auction')</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">@lang('Reserve Price') <small class="text-muted">(@lang('Optional'))</small></label>
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text bg-light">{{ gs()->cur_sym }}</span>
                                                <input type="number" name="reserve_price" class="form-control" 
                                                       value="{{ old('reserve_price', $draftData['reserve_price'] ?? '') }}" step="0.01" min="0" placeholder="0.00">
                                            </div>
                                            <small class="text-muted">@lang('Minimum price you will accept (hidden from buyers)')</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">@lang('Buy Now Price') <small class="text-muted">(@lang('Optional'))</small></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">{{ gs()->cur_sym }}</span>
                                                <input type="number" name="buy_now_price" class="form-control" 
                                                       value="{{ old('buy_now_price', $draftData['buy_now_price'] ?? '') }}" step="0.01" min="0" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">@lang('Bid Increment')</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">{{ gs()->cur_sym }}</span>
                                                <input type="number" name="bid_increment" class="form-control" 
                                                       value="{{ old('bid_increment', $draftData['bid_increment'] ?? 10) }}" step="0.01" min="1" placeholder="10.00">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">@lang('Auction Duration')</label>
                                            @php
                                                $maxDays = $marketplaceSettings['max_auction_days'] ?? 30;
                                            @endphp
                                            <select name="auction_duration" class="form-select">
                                                <option value="3" {{ old('auction_duration', $draftData['auction_duration'] ?? '') == '3' ? 'selected' : '' }}>3 @lang('days')</option>
                                                <option value="5" {{ old('auction_duration', $draftData['auction_duration'] ?? '') == '5' ? 'selected' : '' }}>5 @lang('days')</option>
                                                <option value="7" {{ old('auction_duration', $draftData['auction_duration'] ?? '7') == '7' ? 'selected' : '' }}>7 @lang('days')</option>
                                                <option value="10" {{ old('auction_duration', $draftData['auction_duration'] ?? '') == '10' ? 'selected' : '' }}>10 @lang('days')</option>
                                                <option value="14" {{ old('auction_duration', $draftData['auction_duration'] ?? '') == '14' ? 'selected' : '' }}>14 @lang('days')</option>
                                                @if($maxDays >= 21)
                                                    <option value="21" {{ old('auction_duration', $draftData['auction_duration'] ?? '') == '21' ? 'selected' : '' }}>21 @lang('days')</option>
                                                @endif
                                                @if($maxDays >= 30)
                                                    <option value="30" {{ old('auction_duration', $draftData['auction_duration'] ?? '') == '30' ? 'selected' : '' }}>30 @lang('days')</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="step-actions mt-4 d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary btn-prev" data-prev="2">
                                        <i class="las la-arrow-left me-1"></i> @lang('Back')
                                    </button>
                                    <button type="button" class="btn btn--base btn-next" data-next="4">
                                        @lang('Continue') <i class="las la-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>
                            
                            {{-- ============================================
                                 STEP 4: Media Upload
                                 ============================================ --}}
                            <div class="form-step d-none" data-step="4">
                                <div class="step-header mb-4">
                                    <h5 class="fw-bold mb-1">@lang('Review & Submit')</h5>
                                    <p class="text-muted mb-0">@lang('Review your listing and submit for approval')</p>
                                </div>
                                
                                {{-- Domain Information Message (for domain type) --}}
                                <div class="domain-info-message d-none mb-4">
                                    @php
                                        $requiresApproval = ($marketplaceSettings['listing_approval_required'] ?? '1') == '1';
                                    @endphp
                                    <div class="alert alert-info border-0 shadow-sm">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-shrink-0 me-3">
                                                <i class="las la-info-circle" style="font-size: 2rem; color: #0dcaf0;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="alert-heading mb-2">
                                                    <i class="las la-globe me-2"></i>@lang('Domain Listing Information')
                                                </h5>
                                                @if($requiresApproval)
                                                    <p class="mb-2">
                                                        @lang('By clicking submit, you have submitted your domain listing to our admin team. They will review and approve it - this usually takes less than a day.')
                                                    </p>
                                                    <p class="mb-0 small text-muted">
                                                        <i class="las la-clock me-1"></i>
                                                        @lang('You will be notified once your listing is approved and goes live.')
                                                    </p>
                                                @else
                                                    <p class="mb-0">
                                                        @lang('Your domain listing will be published immediately after submission and will be visible to all buyers.')
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-center text-muted small mt-3">
                                        <i class="las la-globe me-1"></i>
                                        @lang('Your domain will be displayed as a card with a colored background.')
                                    </p>
                                </div>
                                
                                {{-- Image Upload (hidden for domain type) --}}
                                <div class="image-upload-section">
                                <div class="upload-area" id="uploadArea">
                                    <div class="upload-placeholder">
                                        <i class="las la-cloud-upload-alt"></i>
                                        <h6>@lang('Drag & Drop Images Here')</h6>
                                        <p class="text-muted mb-2">@lang('or')</p>
                                        <label class="btn btn--base btn-sm">
                                            <i class="las la-folder-open me-1"></i> @lang('Browse Files')
                                            <input type="file" name="images[]" id="imageInput" multiple accept="image/*" class="d-none">
                                        </label>
                                        <p class="text-muted mt-3 small">
                                            @lang('Upload up to') {{ $marketplaceSettings['max_images_per_listing'] ?? 10 }} @lang('images') 
                                            • @lang('Max 2MB each') • @lang('JPG, PNG, GIF')
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="image-preview-grid mt-3" id="imagePreview"></div>
                                
                                <div class="alert alert-light border mt-4">
                                    <h6 class="mb-2"><i class="las la-lightbulb text-warning me-2"></i>@lang('Tips for Great Images')</h6>
                                    <ul class="mb-0 small">
                                        <li>@lang('Include screenshots of traffic stats and analytics')</li>
                                        <li>@lang('Show revenue/earnings proof if applicable')</li>
                                        <li>@lang('Capture the homepage and key pages')</li>
                                        <li>@lang('First image will be used as the thumbnail')</li>
                                    </ul>
                                    </div>
                                </div>
                                
                                <div class="step-actions mt-4 d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary btn-prev" data-prev="3">
                                        <i class="las la-arrow-left me-1"></i> @lang('Back')
                                    </button>
                                    <button type="submit" class="btn btn--base" id="submitListingBtn">
                                        <i class="las la-check-circle me-1"></i> @lang('Submit Listing')
                                    </button>
                                </div>
                            </div>
                            
                            {{-- Step 5 removed - verification is now in Step 1 --}}
                            {{-- All Step 5 content has been moved to Step 1 to prevent duplicate IDs --}}
                            
                        </form>
                    </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>
</section>
@endsection

@push('style')
<link rel="stylesheet" href="{{ asset('assets/templates/basic/css/listing-form.css') }}">
@endpush

@push('script')
<script src="{{ asset('assets/templates/basic/js/listing-form.js') }}"></script>
<script>
$(document).ready(function() {
    // Initialize the Listing Form Handler
    ListingFormHandler.init({
        draftSaveUrl: '{{ route("user.listing.draft.save") }}',
        draftClearUrl: '{{ route("user.listing.draft.clear") }}',
        maxImages: {{ $marketplaceSettings['max_images_per_listing'] ?? 10 }},
        hasDraft: {{ !empty($draftData) ? 'true' : 'false' }},
        currentStage: {{ $currentStage ?? 1 }}
    });

    // Website/Domain Verification Handler
    @if(\App\Models\MarketplaceSetting::requireWebsiteVerification() || \App\Models\MarketplaceSetting::requireDomainVerification())
    window.WebsiteVerificationHandler = {
        init: function() {
            this.bindEvents();
            this.checkVerificationRequirement();
        },

        bindEvents: function() {
            // Method change
            $('#websiteVerificationMethod').on('change', this.onMethodChange.bind(this));

            // Verify button
            $('#verifyWebsiteBtn').on('click', this.verifyWebsite.bind(this));

            // Download file button
            $('#downloadWebsiteTxtFile').on('click', this.downloadTxtFile.bind(this));

            // Business type change
            $('input[name="business_type"]').on('change', this.checkVerificationRequirement.bind(this));

            // Website/Domain URL change
            $('#website_url, #domain_name').on('input', this.checkVerificationRequirement.bind(this));
        },

        checkVerificationRequirement: function() {
            var businessType = $('input[name="business_type"]:checked').val();
            var hasUrl = false;

            if (businessType === 'website') {
                hasUrl = $('#website_url').val().trim() !== '';
            } else if (businessType === 'domain') {
                hasUrl = $('#domain_name').val().trim() !== '';
            }

            var requiresVerification = false;
            @if(\App\Models\MarketplaceSetting::requireDomainVerification())
                if (businessType === 'domain') requiresVerification = true;
            @endif
            @if(\App\Models\MarketplaceSetting::requireWebsiteVerification())
                if (businessType === 'website') requiresVerification = true;
            @endif

            if (requiresVerification && hasUrl) {
                $('#websiteVerificationSection').show();
                // Trigger method change to show correct content, then generate data
                this.onMethodChange();
                setTimeout(function() {
                    this.generateVerificationData();
                }.bind(this), 100);
            } else {
                $('#websiteVerificationSection').hide();
                $('#websiteVerified').val('0');
            }
        },

        generateVerificationData: function() {
            var businessType = $('input[name="business_type"]:checked').val();
            var url = businessType === 'website' ? $('#website_url').val() : $('#domain_name').val();

            console.log('generateVerificationData called', {
                businessType: businessType,
                url: url,
                method: $('#websiteVerificationMethod').val()
            });

            if (!url) {
                console.log('No URL provided, returning');
                return;
            }

            this.showStatus('Generating verification data...', 'info');

            // Generate verification data
            $.ajax({
                url: '{{ route("user.verification.generate") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    domain: url,
                    method: $('#websiteVerificationMethod').val() || 'txt_file'
                },
                success: function(response) {
                    console.log('AJAX success response:', response);
                    console.log('Response type:', typeof response);
                    console.log('Response keys:', Object.keys(response));

                    if (response.success) {
                        console.log('Response successful, updating UI');
                        console.log('Token:', response.token);
                        console.log('Filename:', response.filename);
                        console.log('Method:', response.method);

                        $('#websiteVerificationToken').val(response.token || '');
                        $('#websiteVerificationFilename').val(response.filename || '');
                        $('#websiteVerificationDnsName').val(response.dns_name || '');

                        // Force method selection based on response
                        if (response.method) {
                            $('#websiteVerificationMethod').val(response.method);
                        }

                        this.updateVerificationUI(response);
                        this.showStatus('Verification data generated successfully', 'success');
                    } else {
                        console.log('Response not successful:', response);
                        this.showStatus(response.message || 'Failed to generate verification data', 'danger');
                    }
                }.bind(this),
                error: function(xhr, status, error) {
                    console.error('AJAX error:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText,
                        responseJSON: xhr.responseJSON
                    });

                    var errorMsg = xhr.responseJSON?.message || 'Failed to generate verification data';
                    this.showStatus(errorMsg, 'danger');
                }.bind(this)
            });
        },

        updateVerificationUI: function(data) {
            console.log('updateVerificationUI called with data:', data);

            var method = data.method || $('#websiteVerificationMethod').val() || 'txt_file';
            console.log('Using method:', method);

            // Update the method dropdown to match the response
            if (data.method) {
                $('#websiteVerificationMethod').val(data.method);
            }

            // Hide all method content first
            $('.verification-method-content').hide();

            if (method === 'txt_file') {
                console.log('Updating TXT file UI');

                var filename = data.filename || 'verification.txt';
                var domain = data.domain || 'yourdomain.com';
                var token = data.content || data.token || '';

                $('#websiteTxtFileName').text(filename);
                $('#websiteTxtFileLocation').text('https://' + domain + '/');
                $('#websiteTxtFileContent').text(token);
                $('#websiteTxtFileUrl').text('https://' + domain + '/' + filename);

                $('#websiteTxtFileMethod').show();
                console.log('TXT file UI updated - filename:', filename, 'token:', token);

            } else if (method === 'dns_record') {
                console.log('Updating DNS record UI');

                var dnsName = data.dns_name || '_verify';
                var dnsValue = data.dns_value || data.token || '';

                $('#websiteDnsRecordName').text(dnsName);
                $('#websiteDnsRecordValue').text(dnsValue);

                $('#websiteDnsRecordMethod').show();
                console.log('DNS record UI updated - name:', dnsName, 'value:', dnsValue);
            }

            console.log('UI update complete');
        },

        onMethodChange: function() {
            var method = $('#websiteVerificationMethod').val();

            $('.verification-method-content').hide();
            $('#websiteVerified').val('0');

            if (method === 'txt_file') {
                $('#websiteTxtFileMethod').show();
            } else if (method === 'dns_record') {
                $('#websiteDnsRecordMethod').show();
            }

            this.generateVerificationData();
        },

        verifyWebsite: function() {
            var verificationId = $('#websiteVerificationToken').val();
            var method = $('#websiteVerificationMethod').val();

            if (!verificationId) {
                this.showStatus('Please select a verification method first.', 'danger');
                return;
            }

            $('#verifyWebsiteBtn').prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i>Verifying...');
            this.showStatus('Verifying ownership...', 'info');

            $.ajax({
                url: '{{ route("user.verification.verify") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    domain: $('input[name="business_type"]:checked').val() === 'website' ? $('#website_url').val() : $('#domain_name').val(),
                    method: $('#websiteVerificationMethod').val(),
                    token: $('#websiteVerificationToken').val(),
                    filename: $('#websiteVerificationFilename').val(),
                    dns_name: $('#websiteVerificationDnsName').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#websiteVerified').val('1');
                        this.showStatus('Domain ownership verified successfully!', 'success');
                        $('#verifyWebsiteBtn').html('<i class="las la-check-circle me-1"></i>Verified');
                        $('#verifyWebsiteBtn').removeClass('btn--base').addClass('btn-success');
                    } else {
                        this.showStatus(response.message, 'danger');
                        $('#verifyWebsiteBtn').prop('disabled', false).html('<i class="las la-check-circle me-1"></i>Verify Ownership');
                    }
                }.bind(this),
                error: function(xhr) {
                    var response = xhr.responseJSON;
                    var message = response && response.message ? response.message : 'Verification failed. Please try again.';
                    this.showStatus(message, 'danger');
                    $('#verifyWebsiteBtn').prop('disabled', false).html('<i class="las la-check-circle me-1"></i>Verify Ownership');
                }.bind(this)
            });
        },

        downloadTxtFile: function() {
            var token = $('#websiteVerificationToken').val();
            var filename = $('#websiteVerificationFilename').val();
            var domain = $('input[name="business_type"]:checked').val() === 'website' ? $('#website_url').val() : $('#domain_name').val();

            if (token && filename) {
                var url = '{{ route("user.verification.download") }}?token=' + encodeURIComponent(token) +
                         '&filename=' + encodeURIComponent(filename) +
                         '&domain=' + encodeURIComponent(domain);
                window.open(url, '_blank');
            }
        },

        showStatus: function(message, type) {
            var statusEl = $('#websiteVerificationStatus');
            statusEl.removeClass('text-success text-danger text-info text-warning');
            statusEl.addClass('text-' + type);
            statusEl.html('<i class="las la-info-circle me-1"></i>' + message);
        }
    };

    // Initialize verification handler
    window.WebsiteVerificationHandler.init();
    @endif
});
</script>
@endpush
