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
                            <span class="step-text">@lang('Type')</span>
                        </div>
                        <div class="step" data-step="2">
                            <span class="step-number">2</span>
                            <span class="step-text">@lang('Asset')</span>
                        </div>
                        <div class="step" data-step="3">
                            <span class="step-number">3</span>
                            <span class="step-text">@lang('Verify')</span>
                        </div>
                        <div class="step" data-step="4">
                            <span class="step-number">4</span>
                            <span class="step-text">@lang('Details')</span>
                        </div>
                        <div class="step" data-step="5">
                            <span class="step-number">5</span>
                            <span class="step-text">@lang('Pricing')</span>
                        </div>
                        <div class="step" data-step="6">
                            <span class="step-number">6</span>
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
                                 STEP 1: Business Type Selection Only
                                 ============================================ --}}
                            <div class="form-step" data-step="1">
                                <div class="step-header mb-4">
                                    <h5 class="fw-bold mb-1">@lang('What are you selling?')</h5>
                                    <p class="text-muted mb-0">@lang('Select the type of online business you want to list')</p>
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
                                
                                <div class="alert alert-info mt-4">
                                    <i class="las la-info-circle me-2"></i>
                                    <strong>@lang('Next Step:')</strong> 
                                    @lang('After selecting a business type, you will be asked to enter the specific asset details.')
                                </div>
                                
                                <div class="step-actions mt-4 d-flex justify-content-between">
                                    <div></div>
                                    <button type="button" class="btn btn--base btn-next" data-next="2" id="step1ContinueBtn">
                                        @lang('Continue') <i class="las la-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>
                            
                            {{-- ============================================
                                 STEP 2: Asset Entry (Domain/Website/Social Media/App)
                                 ============================================ --}}
                            <div class="form-step d-none" data-step="2">
                                <div class="step-header mb-4">
                                    <h5 class="fw-bold mb-1">@lang('Enter Asset Details')</h5>
                                    <p class="text-muted mb-0">@lang('Provide information about the specific asset you are selling')</p>
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
                                                           value="{{ old('domain_name', $draftData['domain_name'] ?? '') }}" placeholder="https://example.com" required>
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
                                                           value="{{ old('website_url', $draftData['website_url'] ?? '') }}" placeholder="https://example.com" required>
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
                                
                                {{-- Social Media Fields --}}
                                <div class="business-fields social_media_account-fields d-none">
                                    <div class="card border-purple">
                                        <div class="card-header bg-purple bg-opacity-10">
                                            <h6 class="mb-0">
                                                <i class="las la-users me-2"></i>@lang('Social Media Account Information')
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">@lang('Platform') <span class="text-danger">*</span></label>
                                                    <select name="platform" class="form-select form-select-lg" required>
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
                                                               value="{{ old('social_username', $draftData['social_username'] ?? '') }}" placeholder="username" required>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">@lang('Account URL')</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="las la-link"></i></span>
                                                        <input type="url" name="social_url" class="form-control" 
                                                               value="{{ old('social_url', $draftData['social_url'] ?? '') }}" placeholder="https://instagram.com/username">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">@lang('Followers')</label>
                                                    <input type="number" name="followers_count" class="form-control" 
                                                           value="{{ old('followers_count', $draftData['followers_count'] ?? '') }}" placeholder="0" min="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">@lang('Engagement Rate') (%)</label>
                                                    <input type="number" name="engagement_rate" class="form-control" 
                                                           value="{{ old('engagement_rate', $draftData['engagement_rate'] ?? '') }}" step="0.01" min="0" max="100" placeholder="0.00">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">@lang('Account Niche')</label>
                                                    <input type="text" name="social_niche" class="form-control" 
                                                           value="{{ old('social_niche', $draftData['social_niche'] ?? '') }}" placeholder="@lang('e.g., Fashion, Gaming')">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Mobile App Fields --}}
                                <div class="business-fields mobile_app-fields d-none">
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning bg-opacity-10">
                                            <h6 class="mb-0">
                                                <i class="las la-mobile-alt me-2"></i>@lang('Mobile App Information')
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">@lang('App Name') <span class="text-danger">*</span></label>
                                                    <input type="text" name="app_name" class="form-control form-control-lg" 
                                                           value="{{ old('app_name', $draftData['app_name'] ?? '') }}" placeholder="@lang('Your App Name')" required>
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
                                                               value="{{ old('app_store_url', $draftData['app_store_url'] ?? '') }}" placeholder="https://apps.apple.com/...">
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">@lang('Play Store URL (Android)')</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="lab la-google-play"></i></span>
                                                        <input type="url" name="play_store_url" class="form-control" 
                                                               value="{{ old('play_store_url', $draftData['play_store_url'] ?? '') }}" placeholder="https://play.google.com/...">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">@lang('Total Downloads')</label>
                                                    <input type="number" name="downloads_count" class="form-control" 
                                                           value="{{ old('downloads_count', $draftData['downloads_count'] ?? '') }}" placeholder="0" min="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">@lang('Rating') (0-5)</label>
                                                    <input type="number" name="app_rating" class="form-control" 
                                                           value="{{ old('app_rating', $draftData['app_rating'] ?? '') }}" step="0.1" min="0" max="5" placeholder="4.5">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">@lang('Active Users')</label>
                                                    <input type="number" name="active_users" class="form-control" 
                                                           value="{{ old('active_users', $draftData['active_users'] ?? '') }}" placeholder="0" min="0">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Desktop App Fields --}}
                                <div class="business-fields desktop_app-fields d-none">
                                    <div class="card border-danger">
                                        <div class="card-header bg-danger bg-opacity-10">
                                            <h6 class="mb-0">
                                                <i class="las la-desktop me-2"></i>@lang('Desktop App Information')
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">@lang('App Name') <span class="text-danger">*</span></label>
                                                    <input type="text" name="app_name" class="form-control form-control-lg" 
                                                           value="{{ old('app_name', $draftData['app_name'] ?? '') }}" placeholder="@lang('Your App Name')" required>
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
                                                               value="{{ old('desktop_url', $draftData['desktop_url'] ?? '') }}" placeholder="https://yourapp.com/download">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">@lang('Total Downloads')</label>
                                                    <input type="number" name="downloads_count" class="form-control" 
                                                           value="{{ old('downloads_count', $draftData['downloads_count'] ?? '') }}" placeholder="0" min="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">@lang('Active Users')</label>
                                                    <input type="number" name="active_users" class="form-control" 
                                                           value="{{ old('active_users', $draftData['active_users'] ?? '') }}" placeholder="0" min="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">@lang('Supported Platforms')</label>
                                                    <input type="text" name="supported_platforms" class="form-control" 
                                                           value="{{ old('supported_platforms', $draftData['supported_platforms'] ?? '') }}" placeholder="@lang('Windows, Mac, Linux')">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="step-actions mt-4 d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary btn-prev" data-prev="1">
                                        <i class="las la-arrow-left me-1"></i> @lang('Back')
                                    </button>
                                    <button type="button" class="btn btn--base btn-next" data-next="3" id="step2ContinueBtn">
                                        @lang('Continue') <i class="las la-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>
                            
                            {{-- ============================================
                                 STEP 3: Ownership Verification (Conditional)
                                 ============================================ --}}
                            <div class="form-step d-none" data-step="3" id="ownershipVerificationStep">
                                <div class="step-header mb-4">
                                    <h5 class="fw-bold mb-1">@lang('Verify Ownership')</h5>
                                    <p class="text-muted mb-0">@lang('Verify that you own this asset before continuing')</p>
                                </div>
                                
                                {{-- Ownership Validation Section --}}
                                <div id="ownershipValidationSection" class="mb-4">
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning bg-opacity-10">
                                            <h6 class="mb-0">
                                                <i class="las la-shield-alt me-2"></i>@lang('Ownership Validation') <span class="text-danger">*</span>
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted mb-3">
                                                @lang('To ensure you own this asset, please verify ownership using one of the methods below.')
                                            </p>
                                            
                                            <div id="validationMethodsContainer" class="mb-3">
                                                <label class="form-label fw-semibold">@lang('Select Verification Method')</label>
                                                <div id="validationMethodsList"></div>
                                            </div>
                                            
                                            <div id="validationInstructions" class="alert alert-info" style="display: none;">
                                                <h6 class="alert-heading">@lang('Instructions')</h6>
                                                <div id="instructionsContent"></div>
                                            </div>
                                            
                                            <div id="validationResult" class="mt-3" style="display: none;"></div>
                                            
                                            <div class="d-flex gap-2 mt-3">
                                                <button type="button" class="btn btn--base" id="generateTokenBtn" style="display: none;">
                                                    <i class="las la-key me-1"></i>@lang('Generate Verification Token')
                                                </button>
                                                <button type="button" class="btn btn-success" id="validateOwnershipBtn" style="display: none;">
                                                    <i class="las la-check-circle me-1"></i>@lang('Validate Ownership')
                                                </button>
                                            </div>
                                            
                                            <div id="validationStatus" class="mt-3" style="display: none;">
                                                <div class="alert alert-success">
                                                    <i class="las la-check-circle me-2"></i>
                                                    <strong>@lang('Ownership Verified!')</strong>
                                                    <p class="mb-0 mt-1">@lang('You can now continue with your listing.')</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Skip message for types that don't require verification --}}
                                <div id="verificationNotRequiredMessage" class="alert alert-info" style="display: none;">
                                    <i class="las la-info-circle me-2"></i>
                                    <strong>@lang('Ownership verification not required')</strong>
                                    <p class="mb-0 mt-2">@lang('Ownership verification is not required for this business type. You can proceed to the next step.')</p>
                                </div>
                                
                                <div class="step-actions mt-4 d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary btn-prev" data-prev="2">
                                        <i class="las la-arrow-left me-1"></i> @lang('Back')
                                    </button>
                                    <button type="button" class="btn btn--base btn-next" data-next="4" id="step3ContinueBtn">
                                        @lang('Continue') <i class="las la-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>
                            
                            {{-- ============================================
                                 STEP 4: Business Details (Renumbered from old Step 2)
                                 ============================================ --}}
                            <div class="form-step d-none" data-step="4">
                                <div class="step-header mb-4">
                                    <h5 class="fw-bold mb-1">@lang('Business Details')</h5>
                                    <p class="text-muted mb-0">@lang('Provide information about your business')</p>
                                </div>
                                
                                {{-- Common Fields for All Types --}}
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
                                        <textarea name="description" class="form-control" rows="6" required
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
                                                       value="{{ old('monthly_revenue', $draftData['monthly_revenue'] ?? '') }}" step="0.01" min="0" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">@lang('Monthly Profit')</label>
                                            <div class="input-group">
                                                <span class="input-group-text">{{ gs()->cur_sym }}</span>
                                                <input type="number" name="monthly_profit" class="form-control" 
                                                       value="{{ old('monthly_profit', $draftData['monthly_profit'] ?? '') }}" step="0.01" min="0" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">@lang('Monthly Visitors')</label>
                                            <input type="number" name="monthly_visitors" class="form-control" 
                                                   value="{{ old('monthly_visitors', $draftData['monthly_visitors'] ?? '') }}" min="0" placeholder="0">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">@lang('Page Views/Month')</label>
                                            <input type="number" name="monthly_page_views" class="form-control" 
                                                   value="{{ old('monthly_page_views', $draftData['monthly_page_views'] ?? '') }}" min="0" placeholder="0">
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
                                    <button type="button" class="btn btn-outline-secondary btn-prev" data-prev="3">
                                        <i class="las la-arrow-left me-1"></i> @lang('Back')
                                    </button>
                                    <button type="button" class="btn btn--base btn-next" data-next="5">
                                        @lang('Continue') <i class="las la-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>
                            
                            {{-- ============================================
                                 STEP 5: Sale Type & Pricing (Renumbered from old Step 3)
                                 ============================================ --}}
                            <div class="form-step d-none" data-step="5">
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
                                    <button type="button" class="btn btn-outline-secondary btn-prev" data-prev="4">
                                        <i class="las la-arrow-left me-1"></i> @lang('Back')
                                    </button>
                                    <button type="button" class="btn btn--base btn-next" data-next="6">
                                        @lang('Continue') <i class="las la-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>
                            
                            {{-- ============================================
                                 STEP 6: Media & Review (Renumbered from old Step 4)
                                 ============================================ --}}
                            <div class="form-step d-none" data-step="6">
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
                                        <li>@lang('NB : First image will be used as the thumbnail')</li>
                                    </ul>
                                    </div>
                                </div>
                                
                                <div class="step-actions mt-4 d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary btn-prev" data-prev="5">
                                        <i class="las la-arrow-left me-1"></i> @lang('Back')
                                    </button>
                                    <button type="submit" class="btn btn--base" id="submitListingBtn">
                                        <i class="las la-check-circle me-1"></i> @lang('Submit Listing')
                                    </button>
                                </div>
                            </div>
                            
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

    // Ownership Validation Handler
    let ownershipValidation = {
        businessType: null,
        primaryAssetUrl: null,
        verificationToken: null,
        selectedMethod: null,
        instructions: null,
        isVerified: {{ $ownershipValidationData['is_verified'] ? 'true' : 'false' }},
        isLoading: false,
        loadTimeout: null,
        methodsCache: null, // Cache methods to avoid duplicate requests
        lastMethodsRequest: null, // Track last request to prevent duplicates
        isGeneratingToken: false, // Prevent duplicate token generation
        isValidating: false, // Prevent duplicate validation
        urlChangeTimeout: null,
        socialChangeTimeout: null,
        
        init: function() {
            const self = this;
            
            // Restore state from session on page load
            @if($ownershipValidationData['verification_token'])
                this.verificationToken = '{{ $ownershipValidationData['verification_token'] }}';
            @endif
            
            @if($ownershipValidationData['verification_method'])
                this.selectedMethod = '{{ $ownershipValidationData['verification_method'] }}';
            @endif
            
            @if($ownershipValidationData['verification_asset'])
                this.primaryAssetUrl = '{{ $ownershipValidationData['verification_asset'] }}';
            @endif
            
            @if($ownershipValidationData['verification_business_type'])
                this.businessType = '{{ $ownershipValidationData['verification_business_type'] }}';
            @endif
            
            // Restore state on page load - use requestAnimationFrame for instant response
            requestAnimationFrame(function() {
                // First, get current business type from form (draft may have restored it)
                const selectedBusinessType = $('input[name="business_type"]:checked').val();
                if (selectedBusinessType) {
                    self.businessType = selectedBusinessType;
                }
                
                // Get current asset URL from form (draft may have restored it)
                const currentAssetUrl = self.getCurrentAssetUrl();
                if (currentAssetUrl) {
                    self.primaryAssetUrl = currentAssetUrl;
                }
                
                // Normalize URLs for comparison
                const normalizeUrl = function(url) {
                    if (!url) return '';
                    return url.trim().toLowerCase().replace(/\/+$/, '');
                };
                
                // Check if asset URL matches session (if verified)
                // IMPORTANT: Only compare if business type matches (prevent false positives when switching types)
                if (self.isVerified || self.verificationToken) {
                    const sessionAsset = '{{ $ownershipValidationData['verification_asset'] ?? '' }}';
                    const sessionBusinessType = '{{ $ownershipValidationData['verification_business_type'] ?? '' }}';
                    
                    // Priority check: If business type changed, clear everything silently
                    if (sessionBusinessType && self.businessType && sessionBusinessType !== self.businessType) {
                        // Business type changed - clear verification silently (no warnings)
                        self.isVerified = false;
                        self.verificationToken = null;
                        self.selectedMethod = null;
                        self.instructions = null;
                        self.primaryAssetUrl = null; // Clear to prevent false comparisons
                        // Clear session (fire and forget, no user notification)
                        $.ajax({
                            url: '{{ route("user.ownership.validation.clear") }}',
                            method: 'POST',
                            data: { _token: '{{ csrf_token() }}' },
                            async: true
                        });
                    } else if (sessionBusinessType === self.businessType && sessionAsset) {
                        // Same business type - check if URL changed
                        const normalizedSession = normalizeUrl(sessionAsset);
                        const normalizedCurrent = normalizeUrl(self.primaryAssetUrl || '');
                        
                        // Only clear if URL changed for the SAME business type
                        if (normalizedCurrent !== '' && normalizedSession !== normalizedCurrent) {
                            // Asset URL changed for same business type - clear verification
                            self.isVerified = false;
                            self.verificationToken = null;
                            self.selectedMethod = null;
                            self.instructions = null;
                            // Clear session (fire and forget)
                            $.ajax({
                                url: '{{ route("user.ownership.validation.clear") }}',
                                method: 'POST',
                                data: { _token: '{{ csrf_token() }}' },
                                async: true
                            });
                        }
                    }
                }
                
                // Restore validation state immediately
                if (self.isVerified && self.primaryAssetUrl) {
                    // Verified and have asset URL - restore state instantly
                    self.restoreValidationState();
                } else if (self.verificationToken && self.businessType && self.primaryAssetUrl) {
                    // Have token but not verified yet - restore UI
                    self.checkIfValidationRequired();
                    self.restoreValidationState();
                } else if (self.businessType) {
                    // Just have business type - check if validation needed
                    self.checkIfValidationRequired();
                }
            });
            
            // Watch for business type changes
            $('input[name="business_type"]').on('change', function() {
                const newBusinessType = $(this).val();
                const oldBusinessType = self.businessType;
                
                // If business type actually changed, completely reset validation state
                if (oldBusinessType && oldBusinessType !== newBusinessType) {
                    // Clear methods cache when business type changes
                    self.methodsCache = null;
                    self.lastMethodsRequest = null;
                    
                    // Completely reset validation state (don't trigger URL change warnings)
                    self.isVerified = false;
                    self.verificationToken = null;
                    self.selectedMethod = null;
                    self.instructions = null;
                    self.primaryAssetUrl = null; // Clear old URL to prevent false comparisons
                    
                    // Clear UI state silently (no warnings)
                    $('#validationStatus').hide();
                    $('#validationMethodsList').empty();
                    $('#validationInstructions').hide();
                    $('#validationResult').hide();
                    $('#generateTokenBtn').hide();
                    $('#validateOwnershipBtn').hide();
                    
                    // Clear session via AJAX (async, don't block, no user notification)
                    $.ajax({
                        url: '{{ route("user.ownership.validation.clear") }}',
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        async: true
                    });
                }
                
                // Update business type
                self.businessType = newBusinessType;
                
                // Use requestAnimationFrame for instant response
                requestAnimationFrame(function() {
                    self.checkIfValidationRequired();
                });
            });
            
            // Watch for domain/website URL changes (optimized debounce - shorter delay)
            $('#domainNameInput, #websiteUrlInput').on('input blur', function() {
                clearTimeout(self.urlChangeTimeout);
                const inputElement = this;
                self.urlChangeTimeout = setTimeout(function() {
                    // Get current business type to ensure we're comparing same type
                    const currentBusinessType = $('input[name="business_type"]:checked').val();
                    
                    // Only check URL changes if business type matches
                    if (currentBusinessType !== self.businessType) {
                        // Business type changed, don't check URL changes
                        self.businessType = currentBusinessType;
                        self.primaryAssetUrl = null; // Clear to prevent false comparisons
                        self.checkIfValidationRequired();
                        return;
                    }
                    
                    const newUrl = $(inputElement).val() || '';
                    const oldUrl = self.primaryAssetUrl || '';
                    
                    // Normalize URLs for comparison (remove trailing slashes, lowercase)
                    const normalizeUrl = function(url) {
                        if (!url) return '';
                        return url.trim().toLowerCase().replace(/\/+$/, '');
                    };
                    
                    const normalizedNew = normalizeUrl(newUrl);
                    const normalizedOld = normalizeUrl(oldUrl);
                    
                    // Only check for URL changes if:
                    // 1. We have a token/verification
                    // 2. Old URL exists (not empty)
                    // 3. New URL is different from old URL
                    // 4. New URL is not empty
                    // 5. Business type hasn't changed
                    if ((self.verificationToken || self.isVerified) && 
                        normalizedOld !== '' && 
                        normalizedNew !== normalizedOld && 
                        normalizedNew !== '' &&
                        currentBusinessType === self.businessType) {
                        // URL actually changed for the same business type
                        self.clearValidationState('Asset URL changed. Please generate a new verification token.');
                    }
                    
                    // Update primary asset URL and check validation
                    self.primaryAssetUrl = newUrl;
                    self.checkIfValidationRequired();
                }, 200); // Reduced from 500ms to 200ms for faster response
            });
            
            // Watch for social media fields (optimized debounce)
            $('input[name="social_url"], input[name="social_username"], select[name="platform"]').on('change blur', function() {
                clearTimeout(self.socialChangeTimeout);
                self.socialChangeTimeout = setTimeout(function() {
                    // Get current asset URL
                    const platform = $('select[name="platform"]').val();
                    const username = $('input[name="social_username"]').val();
                    const url = $('input[name="social_url"]').val();
                    const newUrl = url || (platform && username ? platform + '/' + username : '');
                    
                    // Check if URL changed
                    const normalizeUrl = function(url) {
                        if (!url) return '';
                        return url.trim().toLowerCase().replace(/\/+$/, '');
                    };
                    
                    const normalizedNew = normalizeUrl(newUrl);
                    const normalizedOld = normalizeUrl(self.primaryAssetUrl || '');
                    
                    if ((self.verificationToken || self.isVerified) && normalizedOld !== '' && normalizedNew !== normalizedOld && normalizedNew !== '') {
                        self.clearValidationState('Asset URL changed. Please generate a new verification token.');
                    }
                    
                    self.primaryAssetUrl = newUrl;
                    self.checkIfValidationRequired();
                }, 200); // Reduced from 500ms to 200ms for faster response
            });
            
            // Also watch for when input sections become visible using MutationObserver
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                        const target = $(mutation.target);
                        if (target.is('#domainInputSection, #websiteInputSection') && target.is(':visible')) {
                            // Use requestAnimationFrame for instant response
                            requestAnimationFrame(function() {
                                self.checkIfValidationRequired();
                            });
                        }
                    }
                });
            });
            
            // Observe domain and website input sections for visibility changes
            const domainSection = document.getElementById('domainInputSection');
            const websiteSection = document.getElementById('websiteInputSection');
            if (domainSection) {
                observer.observe(domainSection, { attributes: true, attributeFilter: ['style'] });
            }
            if (websiteSection) {
                observer.observe(websiteSection, { attributes: true, attributeFilter: ['style'] });
            }
            
            // Generate token button
            $('#generateTokenBtn').on('click', function() {
                self.generateToken();
            });
            
            // Validate ownership button
            $('#validateOwnershipBtn').on('click', function() {
                self.validateOwnership();
            });
            
            // Method selection
            $(document).on('change', 'input[name="validation_method"]', function() {
                self.selectedMethod = $(this).val();
                
                // Check if asset URL changed since token was generated (only for same business type)
                const currentBusinessType = $('input[name="business_type"]:checked').val();
                const sessionBusinessType = '{{ $ownershipValidationData['verification_business_type'] ?? '' }}';
                const sessionAsset = '{{ $ownershipValidationData['verification_asset'] ?? '' }}';
                
                // Only check URL change if business type matches (same type)
                if (self.verificationToken && sessionAsset && currentBusinessType === sessionBusinessType && sessionBusinessType) {
                    const currentAssetUrl = self.getCurrentAssetUrl();
                    const normalizeUrl = function(url) {
                        if (!url) return '';
                        return url.trim().toLowerCase().replace(/\/+$/, '');
                    };
                    
                    const normalizedSession = normalizeUrl(sessionAsset);
                    const normalizedCurrent = normalizeUrl(currentAssetUrl);
                    
                    // Only warn if URL changed for the SAME business type
                    if (normalizedSession !== '' && normalizedCurrent !== '' && normalizedSession !== normalizedCurrent) {
                        // Asset URL changed for same business type - need new token
                        notify('warning', 'Asset URL has changed. Please generate a new verification token.');
                        self.verificationToken = null;
                        self.selectedMethod = null;
                        $(this).prop('checked', false);
                        $('#generateTokenBtn').show();
                        $('#validateOwnershipBtn').hide();
                        $('#validationInstructions').hide();
                        return;
                    }
                } else if (currentBusinessType !== sessionBusinessType && sessionBusinessType) {
                    // Business type changed - silently reset (no warning)
                    self.verificationToken = null;
                    self.selectedMethod = null;
                    $(this).prop('checked', false);
                    $('#generateTokenBtn').show();
                    $('#validateOwnershipBtn').hide();
                    $('#validationInstructions').hide();
                    return;
                }
                
                if (self.selectedMethod === 'oauth_login') {
                    // For OAuth, show buttons immediately (no token needed)
                    $('#generateTokenBtn').hide();
                    $('#validateOwnershipBtn').hide();
                    // Generate token for OAuth if not exists (needed for session)
                    if (!self.verificationToken && self.primaryAssetUrl) {
                        self.generateToken();
                    } else {
                        self.showInstructions();
                    }
                } else if (self.verificationToken) {
                    // Token exists, show instructions and validate button
                    self.showInstructions();
                    $('#validateOwnershipBtn').show();
                    $('#generateTokenBtn').hide();
                } else {
                    // No token yet - show generate token button
                    $('#generateTokenBtn').show();
                    $('#validateOwnershipBtn').hide();
                    $('#validationInstructions').hide();
                    if (self.primaryAssetUrl && self.primaryAssetUrl.trim()) {
                        notify('info', 'Please generate a verification token first');
                    }
                }
            });
            
            // Watch for platform changes to update OAuth buttons
            $('select[name="platform"]').on('change', function() {
                if (self.businessType === 'social_media_account' && self.selectedMethod === 'oauth_login') {
                    self.loadValidationMethods();
                }
            });
            
            // Check if already verified
            if (this.isVerified) {
                this.showVerifiedStatus();
            }
        },
        
        restoreValidationState: function() {
            const self = this;
            
            // If already verified, just show the status
            if (this.isVerified) {
                // Ensure validation section is visible
                $('#ownershipValidationSection').show();
                this.showVerifiedStatus();
                return;
            }
            
            // If we have token and business type, restore the validation UI
            if (this.verificationToken && this.businessType) {
                // Show validation section
                $('#ownershipValidationSection').show();
                
                // Load validation methods
                this.loadValidationMethods(function() {
                    // After methods are loaded, restore selected method if exists
                    if (self.selectedMethod) {
                        const methodInput = $('input[name="validation_method"][value="' + self.selectedMethod + '"]');
                        if (methodInput.length > 0) {
                            methodInput.prop('checked', true).trigger('change');
                            
                            // If we have instructions stored, show them
                            if (self.instructions) {
                                self.showInstructions(self.instructions);
                            }
                            
                            // Show validate button if token exists and not OAuth
                            if (self.selectedMethod !== 'oauth_login') {
                                $('#validateOwnershipBtn').show();
                                $('#generateTokenBtn').hide();
                            }
                        }
                    } else if (self.verificationToken && self.businessType !== 'social_media_account') {
                        // Token exists but no method selected - show generate button as fallback
                        $('#generateTokenBtn').show();
                    }
                });
            } else if (this.businessType && this.primaryAssetUrl) {
                // Have business type and asset URL but no token - show validation section
                $('#ownershipValidationSection').show();
                this.loadValidationMethods();
            }
        },
        
        checkIfValidationRequired: function() {
            // Clear any pending timeout
            if (this.loadTimeout) {
                clearTimeout(this.loadTimeout);
            }
            
            // Prevent multiple simultaneous calls
            if (this.isLoading) {
                return;
            }
            
            const requiresValidation = ['domain', 'website', 'social_media_account'];
            
            // Get primary asset URL based on business type
            let assetUrl = '';
            let shouldShow = false;
            
            if (this.businessType === 'domain') {
                assetUrl = $('#domainNameInput').val() || '';
                shouldShow = !!assetUrl;
            } else if (this.businessType === 'website') {
                assetUrl = $('#websiteUrlInput').val() || '';
                shouldShow = !!assetUrl;
            } else if (this.businessType === 'social_media_account') {
                const platform = $('select[name="platform"]').val();
                const username = $('input[name="social_username"]').val();
                const url = $('input[name="social_url"]').val();
                assetUrl = url || (platform && username ? platform + '/' + username : '');
                shouldShow = !!assetUrl;
            }
            
            // Only check for URL changes if we have validation state AND business type matches session
            const sessionBusinessType = '{{ $ownershipValidationData['verification_business_type'] ?? '' }}';
            const sessionAsset = '{{ $ownershipValidationData['verification_asset'] ?? '' }}';
            
            // Only compare URLs if:
            // 1. We have a token/verification
            // 2. Current business type matches the session business type (same type)
            // 3. We have both old and new URLs
            if ((this.verificationToken || this.isVerified) && 
                this.businessType === sessionBusinessType && 
                sessionBusinessType && 
                sessionAsset) {
                
                // Normalize URLs for comparison
                const normalizeUrl = function(url) {
                    if (!url) return '';
                    return url.trim().toLowerCase().replace(/\/+$/, '');
                };
                
                const normalizedNew = normalizeUrl(assetUrl);
                const normalizedOld = normalizeUrl(sessionAsset);
                
                // Only clear if URL actually changed for the SAME business type
                if (normalizedOld !== '' && normalizedNew !== '' && normalizedNew !== normalizedOld) {
                    // Asset URL changed for the same business type - clear validation state
                    this.clearValidationState('Asset URL changed. Please verify ownership for the new asset.');
                }
            }
            
            // Update primary asset URL
            this.primaryAssetUrl = assetUrl;
            
            // Show/hide validation section (only in Step 3)
            // Check if we're currently on Step 3
            const currentStep = ListingFormHandler.currentStep || 1;
            if (currentStep === 3) {
                if (requiresValidation.includes(this.businessType)) {
                    // Show validation section
                    $('#ownershipValidationSection').show();
                    $('#verificationNotRequiredMessage').hide();
                    
                    // Load methods if we have asset URL or if already verified
                    if ((assetUrl && assetUrl.trim()) || this.isVerified) {
                        // Debounce the load to prevent multiple calls (reduced delay)
                        clearTimeout(this.loadTimeout);
                        this.loadTimeout = setTimeout(() => {
                            this.loadValidationMethods();
                        }, 150); // Reduced from 300ms to 150ms
                    }
                } else {
                    // Verification not required - show message
                    $('#ownershipValidationSection').hide();
                    $('#verificationNotRequiredMessage').show();
                }
            }
        },
        
        clearValidationState: function(message) {
            const self = this;
            
            // Clear local state
            this.isVerified = false;
            this.verificationToken = null;
            this.selectedMethod = null;
            this.instructions = null;
            
            // Clear session
            $.ajax({
                url: '{{ route("user.ownership.validation.clear") }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function() {
                    // Hide verified status
                    $('#validationStatus').hide();
                    $('#validationMethodsList').empty();
                    $('#validationInstructions').hide();
                    $('#validationResult').hide();
                    
                    // Show generate token button if we have asset URL
                    if (self.primaryAssetUrl && self.primaryAssetUrl.trim()) {
                        $('#generateTokenBtn').show();
                    }
                    
                    // Show message if provided
                    if (message) {
                        notify('info', message);
                    }
                },
                error: function() {
                    // Even if AJAX fails, clear local state
                    $('#validationStatus').hide();
                    if (message) {
                        notify('info', message);
                    }
                }
            });
        },
        
        loadValidationMethods: function(callback) {
            const self = this;
            
            // Use cache if available and business type matches
            if (this.methodsCache && this.methodsCache.businessType === this.businessType) {
                this.renderMethods(this.methodsCache.methods);
                if (this.businessType !== 'social_media_account' && !this.verificationToken) {
                    $('#generateTokenBtn').show();
                }
                if (callback) callback();
                return;
            }
            
            // Prevent duplicate simultaneous calls for same business type
            const requestKey = this.businessType + '_' + Date.now();
            if (this.lastMethodsRequest && this.lastMethodsRequest.businessType === this.businessType) {
                // If request is less than 1 second old, wait for it
                if (Date.now() - this.lastMethodsRequest.timestamp < 1000) {
                    if (callback) {
                        // Store callback to execute when request completes
                        if (!this.lastMethodsRequest.callbacks) {
                            this.lastMethodsRequest.callbacks = [];
                        }
                        this.lastMethodsRequest.callbacks.push(callback);
                    }
                    return;
                }
            }
            
            if (!this.businessType) {
                if (callback) callback();
                return;
            }
            
            this.isLoading = true;
            this.lastMethodsRequest = {
                businessType: this.businessType,
                timestamp: Date.now(),
                callbacks: callback ? [callback] : []
            };
            
            // Show loading state only if container is empty
            const container = $('#validationMethodsList');
            if (container.children().length === 0) {
                container.html('<div class="text-center py-2"><i class="las la-spinner la-spin"></i> <small>Loading methods...</small></div>');
            }
            
            $.ajax({
                url: '{{ route("user.ownership.validation.methods") }}',
                method: 'GET',
                data: { business_type: this.businessType },
                timeout: 8000, // Reduced timeout for faster failure detection
                cache: false, // Ensure fresh data
                success: function(response) {
                    self.isLoading = false;
                    if (response.success && response.methods) {
                        // Cache the methods
                        self.methodsCache = {
                            businessType: self.businessType,
                            methods: response.methods
                        };
                        
                        // Restore token and instructions from response if available
                        if (response.token && !self.verificationToken) {
                            self.verificationToken = response.token;
                        }
                        if (response.instructions && !self.instructions) {
                            self.instructions = response.instructions;
                        }
                        
                        self.renderMethods(response.methods);
                        // Show generate token button when methods are loaded (for non-OAuth methods)
                        if (self.businessType !== 'social_media_account' && !self.verificationToken) {
                            $('#generateTokenBtn').show();
                        }
                        
                        // Execute all callbacks
                        if (self.lastMethodsRequest && self.lastMethodsRequest.callbacks) {
                            self.lastMethodsRequest.callbacks.forEach(function(cb) {
                                if (typeof cb === 'function') cb();
                            });
                        }
                        if (callback && self.lastMethodsRequest.callbacks.indexOf(callback) === -1) {
                            callback();
                        }
                    } else {
                        notify('error', 'Failed to load validation methods');
                        container.html('<div class="alert alert-danger">Failed to load validation methods. <button class="btn btn-sm btn-outline-primary ms-2" onclick="ownershipValidation.loadValidationMethods()">Retry</button></div>');
                        if (callback) callback();
                    }
                    self.lastMethodsRequest = null;
                },
                error: function(xhr, status, error) {
                    self.isLoading = false;
                    let message = 'Failed to load validation methods';
                    if (status === 'timeout') {
                        message = 'Request timed out. Please check your connection.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    notify('error', message);
                    container.html('<div class="alert alert-danger">' + message + ' <button class="btn btn-sm btn-outline-primary ms-2" onclick="ownershipValidation.loadValidationMethods()">Retry</button></div>');
                    
                    // Execute callbacks even on error
                    if (self.lastMethodsRequest && self.lastMethodsRequest.callbacks) {
                        self.lastMethodsRequest.callbacks.forEach(function(cb) {
                            if (typeof cb === 'function') cb();
                        });
                    }
                    if (callback && (!self.lastMethodsRequest || self.lastMethodsRequest.callbacks.indexOf(callback) === -1)) {
                        callback();
                    }
                    self.lastMethodsRequest = null;
                }
            });
        },
        
        renderMethods: function(methods) {
            const self = this;
            const container = $('#validationMethodsList');
            container.empty();
            
            if (!methods || Object.keys(methods).length === 0) {
                container.html('<div class="alert alert-warning">No validation methods available for this business type.</div>');
                return;
            }
            
            $.each(methods, function(key, method) {
                if (key === 'oauth_login') {
                    // For OAuth, show login buttons instead of radio
                    const platform = $('select[name="platform"]').val();
                    const oauthButtonsHtml = self.renderOAuthButtons(platform);
                    container.append(oauthButtonsHtml);
                } else {
                    const isChecked = (self.selectedMethod === key) ? 'checked' : '';
                    const methodHtml = `
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="validation_method" 
                                   id="method_${key}" value="${key}" ${isChecked}>
                            <label class="form-check-label" for="method_${key}">
                                <strong>${method.name}</strong>
                                <small class="d-block text-muted">${method.description}</small>
                            </label>
                        </div>
                    `;
                    container.append(methodHtml);
                }
            });
            
            // If token already exists and a method is selected, show instructions and validate button
            if (self.verificationToken && self.selectedMethod && self.selectedMethod !== 'oauth_login') {
                // Trigger change to show instructions
                $('input[name="validation_method"][value="' + self.selectedMethod + '"]').trigger('change');
            }
        },
        
        renderOAuthButtons: function(platform) {
            const self = this;
            
            if (!platform) {
                return '<div class="alert alert-warning">Please select a platform first</div>';
            }
            
            let buttonsHtml = '<div class="oauth-buttons-container mb-3">';
            buttonsHtml += '<p class="mb-2"><strong>Login with your ' + (platform || 'Social Media') + ' account to verify ownership:</strong></p>';
            buttonsHtml += '<div class="d-flex gap-2 flex-wrap">';
            
            // Use the original platform name in the URL (backend will map it to provider)
            const oauthUrl = '{{ route("user.ownership.validation.oauth.redirect", ":platform") }}'.replace(':platform', platform.toLowerCase());
            buttonsHtml += `
                <a href="${oauthUrl}?business_type=${self.businessType}&handle=${encodeURIComponent($('input[name="social_username"]').val() || '')}&token=${self.verificationToken || ''}&asset_url=${encodeURIComponent(self.primaryAssetUrl || '')}" 
                   class="btn btn-primary">
                    <i class="las la-sign-in-alt me-1"></i>Login with ${platform}
                </a>
            `;
            
            buttonsHtml += '</div></div>';
            return buttonsHtml;
        },
        
        generateToken: function() {
            const self = this;
            
            // Prevent duplicate requests
            if (this.isGeneratingToken) {
                return;
            }
            
            // Ensure business type is set from form
            if (!this.businessType) {
                this.businessType = $('input[name="business_type"]:checked').val();
            }
            
            if (!this.businessType) {
                notify('error', 'Please select a business type first');
                return;
            }
            
            // Get current asset URL from form (in case it changed)
            const currentAssetUrl = this.getCurrentAssetUrl();
            
            // Update primary asset URL
            this.primaryAssetUrl = currentAssetUrl;
            
            if (!this.primaryAssetUrl || !this.primaryAssetUrl.trim()) {
                // Provide more specific error message
                let fieldName = 'primary asset URL';
                if (this.businessType === 'domain') {
                    fieldName = 'domain name';
                } else if (this.businessType === 'website') {
                    fieldName = 'website URL';
                } else if (this.businessType === 'social_media_account') {
                    fieldName = 'social media account details';
                }
                notify('error', `Please enter the ${fieldName} first`);
                return;
            }
            
            // If we already have a token for a different URL, clear it first
            const normalizeUrl = function(url) {
                if (!url) return '';
                return url.trim().toLowerCase().replace(/\/+$/, '');
            };
            
            const sessionAsset = '{{ $ownershipValidationData['verification_asset'] ?? '' }}';
            if (this.verificationToken && sessionAsset) {
                const normalizedSession = normalizeUrl(sessionAsset);
                const normalizedCurrent = normalizeUrl(this.primaryAssetUrl);
                if (normalizedSession !== normalizedCurrent) {
                    // Different URL - clear old token
                    this.verificationToken = null;
                    this.selectedMethod = null;
                    this.instructions = null;
                }
            }
            
            // Optimistic UI update - show loading immediately
            const btn = $('#generateTokenBtn');
            const originalHtml = btn.html();
            btn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i>Generating...');
            this.isGeneratingToken = true;
            
            $.ajax({
                url: '{{ route("user.ownership.validation.generate.token") }}',
                method: 'POST',
                data: {
                    business_type: this.businessType,
                    primary_asset_url: this.primaryAssetUrl,
                    _token: '{{ csrf_token() }}'
                },
                timeout: 10000, // Reduced timeout for faster failure detection
                success: function(response) {
                    self.isGeneratingToken = false;
                    btn.prop('disabled', false).html(originalHtml);
                    if (response.success) {
                        self.verificationToken = response.token;
                        if (response.instructions) {
                            self.instructions = response.instructions;
                        }
                        
                        // Clear any previous method selection since we have a new token
                        $('input[name="validation_method"]').prop('checked', false);
                        self.selectedMethod = null;
                        
                        // For social media OAuth, don't show validate button
                        if (self.businessType === 'social_media_account') {
                            // OAuth buttons are rendered in renderMethods
                            self.loadValidationMethods();
                        } else {
                            // Hide generate button, show validate button
                            $('#generateTokenBtn').hide();
                            $('#validateOwnershipBtn').hide(); // Hide until method is selected
                            $('#validationInstructions').hide();
                            
                            // Reload methods to get fresh list
                            self.loadValidationMethods();
                        }
                        notify('success', 'Verification token generated. Please select a verification method.');
                    } else {
                        notify('error', response.message || 'Failed to generate token');
                    }
                },
                error: function(xhr, status, error) {
                    self.isGeneratingToken = false;
                    btn.prop('disabled', false).html(originalHtml);
                    let message = 'Failed to generate token';
                    if (status === 'timeout') {
                        message = 'Request timed out. Please check your connection.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    notify('error', message);
                }
            });
        },
        
        showInstructions: function(instructions) {
            if (!this.selectedMethod) {
                $('#validationInstructions').hide();
                return;
            }
            
            // Use passed instructions or stored instructions
            const inst = instructions || this.instructions;
            
            if (inst && inst[this.selectedMethod]) {
                const methodInstructions = inst[this.selectedMethod];
                let stepsHtml = '<ol class="mb-0">';
                methodInstructions.steps.forEach(function(step) {
                    stepsHtml += '<li>' + step + '</li>';
                });
                stepsHtml += '</ol>';
                
                $('#instructionsContent').html('<h6>' + methodInstructions.title + '</h6>' + stepsHtml);
                $('#validationInstructions').show();
            } else {
                $('#validationInstructions').hide();
            }
        },
        
        validateOwnership: function() {
            const self = this;
            
            // Prevent duplicate validation requests
            if (this.isValidating) {
                return;
            }
            
            // Get current business type and asset URL from form
            const currentBusinessType = $('input[name="business_type"]:checked').val();
            const currentAssetUrl = this.getCurrentAssetUrl();
            const sessionBusinessType = '{{ $ownershipValidationData['verification_business_type'] ?? '' }}';
            const sessionAsset = '{{ $ownershipValidationData['verification_asset'] ?? '' }}';
            
            // Only check URL change if business type matches (same type)
            if (this.verificationToken && sessionAsset && currentBusinessType === sessionBusinessType && sessionBusinessType) {
                const normalizeUrl = function(url) {
                    if (!url) return '';
                    return url.trim().toLowerCase().replace(/\/+$/, '');
                };
                
                const normalizedSession = normalizeUrl(sessionAsset);
                const normalizedCurrent = normalizeUrl(currentAssetUrl);
                
                // Only error if URL changed for the SAME business type
                if (normalizedSession !== '' && normalizedCurrent !== '' && normalizedSession !== normalizedCurrent) {
                    notify('error', 'Asset URL has changed. Please generate a new verification token for the current asset.');
                    return;
                }
            } else if (currentBusinessType !== sessionBusinessType && sessionBusinessType) {
                // Business type changed - need to verify for new type
                notify('error', 'Business type has changed. Please generate a new verification token for the selected business type.');
                return;
            }
            
            // Update primary asset URL
            this.primaryAssetUrl = currentAssetUrl;
            
            if (!this.primaryAssetUrl || !this.primaryAssetUrl.trim()) {
                notify('error', 'Please enter the primary asset URL first');
                return;
            }
            
            if (!this.verificationToken) {
                notify('error', 'Please generate a verification token first');
                return;
            }
            
            if (!this.selectedMethod) {
                notify('error', 'Please select a validation method');
                return;
            }
            
            // For OAuth login, redirect happens via button click, not AJAX
            if (this.selectedMethod === 'oauth_login') {
                notify('info', 'Please click the OAuth login button above');
                return;
            }
            
            const additionalData = {};
            if (this.businessType === 'social_media_account') {
                additionalData.platform = $('select[name="platform"]').val();
                additionalData.handle = $('input[name="social_username"]').val();
            } else if (this.selectedMethod === 'file_upload') {
                additionalData.filename = prompt('Enter the filename (default: marketplace-verification.txt):', 'marketplace-verification.txt') || 'marketplace-verification.txt';
            }
            
            // Optimistic UI update
            const btn = $('#validateOwnershipBtn');
            const originalHtml = btn.html();
            btn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i>Validating...');
            this.isValidating = true;
            
            $.ajax({
                url: '{{ route("user.ownership.validation.validate") }}',
                method: 'POST',
                data: {
                    business_type: this.businessType,
                    primary_asset_url: this.primaryAssetUrl,
                    method: this.selectedMethod,
                    token: this.verificationToken,
                    additional_data: additionalData,
                    _token: '{{ csrf_token() }}'
                },
                timeout: 25000, // 25 second timeout for validation (reduced from 30)
                success: function(response) {
                    self.isValidating = false;
                    if (response.success) {
                        self.isVerified = true;
                        self.showVerifiedStatus();
                        notify('success', response.message || 'Ownership verified successfully!');
                    } else {
                        notify('error', response.message || 'Ownership verification failed');
                        $('#validationResult').html('<div class="alert alert-danger">' + response.message + '</div>').show();
                        btn.prop('disabled', false).html(originalHtml);
                    }
                },
                error: function(xhr, status, error) {
                    self.isValidating = false;
                    let message = 'Validation failed';
                    if (status === 'timeout') {
                        message = 'Validation request timed out. Please check your connection.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    notify('error', message);
                    $('#validationResult').html('<div class="alert alert-danger">' + message + '</div>').show();
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        },
        
        getCurrentAssetUrl: function() {
            // First, try to get business type from form if not set
            if (!this.businessType) {
                this.businessType = $('input[name="business_type"]:checked').val() || null;
            }
            
            // Priority: Check visible input sections first (most reliable)
            if ($('#domainInputSection').is(':visible')) {
                const domainValue = $('#domainNameInput').val() || '';
                if (domainValue.trim()) {
                    if (!this.businessType) this.businessType = 'domain';
                    return domainValue.trim();
                }
            }
            
            if ($('#websiteInputSection').is(':visible')) {
                const websiteValue = $('#websiteUrlInput').val() || '';
                if (websiteValue.trim()) {
                    if (!this.businessType) this.businessType = 'website';
                    return websiteValue.trim();
                }
            }
            
            // Fallback: Get URL based on business type if set
            if (this.businessType === 'domain') {
                const domainValue = $('#domainNameInput').val() || '';
                return domainValue.trim();
            } else if (this.businessType === 'website') {
                const websiteValue = $('#websiteUrlInput').val() || '';
                return websiteValue.trim();
            } else if (this.businessType === 'social_media_account') {
                const platform = $('select[name="platform"]').val();
                const username = $('input[name="social_username"]').val();
                const url = $('input[name="social_url"]').val();
                return (url || (platform && username ? platform + '/' + username : '')).trim();
            }
            
            return '';
        },
        
        showVerifiedStatus: function() {
            $('#validationStatus').show();
            $('#generateTokenBtn, #validateOwnershipBtn').hide();
            $('#validationInstructions, #validationResult').hide();
            $('#validationMethodsList').html('<div class="alert alert-success"><i class="las la-check-circle me-2"></i>Ownership has been verified.</div>');
            $('#step1ContinueBtn').prop('disabled', false);
        }
    };
    
    // Initialize ownership validation
    ownershipValidation.init();
    
    // Handle Step 2 to Step 3 navigation (conditional skip for apps)
    $('#step2ContinueBtn').on('click', function(e) {
        const businessType = $('input[name="business_type"]:checked').val();
        const requiresValidation = ['domain', 'website', 'social_media_account'];
        
        // If verification not required, skip Step 3 and go to Step 4
        if (!requiresValidation.includes(businessType)) {
            e.preventDefault();
            e.stopPropagation();
            // Skip Step 3, go directly to Step 4
            ListingFormHandler.showStep(4);
            return false;
        }
        // Otherwise, proceed to Step 3 normally
    });
    
    // Handle Step 3 to Step 4 navigation (check verification)
    $('#step3ContinueBtn').on('click', function(e) {
        const businessType = $('input[name="business_type"]:checked').val();
        const requiresValidation = ['domain', 'website', 'social_media_account'];
        
        if (requiresValidation.includes(businessType)) {
            // Instant check - no delay
            if (!ownershipValidation.isVerified) {
                e.preventDefault();
                e.stopPropagation();
                // Scroll to validation section if not visible
                const validationSection = $('#ownershipValidationSection');
                if (validationSection.length && !validationSection.is(':visible')) {
                    validationSection.show();
                }
                $('html, body').animate({
                    scrollTop: validationSection.offset().top - 100
                }, 300);
                notify('error', 'Please verify ownership before continuing');
                return false;
            }
        }
    });

});
</script>
@endpush
