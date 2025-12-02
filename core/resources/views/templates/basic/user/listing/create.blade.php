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
                                                <input type="text" name="domain_name" class="form-control" 
                                                       value="{{ old('domain_name') }}" placeholder="example.com">
                                            </div>
                                            <small class="text-muted">@lang('Enter domain without http:// or https://')</small>
                                        </div>
                                        
                                        @if(($marketplaceSettings['require_domain_verification'] ?? '1') == '1')
                                            @php
                                                $allowedMethods = \App\Models\MarketplaceSetting::getDomainVerificationMethods();
                                            @endphp
                                            <div class="col-12">
                                                <div class="card border-warning verification-section" id="domainVerificationSection" style="display: none;">
                                                    <div class="card-header bg-warning bg-opacity-10">
                                                        <h6 class="mb-0">
                                                            <i class="las la-shield-alt me-2"></i>@lang('Verification Required')
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <p class="mb-3 small">@lang('You must verify ownership of this domain before your listing can be submitted.')</p>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">@lang('Verification Method')</label>
                                                            <select name="verification_method" id="domainVerificationMethod" class="form-select">
                                                                @if(in_array('txt_file', $allowedMethods))
                                                                    <option value="txt_file">@lang('Upload TXT File to Root')</option>
                                                                @endif
                                                                @if(in_array('dns_record', $allowedMethods))
                                                                    <option value="dns_record">@lang('Add DNS TXT Record')</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        
                                                        {{-- TXT File Method --}}
                                                        <div id="txtFileMethod" class="verification-method-content">
                                                            <div class="alert alert-light border">
                                                                <h6 class="mb-2">@lang('Step 1: Download Verification File')</h6>
                                                                <p class="mb-2 small">@lang('Download the verification file and upload it to your domain root.')</p>
                                                                <button type="button" class="btn btn-sm btn-outline-primary" id="downloadTxtFile">
                                                                    <i class="las la-download me-1"></i>@lang('Download TXT File')
                                                                </button>
                                                                <div class="mt-2">
                                                                    <small class="text-muted">
                                                                        <strong>@lang('File name'):</strong> <code id="txtFileName">-</code><br>
                                                                        <strong>@lang('Upload to'):</strong> <code id="txtFileLocation">-</code>
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        {{-- DNS Record Method --}}
                                                        <div id="dnsRecordMethod" class="verification-method-content" style="display: none;">
                                                            <div class="alert alert-light border">
                                                                <h6 class="mb-2">@lang('Step 1: Add DNS TXT Record')</h6>
                                                                <p class="mb-2 small">@lang('Add the following TXT record to your domain DNS settings:')</p>
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm table-bordered">
                                                                        <tr>
                                                                            <th width="30%">@lang('Type')</th>
                                                                            <th width="35%">@lang('Name/Host')</th>
                                                                            <th width="35%">@lang('Value/Content')</th>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><code>TXT</code></td>
                                                                            <td><code id="dnsRecordName">-</code></td>
                                                                            <td><code id="dnsRecordValue" class="text-break">-</code></td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                                <small class="text-muted">@lang('DNS propagation may take up to 24-48 hours.')</small>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mt-3">
                                                            <button type="button" class="btn btn--base btn-sm" id="verifyDomainBtn">
                                                                <i class="las la-check-circle me-1"></i>@lang('Verify Ownership')
                                                            </button>
                                                            <span id="verificationStatus" class="ms-2"></span>
                                                        </div>
                                                        
                                                        <input type="hidden" name="domain_verified" id="domainVerified" value="0">
                                                        <input type="hidden" name="verification_token" id="domainVerificationToken" value="">
                                                        <input type="hidden" name="verification_filename" id="domainVerificationFilename" value="">
                                                        <input type="hidden" name="verification_dns_name" id="domainVerificationDnsName" value="">
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        
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
                                                <input type="url" name="website_url" class="form-control" 
                                                       value="{{ old('website_url') }}" placeholder="https://example.com">
                                            </div>
                                        </div>
                                        
                                        @if(($marketplaceSettings['require_website_verification'] ?? '1') == '1')
                                            @php
                                                $allowedMethods = \App\Models\MarketplaceSetting::getDomainVerificationMethods();
                                            @endphp
                                            <div class="col-12">
                                                <div class="card border-warning verification-section" id="websiteVerificationSection" style="display: none;">
                                                    <div class="card-header bg-warning bg-opacity-10">
                                                        <h6 class="mb-0">
                                                            <i class="las la-shield-alt me-2"></i>@lang('Verification Required')
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <p class="mb-3 small">@lang('You must verify ownership of this website before your listing can be submitted.')</p>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">@lang('Verification Method')</label>
                                                            <select name="verification_method" id="websiteVerificationMethod" class="form-select">
                                                                @if(in_array('txt_file', $allowedMethods))
                                                                    <option value="txt_file">@lang('Upload TXT File to Root')</option>
                                                                @endif
                                                                @if(in_array('dns_record', $allowedMethods))
                                                                    <option value="dns_record">@lang('Add DNS TXT Record')</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        
                                                        {{-- TXT File Method --}}
                                                        <div id="websiteTxtFileMethod" class="verification-method-content">
                                                            <div class="alert alert-light border">
                                                                <h6 class="mb-2">@lang('Step 1: Download Verification File')</h6>
                                                                <p class="mb-2 small">@lang('Download the verification file and upload it to your website root.')</p>
                                                                <button type="button" class="btn btn-sm btn-outline-primary" id="downloadWebsiteTxtFile">
                                                                    <i class="las la-download me-1"></i>@lang('Download TXT File')
                                                                </button>
                                                                <div class="mt-2">
                                                                    <small class="text-muted">
                                                                        <strong>@lang('File name'):</strong> <code id="websiteTxtFileName">-</code><br>
                                                                        <strong>@lang('Upload to'):</strong> <code id="websiteTxtFileLocation">-</code>
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        {{-- DNS Record Method --}}
                                                        <div id="websiteDnsRecordMethod" class="verification-method-content" style="display: none;">
                                                            <div class="alert alert-light border">
                                                                <h6 class="mb-2">@lang('Step 1: Add DNS TXT Record')</h6>
                                                                <p class="mb-2 small">@lang('Add the following TXT record to your domain DNS settings:')</p>
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm table-bordered">
                                                                        <tr>
                                                                            <th width="30%">@lang('Type')</th>
                                                                            <th width="35%">@lang('Name/Host')</th>
                                                                            <th width="35%">@lang('Value/Content')</th>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><code>TXT</code></td>
                                                                            <td><code id="websiteDnsRecordName">-</code></td>
                                                                            <td><code id="websiteDnsRecordValue" class="text-break">-</code></td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                                <small class="text-muted">@lang('DNS propagation may take up to 24-48 hours.')</small>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mt-3">
                                                            <button type="button" class="btn btn--base btn-sm" id="verifyWebsiteBtn">
                                                                <i class="las la-check-circle me-1"></i>@lang('Verify Ownership')
                                                            </button>
                                                            <span id="websiteVerificationStatus" class="ms-2"></span>
                                                        </div>
                                                        
                                                        <input type="hidden" name="domain_verified" id="websiteVerified" value="0">
                                                        <input type="hidden" name="verification_token" id="websiteVerificationToken" value="">
                                                        <input type="hidden" name="verification_filename" id="websiteVerificationFilename" value="">
                                                        <input type="hidden" name="verification_dns_name" id="websiteVerificationDnsName" value="">
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        
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
                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold">@lang('Listing Title') <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control form-control-lg" 
                                               value="{{ old('title') }}" 
                                               placeholder="@lang('e.g., Premium Tech Blog with 50k Monthly Visitors')" required>
                                        <small class="text-muted">@lang('Write a compelling title that attracts buyers')</small>
                                    </div>
                                    <div class="col-md-4">
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
                                
                                {{-- Financials Section --}}
                                <div class="mt-4 p-3 bg-light rounded">
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
            if (!$('input[name="title"]').val()) {
                notify('error', '@lang("Please enter a listing title")');
                return;
            }
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
        
        // Hide all business fields
        $('.business-fields').addClass('d-none');
        
        // Show relevant fields
        $(`.${type}-fields`).removeClass('d-none');
        
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
    
    // Initialize business type if pre-selected
    const preselectedType = $('input[name="business_type"]:checked').val();
    if (preselectedType) {
        $('input[name="business_type"]:checked').trigger('change');
    }
    
    // ============================================
    // Domain/Website Verification Logic
    // ============================================
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
    
    // Show verification section when domain/website is entered
    $('input[name="domain_name"]').on('input', function() {
        const domain = $(this).val().trim();
        if (domain) {
            $('#domainVerificationSection').slideDown();
            generateDomainVerification(domain);
        } else {
            $('#domainVerificationSection').slideUp();
        }
    });
    
    $('input[name="website_url"]').on('input', function() {
        const url = $(this).val().trim();
        if (url) {
            try {
                const urlObj = new URL(url);
                const domain = urlObj.hostname.replace(/^www\./, '');
                $('#websiteVerificationSection').slideDown();
                generateWebsiteVerification(domain);
            } catch(e) {
                // Invalid URL, hide verification
                $('#websiteVerificationSection').slideUp();
            }
        } else {
            $('#websiteVerificationSection').slideUp();
        }
    });
    
    // Generate verification data for domain
    function generateDomainVerification(domain) {
        domainVerificationData.domain = domain;
        domainVerificationData.token = 'escrow-verify-' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
        domainVerificationData.filename = 'escrow-verification-' + Math.random().toString(36).substring(2, 10) + '.txt';
        domainVerificationData.dnsName = '_escrow-verify';
        
        updateDomainVerificationDisplay();
    }
    
    // Generate verification data for website
    function generateWebsiteVerification(domain) {
        websiteVerificationData.domain = domain;
        websiteVerificationData.token = 'escrow-verify-' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
        websiteVerificationData.filename = 'escrow-verification-' + Math.random().toString(36).substring(2, 10) + '.txt';
        websiteVerificationData.dnsName = '_escrow-verify';
        
        updateWebsiteVerificationDisplay();
    }
    
    // Update domain verification display
    function updateDomainVerificationDisplay() {
        const method = $('#domainVerificationMethod').val();
        
        if (method === 'txt_file') {
            $('#txtFileMethod').show();
            $('#dnsRecordMethod').hide();
            $('#txtFileName').text(domainVerificationData.filename);
            $('#txtFileLocation').text('https://' + domainVerificationData.domain + '/' + domainVerificationData.filename);
        } else {
            $('#txtFileMethod').hide();
            $('#dnsRecordMethod').show();
            $('#dnsRecordName').text(domainVerificationData.dnsName);
            $('#dnsRecordValue').text(domainVerificationData.token);
        }
        
        // Update hidden fields
        $('#domainVerificationToken').val(domainVerificationData.token);
        $('#domainVerificationFilename').val(domainVerificationData.filename);
        $('#domainVerificationDnsName').val(domainVerificationData.dnsName);
        
        // Reset verification status
        $('#domainVerified').val('0');
        $('#verificationStatus').html('');
    }
    
    // Update website verification display
    function updateWebsiteVerificationDisplay() {
        const method = $('#websiteVerificationMethod').val();
        
        if (method === 'txt_file') {
            $('#websiteTxtFileMethod').show();
            $('#websiteDnsRecordMethod').hide();
            $('#websiteTxtFileName').text(websiteVerificationData.filename);
            $('#websiteTxtFileLocation').text('https://' + websiteVerificationData.domain + '/' + websiteVerificationData.filename);
        } else {
            $('#websiteTxtFileMethod').hide();
            $('#websiteDnsRecordMethod').show();
            $('#websiteDnsRecordName').text(websiteVerificationData.dnsName);
            $('#websiteDnsRecordValue').text(websiteVerificationData.token);
        }
        
        // Update hidden fields
        $('#websiteVerificationToken').val(websiteVerificationData.token);
        $('#websiteVerificationFilename').val(websiteVerificationData.filename);
        $('#websiteVerificationDnsName').val(websiteVerificationData.dnsName);
        
        // Reset verification status
        $('#websiteVerified').val('0');
        $('#websiteVerificationStatus').html('');
    }
    
    // Change verification method
    $('#domainVerificationMethod').on('change', updateDomainVerificationDisplay);
    $('#websiteVerificationMethod').on('change', updateWebsiteVerificationDisplay);
    
    // Download TXT file for domain
    $('#downloadTxtFile').on('click', function() {
        if (!domainVerificationData.token) return;
        
        const blob = new Blob([domainVerificationData.token], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = domainVerificationData.filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    });
    
    // Download TXT file for website
    $('#downloadWebsiteTxtFile').on('click', function() {
        if (!websiteVerificationData.token) return;
        
        const blob = new Blob([websiteVerificationData.token], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = websiteVerificationData.filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    });
    
    // Verify domain ownership
    $('#verifyDomainBtn').on('click', function() {
        const btn = $(this);
        const method = $('#domainVerificationMethod').val();
        const domain = domainVerificationData.domain;
        const token = domainVerificationData.token;
        
        if (!domain || !token) {
            notify('error', '@lang("Please enter a domain name first")');
            return;
        }
        
        btn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i>@lang("Verifying...")');
        $('#verificationStatus').html('');
        
        $.ajax({
            url: '{{ route("user.verification.verify-ajax") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                domain: domain,
                method: method,
                token: token,
                filename: domainVerificationData.filename,
                dns_name: domainVerificationData.dnsName
            },
            success: function(response) {
                if (response.success) {
                    $('#domainVerified').val('1');
                    $('#verificationStatus').html('<span class="badge badge--success"><i class="las la-check-circle"></i> @lang("Verified")</span>');
                    notify('success', '@lang("Domain ownership verified successfully!")');
                } else {
                    $('#domainVerified').val('0');
                    $('#verificationStatus').html('<span class="badge badge--danger"><i class="las la-times-circle"></i> @lang("Not Verified")</span>');
                    notify('error', response.message || '@lang("Verification failed. Please check and try again.")');
                }
            },
            error: function(xhr) {
                $('#domainVerified').val('0');
                $('#verificationStatus').html('<span class="badge badge--danger"><i class="las la-times-circle"></i> @lang("Error")</span>');
                const message = xhr.responseJSON?.message || '@lang("An error occurred during verification")';
                notify('error', message);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="las la-check-circle me-1"></i>@lang("Verify Ownership")');
            }
        });
    });
    
    // Verify website ownership
    $('#verifyWebsiteBtn').on('click', function() {
        const btn = $(this);
        const method = $('#websiteVerificationMethod').val();
        const domain = websiteVerificationData.domain;
        const token = websiteVerificationData.token;
        
        if (!domain || !token) {
            notify('error', '@lang("Please enter a website URL first")');
            return;
        }
        
        btn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i>@lang("Verifying...")');
        $('#websiteVerificationStatus').html('');
        
        $.ajax({
            url: '{{ route("user.verification.verify-ajax") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                domain: domain,
                method: method,
                token: token,
                filename: websiteVerificationData.filename,
                dns_name: websiteVerificationData.dnsName
            },
            success: function(response) {
                if (response.success) {
                    $('#websiteVerified').val('1');
                    $('#websiteVerificationStatus').html('<span class="badge badge--success"><i class="las la-check-circle"></i> @lang("Verified")</span>');
                    notify('success', '@lang("Website ownership verified successfully!")');
                } else {
                    $('#websiteVerified').val('0');
                    $('#websiteVerificationStatus').html('<span class="badge badge--danger"><i class="las la-times-circle"></i> @lang("Not Verified")</span>');
                    notify('error', response.message || '@lang("Verification failed. Please check and try again.")');
                }
            },
            error: function(xhr) {
                $('#websiteVerified').val('0');
                $('#websiteVerificationStatus').html('<span class="badge badge--danger"><i class="las la-times-circle"></i> @lang("Error")</span>');
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
        const requireDomainVerification = {{ ($marketplaceSettings['require_domain_verification'] ?? '1') == '1' ? 'true' : 'false' }};
        const requireWebsiteVerification = {{ ($marketplaceSettings['require_website_verification'] ?? '1') == '1' ? 'true' : 'false' }};
        
        if (businessType === 'domain' && requireDomainVerification) {
            if ($('#domainVerified').val() !== '1') {
                e.preventDefault();
                notify('error', '@lang("You must verify domain ownership before submitting the listing")');
                showStep(2); // Go back to step 2
                return false;
            }
        }
        
        if (businessType === 'website' && requireWebsiteVerification) {
            if ($('#websiteVerified').val() !== '1') {
                e.preventDefault();
                notify('error', '@lang("You must verify website ownership before submitting the listing")');
                showStep(2); // Go back to step 2
                return false;
            }
        }
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
