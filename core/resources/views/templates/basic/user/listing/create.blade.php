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
                            <span class="step-text">@lang('Business Type')</span>
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
                        <h5 class="mb-0">
                            <i class="las la-plus-circle text--base me-2"></i>
                            @lang('Create New Listing')
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('user.listing.store') }}" method="POST" enctype="multipart/form-data" id="listingForm">
                            @csrf
                            
                            {{-- ============================================
                                 STEP 1: Business Type Selection
                                 ============================================ --}}
                            <div class="form-step" data-step="1">
                                <div class="step-header mb-4">
                                    <h5 class="fw-bold mb-1">@lang('What are you selling?')</h5>
                                    <p class="text-muted mb-0">@lang('Select the type of online business you want to list')</p>
                                </div>
                                
                                <div class="business-type-grid">
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
                                                       {{ old('business_type') == $key ? 'checked' : '' }} required>
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
                                
                                <div class="step-actions mt-4 text-end">
                                    <button type="button" class="btn btn--base btn-next" data-next="2">
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
                                
                                {{-- Domain Fields --}}
                                <div class="business-fields domain-fields d-none">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">@lang('Domain Name') <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text bg-light"><i class="las la-globe"></i></span>
                                                <input type="text" name="domain_name" id="domainNameInput" class="form-control" 
                                                       value="{{ old('domain_name') }}" placeholder="https://example.com">
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
                                        
                                        @include('templates.basic.user.listing.partials.website-verification')
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">@lang('Domain Registrar')</label>
                                            <input type="text" name="domain_registrar" class="form-control" 
                                                   value="{{ old('domain_registrar') }}" placeholder="@lang('e.g., GoDaddy, Namecheap')">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">@lang('Expiry Date')</label>
                                            <input type="date" name="domain_expiry" class="form-control" value="{{ old('domain_expiry') }}">
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Website Fields --}}
                                <div class="business-fields website-fields d-none">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">@lang('Website URL') <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text bg-light"><i class="las la-link"></i></span>
                                                <input type="url" name="website_url" id="websiteUrlInput" class="form-control" 
                                                       value="{{ old('website_url') }}" placeholder="https://example.com">
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
                                        
                                        @include('templates.basic.user.listing.partials.website-verification')
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">@lang('Website Niche')</label>
                                            <input type="text" name="niche" class="form-control" 
                                                   value="{{ old('niche') }}" placeholder="@lang('e.g., Technology, Health, Finance')">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">@lang('Tech Stack / Platform')</label>
                                            <input type="text" name="tech_stack" class="form-control" 
                                                   value="{{ old('tech_stack') }}" placeholder="@lang('e.g., WordPress, Shopify, Laravel')">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">@lang('Domain Registrar')</label>
                                            <input type="text" name="domain_registrar" class="form-control" 
                                                   value="{{ old('domain_registrar') }}" placeholder="@lang('e.g., GoDaddy, Namecheap')">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">@lang('Domain Expiry')</label>
                                            <input type="date" name="domain_expiry" class="form-control" value="{{ old('domain_expiry') }}">
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Social Media Fields --}}
                                <div class="business-fields social_media_account-fields d-none">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">@lang('Platform') <span class="text-danger">*</span></label>
                                            <select name="platform" class="form-select form-select-lg">
                                                <option value="">@lang('Select Platform')</option>
                                                @foreach($platforms as $key => $name)
                                                    <option value="{{ $key }}" {{ old('platform') == $key ? 'selected' : '' }}>{{ $name }}</option>
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
                                            <input type="text" name="niche" class="form-control" 
                                                   value="{{ old('niche') }}" placeholder="@lang('e.g., Fashion, Gaming')">
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
                                            <input type="text" name="tech_stack" class="form-control form-control-lg" 
                                                   value="{{ old('tech_stack') }}" placeholder="@lang('e.g., React Native, Flutter')">
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
                                            <input type="text" name="tech_stack" class="form-control form-control-lg" 
                                                   value="{{ old('tech_stack') }}" placeholder="@lang('e.g., Electron, .NET, Java')">
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
                                                        {{ old('listing_category_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">@lang('Description') <span class="text-danger">*</span></label>
                                        <textarea name="description" class="form-control" rows="6" required
                                                  placeholder="@lang('Describe your business in detail. Include information about traffic sources, monetization methods, growth potential, and what is included in the sale...')">{{ old('description') }}</textarea>
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
                                                       value="1" {{ old('is_confidential') ? 'checked' : '' }}>
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
                                                       value="1" {{ old('requires_nda') ? 'checked' : '' }}>
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
                                                      placeholder="@lang('Explain why this listing is confidential (e.g., sensitive financial data, proprietary technology, etc.)')">{{ old('confidential_reason') }}</textarea>
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
                                                   {{ old('sale_type', 'fixed_price') == 'fixed_price' ? 'checked' : '' }} required>
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
                                                   {{ old('sale_type') == 'auction' ? 'checked' : '' }}>
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
                                                       value="{{ old('asking_price') }}" step="0.01" min="1" placeholder="0.00">
                                            </div>
                                            <small class="text-muted">@lang('The price you want to sell for')</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">@lang('Allow Offers?')</label>
                                            <select name="allow_offers" class="form-select form-select-lg">
                                                <option value="1" {{ old('allow_offers', '1') == '1' ? 'selected' : '' }}>@lang('Yes, accept offers from buyers')</option>
                                                <option value="0" {{ old('allow_offers') == '0' ? 'selected' : '' }}>@lang('No, fixed price only')</option>
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
                                                       value="{{ old('starting_bid') }}" step="0.01" min="1" placeholder="0.00">
                                            </div>
                                            <small class="text-muted">@lang('Minimum bid to start the auction')</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">@lang('Reserve Price') <small class="text-muted">(@lang('Optional'))</small></label>
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text bg-light">{{ gs()->cur_sym }}</span>
                                                <input type="number" name="reserve_price" class="form-control" 
                                                       value="{{ old('reserve_price') }}" step="0.01" min="0" placeholder="0.00">
                                            </div>
                                            <small class="text-muted">@lang('Minimum price you will accept (hidden from buyers)')</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">@lang('Buy Now Price') <small class="text-muted">(@lang('Optional'))</small></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">{{ gs()->cur_sym }}</span>
                                                <input type="number" name="buy_now_price" class="form-control" 
                                                       value="{{ old('buy_now_price') }}" step="0.01" min="0" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">@lang('Bid Increment')</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">{{ gs()->cur_sym }}</span>
                                                <input type="number" name="bid_increment" class="form-control" 
                                                       value="{{ old('bid_increment', 10) }}" step="0.01" min="1" placeholder="10.00">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">@lang('Auction Duration')</label>
                                            @php
                                                $maxDays = $marketplaceSettings['max_auction_days'] ?? 30;
                                            @endphp
                                            <select name="auction_duration" class="form-select">
                                                <option value="3" {{ old('auction_duration') == '3' ? 'selected' : '' }}>3 @lang('days')</option>
                                                <option value="5" {{ old('auction_duration') == '5' ? 'selected' : '' }}>5 @lang('days')</option>
                                                <option value="7" {{ old('auction_duration', '7') == '7' ? 'selected' : '' }}>7 @lang('days')</option>
                                                <option value="10" {{ old('auction_duration') == '10' ? 'selected' : '' }}>10 @lang('days')</option>
                                                <option value="14" {{ old('auction_duration') == '14' ? 'selected' : '' }}>14 @lang('days')</option>
                                                @if($maxDays >= 21)
                                                    <option value="21" {{ old('auction_duration') == '21' ? 'selected' : '' }}>21 @lang('days')</option>
                                                @endif
                                                @if($maxDays >= 30)
                                                    <option value="30" {{ old('auction_duration') == '30' ? 'selected' : '' }}>30 @lang('days')</option>
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
                                    <h5 class="fw-bold mb-1">@lang('Add Images')</h5>
                                    <p class="text-muted mb-0">@lang('Upload screenshots and images of your business')</p>
                                </div>
                                
                                {{-- Domain Card Preview (for domain type) --}}
                                <div class="domain-card-preview d-none mb-4">
                                    <div class="card border-0 shadow-sm" style="max-width: 400px; margin: 0 auto; overflow: hidden;">
                                        <div class="domain-card-image" id="domainCardImage" style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative;">
                                            <div class="position-absolute top-0 start-0 m-2">
                                                <span class="badge bg-success">
                                                    <i class="las la-check-circle"></i> @lang('Verified')
                                                </span>
                                            </div>
                                            <div class="text-center text-white" style="z-index: 1;">
                                                <i class="las la-globe mb-2" style="font-size: 3rem; opacity: 0.3;"></i>
                                                <div class="position-relative">
                                                    <div class="position-absolute top-0 start-50 translate-middle-x" style="width: 80px; height: 2px; background: rgba(255,255,255,0.5); transform: translateX(-50%);"></div>
                                                </div>
                                                <h3 class="domain-name-preview mb-0 mt-3 fw-bold text-white" id="domainNamePreview" style="font-size: 1.75rem; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">example.com</h3>
                                            </div>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg--base bg-opacity-10 text--base">
                                                    <i class="las la-globe"></i> @lang('Domain')
                                                </span>
                                                <small class="text-muted">@lang('Premium Domains')</small>
                                            </div>
                                            <h5 class="card-title mb-2 fw-semibold" id="domainTitlePreview">@lang('Domain Name')</h5>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small class="text-muted d-block">@lang('Asking Price')</small>
                                                    <span class="text--base fw-bold" id="domainPricePreview">$0.00 USD</span>
                                                </div>
                                                <div class="text-end">
                                                    <small class="text-muted d-block">
                                                        <i class="las la-eye"></i> 0
                                                    </small>
                                                    <small class="text-muted d-block">
                                                        <i class="las la-heart"></i> 0
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-center text-muted small mt-3">
                                        @lang('For domain listings, the domain name will be displayed as a card with a colored background. Images are optional.')
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
                                    <button type="submit" class="btn btn--base btn-lg">
                                        <i class="las la-paper-plane me-1"></i> @lang('Submit for Review')
                                    </button>
                                </div>
                            </div>
                            
                        </form>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>
</section>
@endsection

@push('style')
<style>
    .alert-sm {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        margin-bottom: 0;
    }
    .border-warning {
        border-color: #ffc107 !important;
    }
    .is-invalid.border-warning:focus {
        border-color: #ffc107 !important;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }
</style>
@endpush

@push('script')
<script>
$(document).ready(function() {
    let currentStep = 1;
    const totalSteps = 4;
    
    // Step Navigation
    function showStep(step) {
        $('.form-step').addClass('d-none');
        $(`.form-step[data-step="${step}"]`).removeClass('d-none');
        
        // Update progress
        $('.progress-steps .step').removeClass('active completed');
        $('.progress-steps .step').each(function() {
            const stepNum = $(this).data('step');
            if (stepNum < step) {
                $(this).addClass('completed');
            } else if (stepNum === step) {
                $(this).addClass('active');
            }
        });
        
        currentStep = step;
        window.scrollTo({top: 0, behavior: 'smooth'});
    }
    
    // Next button
    $(document).on('click', '.btn-next', function() {
        const nextStep = $(this).data('next');
        
        // Validation for step 1
        if (currentStep === 1) {
            if (!$('input[name="business_type"]:checked').val()) {
                notify('error', '@lang("Please select a business type")');
                return;
            }
        }
        
        // Validation for step 2
        if (currentStep === 2) {
            if (!$('textarea[name="description"]').val()) {
                notify('error', '@lang("Please enter a description")');
                return;
            }
        }
        
        // Validation for step 3
        if (currentStep === 3) {
            const saleType = $('input[name="sale_type"]:checked').val();
            if (saleType === 'fixed_price' && !$('input[name="asking_price"]').val()) {
                notify('error', '@lang("Please enter an asking price")');
                return;
            }
            if (saleType === 'auction' && !$('input[name="starting_bid"]').val()) {
                notify('error', '@lang("Please enter a starting bid")');
                return;
            }
        }
        
        showStep(nextStep);
    });
    
    // Previous button
    $(document).on('click', '.btn-prev', function() {
        const prevStep = $(this).data('prev');
        showStep(prevStep);
    });
    
    // Business type change - show relevant fields
    $('input[name="business_type"]').on('change', function() {
        const type = $(this).val();
        const requireDomainVerification = {{ \App\Models\MarketplaceSetting::requireDomainVerification() ? 'true' : 'false' }};
        const requireWebsiteVerification = {{ \App\Models\MarketplaceSetting::requireWebsiteVerification() ? 'true' : 'false' }};
        
        // Remove required attribute from all business-specific fields first
        $('#domainNameInput').removeAttr('required');
        $('#websiteUrlInput').removeAttr('required');
        
        // Hide all business fields
        $('.business-fields').addClass('d-none');
        
        // Show relevant fields and add required attribute
        $(`.${type}-fields`).removeClass('d-none');
        
        // Hide/show financial section based on business type
        if (type === 'domain') {
            $('.financial-section').addClass('d-none');
            $('#domainNameInput').attr('required', 'required');
        } else {
            $('.financial-section').removeClass('d-none');
            if (type === 'website') {
                $('#websiteUrlInput').attr('required', 'required');
            }
        }
        
        // Update domain card preview when domain is entered
        if (type === 'domain') {
            setTimeout(function() {
                updateDomainCardPreview();
            }, 100);
        }
        
        // Show/hide image upload section based on business type
        if (type === 'domain') {
            $('.domain-card-preview').removeClass('d-none');
            $('.image-upload-section').addClass('d-none');
        } else {
            $('.domain-card-preview').addClass('d-none');
            $('.image-upload-section').removeClass('d-none');
        }
        
        if (type === 'domain' && requireDomainVerification) {
            setTimeout(function() {
                const domainValue = $('#domainNameInput').val().trim();
                if (domainValue) {
                    $('#domainNameInput').trigger('input');
                } else {
                    $('.domain-fields').find('#websiteVerificationSection').slideUp();
                }
            }, 100);
        } else if (type === 'domain' && !requireDomainVerification) {
            $('.domain-fields').find('#websiteVerificationSection').slideUp();
        } else if (type !== 'domain') {
            $('.domain-fields').find('#websiteVerificationSection').slideUp();
        }
        
        if (type === 'website' && requireWebsiteVerification) {
            const websiteValue = $('#websiteUrlInput').val().trim();
            if (websiteValue) {
                $('#websiteUrlInput').trigger('input');
            } else {
                $('.website-fields #websiteVerificationSection').slideUp();
            }
        } else if (type === 'website' && !requireWebsiteVerification) {
            $('.website-fields #websiteVerificationSection').slideUp();
        }
        
        // Filter categories
        $('#listingCategory option').each(function() {
            const optionType = $(this).data('type');
            if (!optionType || optionType === type) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        $('#listingCategory').val('');
    });
    
    // Sale type change - show relevant pricing fields
    $('input[name="sale_type"]').on('change', function() {
        const type = $(this).val();
        
        if (type === 'auction') {
            $('.fixed-price-fields').addClass('d-none');
            $('.auction-fields').removeClass('d-none');
        } else {
            $('.fixed-price-fields').removeClass('d-none');
            $('.auction-fields').addClass('d-none');
        }
    });
    
    // Initialize sale type
    $('input[name="sale_type"]:checked').trigger('change');
    
    // Confidential & NDA toggle logic
    $('#isConfidential').on('change', function() {
        if ($(this).is(':checked')) {
            $('#ndaSection').slideDown();
            $('#confidentialReasonSection').slideDown();
        } else {
            $('#ndaSection').slideUp();
            $('#confidentialReasonSection').slideUp();
            $('#requiresNda').prop('checked', false);
        }
    });
    
    // Initialize confidential section if pre-checked
    if ($('#isConfidential').is(':checked')) {
        $('#ndaSection').show();
        $('#confidentialReasonSection').show();
    }
    
    // Initialize business type if pre-selected
    const preselectedType = $('input[name="business_type"]:checked').val();
    if (preselectedType) {
        // Set required attribute for pre-selected type
        if (preselectedType === 'domain') {
            $('#domainNameInput').attr('required', 'required');
            $('.financial-section').addClass('d-none');
            $('.domain-card-preview').removeClass('d-none');
            $('.image-upload-section').addClass('d-none');
        } else if (preselectedType === 'website') {
            $('#websiteUrlInput').attr('required', 'required');
        }
        $('input[name="business_type"]:checked').trigger('change');
    } else {
        // Hide financial section and domain card by default
        $('.financial-section').addClass('d-none');
        $('.domain-card-preview').addClass('d-none');
    }
    
    // ============================================
    // Domain/Website Verification Logic
    // ============================================
    @php
        // Get site name prefix for verification (sanitized, lowercase, max 10 chars)
        $siteName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', gs('site_name') ?? 'marketplace'));
        $siteNamePrefix = substr($siteName, 0, 10) ?: 'marketplace';
    @endphp
    const siteNamePrefix = '{{ $siteNamePrefix }}';
    
    let domainVerificationData = {
        token: null,
        filename: null,
        dnsName: null,
        domain: null
    };
    
    let websiteVerificationData = {
        token: null,
        filename: null,
        dnsName: null,
        domain: null
    };
    
    // ============================================
    // Domain Card Preview Logic
    // ============================================
    
    // Generate a consistent color based on domain name
    function getDomainColor(domain) {
        if (!domain) return ['#667eea', '#764ba2'];
        
        // Generate a hash from the domain name
        let hash = 0;
        for (let i = 0; i < domain.length; i++) {
            hash = domain.charCodeAt(i) + ((hash << 5) - hash);
        }
        
        // Predefined color gradients
        const gradients = [
            ['#667eea', '#764ba2'], // Purple
            ['#f093fb', '#f5576c'], // Pink
            ['#4facfe', '#00f2fe'], // Blue
            ['#43e97b', '#38f9d7'], // Green
            ['#fa709a', '#fee140'], // Pink-Yellow
            ['#30cfd0', '#330867'], // Cyan-Purple
            ['#a8edea', '#fed6e3'], // Light Blue-Pink
            ['#ff9a9e', '#fecfef'], // Red-Pink
            ['#ffecd2', '#fcb69f'], // Orange
            ['#ff6e7f', '#bfe9ff'], // Red-Blue
        ];
        
        // Use hash to select a gradient
        const index = Math.abs(hash) % gradients.length;
        return gradients[index];
    }
    
    // Update domain card preview
    function updateDomainCardPreview() {
        const domainValue = $('#domainNameInput').val();
        if (domainValue) {
            const domainName = domainValue.replace(/^https?:\/\//i, '').replace(/^www\./i, '').split('/')[0];
            const displayName = domainName || 'example.com';
            
            // Update domain name
            $('#domainNamePreview').text(displayName);
            
            // Update title preview (use domain name)
            $('#domainTitlePreview').text(displayName);
            
            // Update price preview
            const saleType = $('input[name="sale_type"]:checked').val();
            let price = '0.00';
            if (saleType === 'fixed_price') {
                price = $('input[name="asking_price"]').val() || '0.00';
            } else if (saleType === 'auction') {
                price = $('input[name="starting_bid"]').val() || '0.00';
            }
            $('#domainPricePreview').text('$' + parseFloat(price).toFixed(2) + ' USD');
            
            // Update background color
            const colors = getDomainColor(domainName);
            $('#domainCardImage').css('background', `linear-gradient(135deg, ${colors[0]} 0%, ${colors[1]} 100%)`);
        }
    }
    
    // Update card when price changes
    
    $('input[name="asking_price"], input[name="starting_bid"]').on('input', function() {
        if ($('input[name="business_type"]:checked').val() === 'domain') {
            updateDomainCardPreview();
        }
    });
    
    $('input[name="sale_type"]').on('change', function() {
        if ($('input[name="business_type"]:checked').val() === 'domain') {
            updateDomainCardPreview();
        }
    });
    
    $('#domainNameInput').on('input', function() {
        let value = $(this).val().trim();
        const warning = $('#domainProtocolWarning');
        const helpText = $('#domainHelpText');
        const requireDomainVerification = {{ \App\Models\MarketplaceSetting::requireDomainVerification() ? 'true' : 'false' }};
        
        if (value && !value.match(/^https?:\/\//i)) {
            warning.slideDown();
            $(this).addClass('is-invalid border-warning');
            helpText.html('<span class="text-danger"><i class="las la-exclamation-circle"></i> @lang("URL must start with http:// or https://")</span>');
        } else {
            warning.slideUp();
            $(this).removeClass('is-invalid border-warning');
            helpText.html('@lang("Enter domain with http:// or https:// (e.g., https://example.com)")');
            
            updateDomainCardPreview();
            
            if (value && requireDomainVerification) {
                try {
                    const urlObj = new URL(value);
                    const domain = urlObj.hostname.replace(/^www\./, '');
                    generateDomainVerification(domain);
                    const $verificationSection = $('.domain-fields').find('#websiteVerificationSection');
                    if ($verificationSection.length) {
                        $verificationSection.slideDown(300, function() {
                            updateDomainVerificationDisplay();
                        });
                    }
                } catch(e) {
                    $('.domain-fields').find('#websiteVerificationSection').slideUp();
                }
            } else {
                $('.domain-fields').find('#websiteVerificationSection').slideUp();
            }
        }
    });
    
    $('#websiteUrlInput').on('input', function() {
        let value = $(this).val().trim();
        const warning = $('#websiteProtocolWarning');
        const helpText = $('#websiteHelpText');
        const requireWebsiteVerification = {{ \App\Models\MarketplaceSetting::requireWebsiteVerification() ? 'true' : 'false' }};
        
        if (value && !value.match(/^https?:\/\//i)) {
            warning.slideDown();
            $(this).addClass('is-invalid border-warning');
            helpText.html('<span class="text-danger"><i class="las la-exclamation-circle"></i> @lang("URL must start with http:// or https://")</span>');
        } else {
            warning.slideUp();
            $(this).removeClass('is-invalid border-warning');
            helpText.html('@lang("Enter full URL starting with http:// or https://")');
            
            if (value && requireWebsiteVerification) {
                try {
                    const urlObj = new URL(value);
                    const domain = urlObj.hostname.replace(/^www\./, '');
                    generateWebsiteVerification(domain);
                    $('.website-fields #websiteVerificationSection').slideDown(300, function() {
                        updateWebsiteVerificationDisplay();
                    });
                } catch(e) {
                    $('.website-fields #websiteVerificationSection').slideUp();
                }
            } else {
                $('.website-fields #websiteVerificationSection').slideUp();
            }
        }
    });
    
    $('#domainNameInput').on('blur', function() {
        let value = $(this).val().trim();
        if (value && !value.match(/^https?:\/\//i)) {
            if (value.match(/^[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]?\.[a-zA-Z]{2,}/)) {
                $(this).val('https://' + value);
                $(this).trigger('input');
            }
        }
    });
    
    $('#websiteUrlInput').on('blur', function() {
        let value = $(this).val().trim();
        if (value && !value.match(/^https?:\/\//i)) {
            // If it looks like a domain or URL, prepend https://
            if (value.match(/^[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]?\.[a-zA-Z]{2,}/)) {
                $(this).val('https://' + value);
                $(this).trigger('input');
            }
        }
    });
    
    function generateDomainVerification(domain) {
        if (!domain) return;
        
        domainVerificationData.domain = domain;
        domainVerificationData.token = siteNamePrefix + '-verify-' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
        domainVerificationData.filename = siteNamePrefix + '-verification-' + Math.random().toString(36).substring(2, 10) + '.txt';
        domainVerificationData.dnsName = '_' + siteNamePrefix + '-verify';
        
        const $domainContainer = $('.domain-fields');
        if (!$domainContainer.find('#websiteVerificationMethod').val()) {
            $domainContainer.find('#websiteVerificationMethod').val('txt_file');
        }
        
        updateDomainVerificationDisplay();
    }
    
    function generateWebsiteVerification(domain) {
        if (!domain) return;
        
        websiteVerificationData.domain = domain;
        websiteVerificationData.token = siteNamePrefix + '-verify-' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
        websiteVerificationData.filename = siteNamePrefix + '-verification-' + Math.random().toString(36).substring(2, 10) + '.txt';
        websiteVerificationData.dnsName = '_' + siteNamePrefix + '-verify';
        
        const container = '.website-fields';
        if (!$(container + ' #websiteVerificationMethod').val()) {
            $(container + ' #websiteVerificationMethod').val('txt_file');
        }
        
        updateWebsiteVerificationDisplay();
    }
    
    function updateDomainVerificationDisplay() {
        const $container = $('.domain-fields');
        let method = $container.find('#websiteVerificationMethod').val();
        
        if (!domainVerificationData.domain || !domainVerificationData.token) {
            $container.find('#websiteTxtFileMethod').hide();
            $container.find('#websiteDnsRecordMethod').hide();
            return;
        }
        
        if (!method) {
            method = 'txt_file';
            $container.find('#websiteVerificationMethod').val('txt_file');
        }
        
        $container.find('#websiteTxtFileMethod').hide();
        $container.find('#websiteDnsRecordMethod').hide();
        
        if (method === 'txt_file') {
            $container.find('#websiteTxtFileName').text(domainVerificationData.filename || '-');
            $container.find('#websiteTxtFileLocation').text('https://' + domainVerificationData.domain + '/' + (domainVerificationData.filename || ''));
            $container.find('#websiteTxtFileContent').text(domainVerificationData.token || '-');
            $container.find('#websiteTxtFileMethod').css('display', 'block');
        } else if (method === 'dns_record') {
            $container.find('#websiteDnsRecordName').text(domainVerificationData.dnsName || '-');
            $container.find('#websiteDnsRecordValue').text(domainVerificationData.token || '-');
            $container.find('#websiteDnsRecordMethod').css('display', 'block');
        }
        
        $container.find('#websiteVerificationToken').val(domainVerificationData.token);
        $container.find('#websiteVerificationFilename').val(domainVerificationData.filename);
        $container.find('#websiteVerificationDnsName').val(domainVerificationData.dnsName);
        
        $container.find('#websiteVerified').val('0');
        $container.find('#websiteVerificationStatus').html('');
    }
    
    function updateWebsiteVerificationDisplay() {
        const businessType = $('input[name="business_type"]:checked').val();
        const container = businessType === 'domain' ? '.domain-fields' : '.website-fields';
        let method = $(container + ' #websiteVerificationMethod').val();
        
        if (!websiteVerificationData.domain || !websiteVerificationData.token) {
            $(container + ' #websiteTxtFileMethod').hide();
            $(container + ' #websiteDnsRecordMethod').hide();
            return;
        }
        
        if (!method) {
            method = 'txt_file';
            $(container + ' #websiteVerificationMethod').val('txt_file');
        }
        
        $(container + ' #websiteTxtFileMethod').hide();
        $(container + ' #websiteDnsRecordMethod').hide();
        
        if (method === 'txt_file') {
            $(container + ' #websiteTxtFileName').text(websiteVerificationData.filename || '-');
            $(container + ' #websiteTxtFileLocation').text('https://' + websiteVerificationData.domain + '/' + (websiteVerificationData.filename || ''));
            $(container + ' #websiteTxtFileContent').text(websiteVerificationData.token || '-');
            $(container + ' #websiteTxtFileMethod').css('display', 'block');
        } else if (method === 'dns_record') {
            $(container + ' #websiteDnsRecordName').text(websiteVerificationData.dnsName || '-');
            $(container + ' #websiteDnsRecordValue').text(websiteVerificationData.token || '-');
            $(container + ' #websiteDnsRecordMethod').css('display', 'block');
        }
        
        $(container + ' #websiteVerificationToken').val(websiteVerificationData.token);
        $(container + ' #websiteVerificationFilename').val(websiteVerificationData.filename);
        $(container + ' #websiteVerificationDnsName').val(websiteVerificationData.dnsName);
        
        $(container + ' #websiteVerified').val('0');
        $(container + ' #websiteVerificationStatus').html('');
    }
    
    $(document).on('change', '#websiteVerificationMethod', function() {
        const businessType = $('input[name="business_type"]:checked').val();
        if (businessType === 'domain') {
            updateDomainVerificationDisplay();
        } else {
            updateWebsiteVerificationDisplay();
        }
    });
    
    $(document).on('click', '#downloadWebsiteTxtFile', function() {
        const businessType = $('input[name="business_type"]:checked').val();
        let token, filename;
        
        if (businessType === 'domain') {
            if (!domainVerificationData.token || !domainVerificationData.filename) {
                alert('Please enter a valid domain URL first to generate the verification file.');
                return;
            }
            token = domainVerificationData.token;
            filename = domainVerificationData.filename;
        } else {
            if (!websiteVerificationData.token) return;
            token = websiteVerificationData.token;
            filename = websiteVerificationData.filename;
        }
        
        const blob = new Blob([token], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    });
    
    $(document).on('click', '#verifyWebsiteBtn', function() {
        const btn = $(this);
        const businessType = $('input[name="business_type"]:checked').val();
        const container = businessType === 'domain' ? '.domain-fields' : '.website-fields';
        const method = $(container + ' #websiteVerificationMethod').val();
        let domain, token, filename, dnsName, errorMsg, successMsg;
        
        if (businessType === 'domain') {
            domain = domainVerificationData.domain;
            token = domainVerificationData.token;
            filename = domainVerificationData.filename;
            dnsName = domainVerificationData.dnsName;
            errorMsg = '@lang("Please enter a domain name first")';
            successMsg = '@lang("Domain ownership verified successfully!")';
        } else {
            domain = websiteVerificationData.domain;
            token = websiteVerificationData.token;
            filename = websiteVerificationData.filename;
            dnsName = websiteVerificationData.dnsName;
            errorMsg = '@lang("Please enter a website URL first")';
            successMsg = '@lang("Website ownership verified successfully!")';
        }
        
        if (!domain || !token) {
            notify('error', errorMsg);
            return;
        }
        
        btn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i>@lang("Verifying...")');
        $(container + ' #websiteVerificationStatus').html('');
        
        $.ajax({
            url: '{{ route("user.verification.verify-ajax") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                domain: domain,
                method: method,
                token: token,
                filename: filename,
                dns_name: dnsName
            },
            success: function(response) {
                if (response.success) {
                    $(container + ' #websiteVerified').val('1');
                    $(container + ' #websiteVerificationStatus').html('<span class="badge badge--success"><i class="las la-check-circle"></i> @lang("Verified")</span>');
                    notify('success', successMsg);
                } else {
                    $(container + ' #websiteVerified').val('0');
                    $(container + ' #websiteVerificationStatus').html('<span class="badge badge--danger"><i class="las la-times-circle"></i> @lang("Not Verified")</span>');
                    notify('error', response.message || '@lang("Verification failed. Please check and try again.")');
                }
            },
            error: function(xhr) {
                $(container + ' #websiteVerified').val('0');
                $(container + ' #websiteVerificationStatus').html('<span class="badge badge--danger"><i class="las la-times-circle"></i> @lang("Error")</span>');
                const message = xhr.responseJSON?.message || '@lang("An error occurred during verification")';
                notify('error', message);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="las la-check-circle me-1"></i>@lang("Verify Ownership")');
            }
        });
    });
    
    // Validate verification before form submission
    $('#listingForm').on('submit', function(e) {
        const businessType = $('input[name="business_type"]:checked').val();
        const requireDomainVerification = {{ \App\Models\MarketplaceSetting::requireDomainVerification() ? 'true' : 'false' }};
        const requireWebsiteVerification = {{ \App\Models\MarketplaceSetting::requireWebsiteVerification() ? 'true' : 'false' }};
        
        let shouldPreventSubmit = false;
        
        // Only check verification if it's required AND the business type needs it
        // IMPORTANT: Only check if verification is actually required (setting is ON)
        if (businessType === 'domain' && requireDomainVerification === true) {
            const verified = $('.domain-fields #websiteVerified').val();
            if (verified && verified !== '1') {
                shouldPreventSubmit = true;
                notify('error', '@lang("You must verify domain ownership before submitting the listing")');
                showStep(2);
            }
        }
        
        if (businessType === 'website' && requireWebsiteVerification === true) {
            const verified = $('.website-fields #websiteVerified').val();
            if (verified && verified !== '1') {
                shouldPreventSubmit = true;
                notify('error', '@lang("You must verify website ownership before submitting the listing")');
                showStep(2);
            }
        }
        // If verification is not required, skip check entirely
        
        // If verification failed, prevent submission
        if (shouldPreventSubmit) {
            e.preventDefault();
            return false;
        }
        
        // If we get here, allow form submission
        const submitBtn = $(this).find('button[type="submit"]');
        if (submitBtn.length && !submitBtn.prop('disabled')) {
            submitBtn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i>@lang("Submitting...")');
        }
        
        // Allow form to submit naturally - don't prevent default
        // Form will submit via normal HTML form submission
    });
    
    // Image upload preview
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    const uploadArea = document.getElementById('uploadArea');
    let selectedFiles = [];
    
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            handleFiles(e.target.files);
        });
    }
    
    // Drag and drop
    if (uploadArea) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.add('drag-over');
            });
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.remove('drag-over');
            });
        });
        
        uploadArea.addEventListener('drop', function(e) {
            handleFiles(e.dataTransfer.files);
        });
    }
    
    function handleFiles(files) {
        const maxFiles = {{ $marketplaceSettings['max_images_per_listing'] ?? 10 }};
        
        Array.from(files).forEach(file => {
            if (selectedFiles.length >= maxFiles) {
                notify('error', `@lang("Maximum") ${maxFiles} @lang("images allowed")`);
                return;
            }
            
            if (!file.type.startsWith('image/')) {
                notify('error', '@lang("Only image files are allowed")');
                return;
            }
            
            if (file.size > 2 * 1024 * 1024) {
                notify('error', '@lang("File size must be less than 2MB")');
                return;
            }
            
            selectedFiles.push(file);
            displayPreview(file, selectedFiles.length - 1);
        });
        
        updateFileInput();
    }
    
    function displayPreview(file, index) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'preview-item';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="remove-btn" data-index="${index}">
                    <i class="las la-times"></i>
                </button>
            `;
            imagePreview.appendChild(div);
        };
        reader.readAsDataURL(file);
    }
    
    $(document).on('click', '.remove-btn', function() {
        const index = $(this).data('index');
        selectedFiles.splice(index, 1);
        rebuildPreview();
        updateFileInput();
    });
    
    function rebuildPreview() {
        imagePreview.innerHTML = '';
        selectedFiles.forEach((file, index) => {
            displayPreview(file, index);
        });
    }
    
    function updateFileInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        imageInput.files = dt.files;
    }
});
</script>
@endpush
