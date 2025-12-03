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
                                                    <input type="text" name="niche" class="form-control" 
                                                           value="{{ old('niche', $draftData['niche'] ?? '') }}" placeholder="@lang('e.g., Technology, Health, Finance')">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">@lang('Tech Stack / Platform')</label>
                                                    <input type="text" name="tech_stack" class="form-control" 
                                                           value="{{ old('tech_stack', $draftData['tech_stack'] ?? '') }}" placeholder="@lang('e.g., WordPress, Shopify, Laravel')">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">@lang('Domain Registrar')</label>
                                                    <input type="text" name="domain_registrar" class="form-control" 
                                                           value="{{ old('domain_registrar', $draftData['domain_registrar'] ?? '') }}" placeholder="@lang('e.g., GoDaddy, Namecheap')">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">@lang('Domain Expiry')</label>
                                                    <input type="date" name="domain_expiry" class="form-control" value="{{ old('domain_expiry', $draftData['domain_expiry'] ?? '') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Verification Section (for domain/website only) --}}
                                @php
                                    $requireWebsiteVerification = \App\Models\MarketplaceSetting::requireWebsiteVerification();
                                    $requireDomainVerification = \App\Models\MarketplaceSetting::requireDomainVerification();
                                    $allowedMethods = \App\Models\MarketplaceSetting::getDomainVerificationMethods();
                                @endphp
                                
                                {{-- Domain Verification Section --}}
                                <div id="domainVerificationSection" class="mb-4" style="display: none;">
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning bg-opacity-10">
                                            <h6 class="mb-0">
                                                <i class="las la-shield-alt me-2"></i>@lang('Verify Domain Ownership')
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted small mb-3">@lang('You must verify ownership of this domain before you can continue.')</p>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">@lang('Verification Method')</label>
                                                <select name="verification_method" id="verificationMethodSelect" class="form-select">
                                                    @if(in_array('txt_file', $allowedMethods))
                                                        <option value="txt_file">@lang('Upload TXT File to Root')</option>
                                                    @endif
                                                    @if(in_array('dns_record', $allowedMethods))
                                                        <option value="dns_record">@lang('Add DNS TXT Record')</option>
                                                    @endif
                                                </select>
                                            </div>
                                            
                                            {{-- TXT File Method --}}
                                            <div id="txtFileVerificationMethod" class="verification-method-content">
                                                <div class="alert alert-info border">
                                                    <h6 class="mb-3"><i class="las la-file-alt me-2"></i>@lang('File Upload Method')</h6>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">@lang('Step 1: Download the verification file')</label>
                                                        <button type="button" class="btn btn-sm btn-primary" id="downloadVerificationFile">
                                                            <i class="las la-download me-1"></i>@lang('Download File')
                                                        </button>
                                                    </div>
                                                    
                                                    <div class="mb-3 p-3 bg-light rounded">
                                                        <small class="text-muted d-block mb-2"><strong>@lang('File Details'):</strong></small>
                                                        <table class="table table-sm table-bordered mb-0">
                                                            <tr>
                                                                <td width="40%" class="fw-semibold">@lang('File Name'):</td>
                                                                <td><code id="verificationFileName" class="text-break">-</code></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="fw-semibold">@lang('Upload Location'):</td>
                                                                <td><code id="verificationFileLocation" class="text-break">-</code></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="fw-semibold">@lang('File Content'):</td>
                                                                <td><code id="verificationFileContent" class="text-break small">-</code></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    
                                                    <div class="mb-0">
                                                        <label class="form-label fw-semibold">@lang('Step 2: Upload the file')</label>
                                                        <ol class="small mb-0">
                                                            <li>@lang('Upload the downloaded file to your domain root directory')</li>
                                                            <li>@lang('Common locations:') <code>public_html/</code>, <code>www/</code>, <code>public/</code>, or <code>htdocs/</code></li>
                                                            <li>@lang('The file must be accessible at:') <code id="verificationFileUrl">-</code></li>
                                                            <li>@lang('Make sure the file contains ONLY the verification token (no extra text)')</li>
                                                        </ol>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            {{-- DNS Record Method --}}
                                            <div id="dnsRecordVerificationMethod" class="verification-method-content" style="display: none;">
                                                <div class="alert alert-info border">
                                                    <h6 class="mb-3"><i class="las la-server me-2"></i>@lang('DNS TXT Record Method')</h6>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">@lang('Step 1: Add DNS TXT Record')</label>
                                                        <p class="small mb-2">@lang('Go to your domain registrar or DNS provider and add the following TXT record:')</p>
                                                        
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th width="20%">@lang('Type')</th>
                                                                        <th width="30%">@lang('Name/Host')</th>
                                                                        <th width="50%">@lang('Value/Content')</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td><code>TXT</code></td>
                                                                        <td><code id="verificationDnsName" class="text-break">-</code></td>
                                                                        <td><code id="verificationDnsValue" class="text-break small">-</code></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-0">
                                                        <label class="form-label fw-semibold">@lang('Step 2: Wait for DNS propagation')</label>
                                                        <ul class="small mb-0">
                                                            <li>@lang('DNS changes typically take 5-30 minutes, but can take up to 48 hours')</li>
                                                            <li>@lang('After adding the record, wait a few minutes before clicking "Verify Ownership"')</li>
                                                            <li>@lang('You can check if the record is live using online DNS lookup tools')</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-4 pt-3 border-top">
                                                <button type="button" class="btn btn--base" id="verifyOwnershipBtn">
                                                    <i class="las la-check-circle me-1"></i>@lang('Verify Ownership')
                                                </button>
                                                <span id="verificationStatus" class="ms-3"></span>
                                            </div>
                                            
                                            <input type="hidden" name="domain_verified" id="domainVerified" value="0">
                                            <input type="hidden" name="verification_token" id="verificationToken" value="">
                                            <input type="hidden" name="verification_filename" id="verificationFilename" value="">
                                            <input type="hidden" name="verification_dns_name" id="verificationDnsNameInput" value="">
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Website Verification Section --}}
                                <div id="websiteVerificationSection" class="mb-4" style="display: none;">
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning bg-opacity-10">
                                            <h6 class="mb-0">
                                                <i class="las la-shield-alt me-2"></i>@lang('Verify Website Ownership')
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted small mb-3">@lang('You must verify ownership of this website before you can continue.')</p>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">@lang('Verification Method')</label>
                                                <select name="website_verification_method" id="websiteVerificationMethodSelect" class="form-select">
                                                    @if(in_array('txt_file', $allowedMethods))
                                                        <option value="txt_file">@lang('Upload TXT File to Root')</option>
                                                    @endif
                                                    @if(in_array('dns_record', $allowedMethods))
                                                        <option value="dns_record">@lang('Add DNS TXT Record')</option>
                                                    @endif
                                                </select>
                                            </div>
                                            
                                            {{-- Same verification methods UI as domain --}}
                                            <div id="websiteTxtFileVerificationMethod" class="verification-method-content" style="display: none;">
                                                <div class="alert alert-info border">
                                                    <h6 class="mb-3"><i class="las la-file-alt me-2"></i>@lang('File Upload Method')</h6>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">@lang('Step 1: Download the verification file')</label>
                                                        <button type="button" class="btn btn-sm btn-primary" id="downloadWebsiteVerificationFile">
                                                            <i class="las la-download me-1"></i>@lang('Download File')
                                                        </button>
                                                    </div>
                                                    
                                                    <div class="mb-3 p-3 bg-light rounded">
                                                        <small class="text-muted d-block mb-2"><strong>@lang('File Details'):</strong></small>
                                                        <table class="table table-sm table-bordered mb-0">
                                                            <tr>
                                                                <td width="40%" class="fw-semibold">@lang('File Name'):</td>
                                                                <td><code id="websiteVerificationFileName" class="text-break">-</code></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="fw-semibold">@lang('Upload Location'):</td>
                                                                <td><code id="websiteVerificationFileLocation" class="text-break">-</code></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="fw-semibold">@lang('File Content'):</td>
                                                                <td><code id="websiteVerificationFileContent" class="text-break small">-</code></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    
                                                    <div class="mb-0">
                                                        <label class="form-label fw-semibold">@lang('Step 2: Upload the file')</label>
                                                        <ol class="small mb-0">
                                                            <li>@lang('Upload the downloaded file to your website root directory')</li>
                                                            <li>@lang('Common locations:') <code>public_html/</code>, <code>www/</code>, <code>public/</code>, or <code>htdocs/</code></li>
                                                            <li>@lang('The file must be accessible at:') <code id="websiteVerificationFileUrl">-</code></li>
                                                            <li>@lang('Make sure the file contains ONLY the verification token (no extra text)')</li>
                                                        </ol>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div id="websiteDnsRecordVerificationMethod" class="verification-method-content" style="display: none;">
                                                <div class="alert alert-info border">
                                                    <h6 class="mb-3"><i class="las la-server me-2"></i>@lang('DNS TXT Record Method')</h6>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">@lang('Step 1: Add DNS TXT Record')</label>
                                                        <p class="small mb-2">@lang('Go to your domain registrar or DNS provider and add the following TXT record:')</p>
                                                        
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th width="20%">@lang('Type')</th>
                                                                        <th width="30%">@lang('Name/Host')</th>
                                                                        <th width="50%">@lang('Value/Content')</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td><code>TXT</code></td>
                                                                        <td><code id="websiteVerificationDnsName" class="text-break">-</code></td>
                                                                        <td><code id="websiteVerificationDnsValue" class="text-break small">-</code></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-0">
                                                        <label class="form-label fw-semibold">@lang('Step 2: Wait for DNS propagation')</label>
                                                        <ul class="small mb-0">
                                                            <li>@lang('DNS changes typically take 5-30 minutes, but can take up to 48 hours')</li>
                                                            <li>@lang('After adding the record, wait a few minutes before clicking "Verify Ownership"')</li>
                                                            <li>@lang('You can check if the record is live using online DNS lookup tools')</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-4 pt-3 border-top">
                                                <button type="button" class="btn btn--base" id="verifyWebsiteOwnershipBtn">
                                                    <i class="las la-check-circle me-1"></i>@lang('Verify Ownership')
                                                </button>
                                                <span id="websiteVerificationStatus" class="ms-3"></span>
                                            </div>
                                            
                                            <input type="hidden" name="website_verified" id="websiteVerified" value="0">
                                            <input type="hidden" name="website_verification_token" id="websiteVerificationToken" value="">
                                            <input type="hidden" name="website_verification_filename" id="websiteVerificationFilename" value="">
                                            <input type="hidden" name="website_verification_dns_name" id="websiteVerificationDnsNameInput" value="">
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Verification Not Required Message --}}
                                <div id="verificationNotRequired" class="alert alert-success mb-4" style="display: none;">
                                    <div class="d-flex align-items-start">
                                        <i class="las la-check-circle fs-4 me-3 mt-1"></i>
                                        <div>
                                            <h6 class="fw-bold mb-2">@lang('Verification Not Required')</h6>
                                            <p class="mb-0 small">@lang('Verification is not required for this type of listing. You can proceed to the next step.')</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="step-actions mt-4 d-flex justify-content-between">
                                    <div></div>
                                    <button type="button" class="btn btn--base btn-next" data-next="2" id="step1ContinueBtn" disabled>
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
                                    <button type="button" class="btn btn--base btn-next" data-next="5" id="continueToVerificationBtn">
                                        @lang('Continue') <i class="las la-arrow-right ms-1"></i>
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
    let currentStep = {{ $currentStage ?? 1 }};
    const totalSteps = 4;
    let autoSaveTimer = null;
    const autoSaveDelay = 2000; // 2 seconds after user stops typing
    
    // Restore stage on page load
    if (currentStep > 1) {
        showStep(currentStep);
        // Show notification that draft was restored
        if ({{ !empty($draftData) ? 'true' : 'false' }}) {
            setTimeout(function() {
                notify('info', '@lang("Draft restored. Your previous progress has been loaded.")');
            }, 500);
        }
    }
    
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
        
        // Auto-save when changing steps
        saveDraft();
    }
    
    // Auto-save draft function
    function saveDraft() {
        clearTimeout(autoSaveTimer);
        
        autoSaveTimer = setTimeout(function() {
            // Collect all form data
            const draftData = {
                current_stage: currentStep,
                _token: $('input[name="_token"]').val()
            };
            
            // Get all form inputs
            $('#listingForm').find('input, textarea, select').each(function() {
                const $field = $(this);
                const name = $field.attr('name');
                const type = $field.attr('type');
                
                if (!name || name === '_token' || name === 'images[]') {
                    return;
                }
                
                if (type === 'checkbox' || type === 'radio') {
                    if ($field.is(':checked')) {
                        draftData[name] = $field.val();
                    }
                } else if (type === 'file') {
                    // Skip file inputs
                    return;
                } else {
                    draftData[name] = $field.val() || '';
                }
            });
            
            $.ajax({
                url: '{{ route("user.listing.draft.save") }}',
                method: 'POST',
                data: draftData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        // Show draft indicator
                        if ($('.draft-indicator').length === 0) {
                            $('.card-header').find('h5').after(`
                                <div class="draft-indicator">
                                    <span class="badge bg-info">
                                        <i class="las la-save me-1"></i>
                                        @lang('Draft Saved')
                                    </span>
                                </div>
                            `);
                        }
                    }
                },
                error: function() {
                    console.error('Failed to save draft');
                }
            });
        }, autoSaveDelay);
    }
    
    // Auto-save on form field changes
    $('#listingForm').on('input change', 'input, textarea, select', function() {
        saveDraft();
    });
    
    // Clear draft button
    $('#clearDraftBtn').on('click', function() {
        if (confirm('@lang("Are you sure you want to clear the draft? All unsaved data will be lost.")')) {
            $.ajax({
                url: '{{ route("user.listing.draft.clear") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('.draft-indicator').remove();
                        notify('success', '@lang("Draft cleared successfully")');
                        // Optionally reload page to reset form
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    }
                }
            });
        }
    });
    
    // Next button
    $(document).on('click', '.btn-next', function() {
        const nextStep = $(this).data('next');
        
        // Validation for step 1
        if (currentStep === 1) {
            if (!$('input[name="business_type"]:checked').val()) {
                notify('error', '@lang("Please select a business type")');
                return;
            }
            
            const businessType = $('input[name="business_type"]:checked').val();
            const requireDomainVerification = {{ \App\Models\MarketplaceSetting::requireDomainVerification() ? 'true' : 'false' }};
            const requireWebsiteVerification = {{ \App\Models\MarketplaceSetting::requireWebsiteVerification() ? 'true' : 'false' }};
            
            // Check if domain/website is entered
            if (businessType === 'domain') {
                const domainInput = $('#domainNameInput').val().trim();
                if (!domainInput) {
                    notify('error', '@lang("Please enter a domain name")');
                    return;
                }
                // Check verification if required
                if (requireDomainVerification) {
                    const verified = $('#domainVerified').val();
                    if (!verified || verified !== '1') {
                        notify('error', '@lang("You must verify domain ownership before continuing")');
                        return;
                    }
                }
            } else if (businessType === 'website') {
                const websiteInput = $('#websiteUrlInput').val().trim();
                if (!websiteInput) {
                    notify('error', '@lang("Please enter a website URL")');
                    return;
                }
                // Check verification if required
                if (requireWebsiteVerification) {
                    const verified = $('#websiteVerified').val();
                    if (!verified || verified !== '1') {
                        notify('error', '@lang("You must verify website ownership before continuing")');
                        return;
                    }
                }
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
        saveDraft(); // Save draft when moving to next step
    });
    
    // Previous button
    $(document).on('click', '.btn-prev', function() {
        const prevStep = $(this).data('prev');
        showStep(prevStep);
        saveDraft(); // Save draft when going back
    });
    
    // Business type change - show relevant fields in Step 1
    $('input[name="business_type"]').on('change', function() {
        const type = $(this).val();
        const requireDomainVerification = {{ \App\Models\MarketplaceSetting::requireDomainVerification() ? 'true' : 'false' }};
        const requireWebsiteVerification = {{ \App\Models\MarketplaceSetting::requireWebsiteVerification() ? 'true' : 'false' }};
        
        // Hide all input sections
        $('#domainInputSection').hide();
        $('#websiteInputSection').hide();
        $('#domainVerificationSection').hide();
        $('#websiteVerificationSection').hide();
        $('#verificationNotRequired').hide();
        
        // Reset verification status
        $('#domainVerified').val('0');
        $('#websiteVerified').val('0');
        $('#step1ContinueBtn').prop('disabled', true);
        
        // Show relevant input section
        if (type === 'domain') {
            $('#domainInputSection').show();
            $('#domainNameInput').attr('required', 'required');
            if (requireDomainVerification) {
                // Will show verification section when domain is entered
            } else {
                $('#verificationNotRequired').show();
                $('#step1ContinueBtn').prop('disabled', false);
            }
        } else if (type === 'website') {
            $('#websiteInputSection').show();
            $('#websiteUrlInput').attr('required', 'required');
            if (requireWebsiteVerification) {
                // Will show verification section when website is entered
            } else {
                $('#verificationNotRequired').show();
                $('#step1ContinueBtn').prop('disabled', false);
            }
        } else {
            // Other business types don't need verification
            $('#verificationNotRequired').show();
            $('#step1ContinueBtn').prop('disabled', false);
        }
        
        // Hide/show financial section based on business type (for Step 2)
        if (type === 'domain') {
            $('.financial-section').addClass('d-none');
        } else {
            $('.financial-section').removeClass('d-none');
        }
        
        // Update domain card preview when domain is entered
        if (type === 'domain') {
            setTimeout(function() {
                updateDomainCardPreview();
            }, 100);
        }
        
        // Show/hide image upload section based on business type (for Step 4)
        if (type === 'domain') {
            $('.domain-card-preview').removeClass('d-none');
            $('.image-upload-section').addClass('d-none');
        } else {
            $('.domain-card-preview').addClass('d-none');
            $('.image-upload-section').removeClass('d-none');
        }
        
        // Filter categories (for Step 2)
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
    
    // Initialize sale type (restore from draft if available)
    const savedSaleType = $('input[name="sale_type"]:checked').val();
    if (savedSaleType) {
        $('input[name="sale_type"]:checked').trigger('change');
    } else {
        // Default to fixed_price if nothing selected
        $('input[name="sale_type"][value="fixed_price"]').trigger('change');
    }
    
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
    
    // Initialize business type if pre-selected (from draft or old input)
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
    
    // If we're restoring from draft and on a later stage, trigger business type change
    // to show the correct fields
    if (currentStep > 1 && preselectedType) {
        setTimeout(function() {
            $('input[name="business_type"]:checked').trigger('change');
        }, 100);
    }
    
    // Old verification code removed - verification is now in step 5
    
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
            $('#domainVerificationSection').hide();
            $('#step1ContinueBtn').prop('disabled', true);
        } else {
            warning.slideUp();
            $(this).removeClass('is-invalid border-warning');
            helpText.html('@lang("Enter domain with http:// or https:// (e.g., https://example.com)")');
            
            updateDomainCardPreview();
            
            // Setup verification if required
            if (value && requireDomainVerification) {
                try {
                    const urlObj = new URL(value);
                    const domain = urlObj.hostname.replace(/^www\./, '');
                    setupDomainVerification(domain);
                } catch(e) {
                    $('#domainVerificationSection').hide();
                    $('#step1ContinueBtn').prop('disabled', true);
                }
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
            $('#websiteVerificationSection').hide();
            $('#step1ContinueBtn').prop('disabled', true);
        } else {
            warning.slideUp();
            $(this).removeClass('is-invalid border-warning');
            helpText.html('@lang("Enter full URL starting with http:// or https://")');
            
            // Setup verification if required
            if (value && requireWebsiteVerification) {
                try {
                    const urlObj = new URL(value);
                    const domain = urlObj.hostname.replace(/^www\./, '');
                    setupWebsiteVerification(value, domain);
                } catch(e) {
                    $('#websiteVerificationSection').hide();
                    $('#step1ContinueBtn').prop('disabled', true);
                }
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
    
    // Old verification functions removed - verification is now in step 5
    
    // Save draft before form submission
    $('#listingForm').on('submit', function(e) {
        // Clear any pending auto-save
        clearTimeout(autoSaveTimer);
        
        // Final save before submission
        saveDraft();
        
        // Wait a bit for save to complete
        setTimeout(function() {
            // Continue with form submission
        }, 500);
    });
    
    // Validate verification before form submission (from step 5)
    $('#listingForm').on('submit', function(e) {
        // Clear any pending auto-save
        clearTimeout(autoSaveTimer);
        
        // Final save before submission
        saveDraft();
        
        const businessType = $('input[name="business_type"]:checked').val();
        const requireDomainVerification = {{ \App\Models\MarketplaceSetting::requireDomainVerification() ? 'true' : 'false' }};
        const requireWebsiteVerification = {{ \App\Models\MarketplaceSetting::requireWebsiteVerification() ? 'true' : 'false' }};
        
        let shouldPreventSubmit = false;
        
        // Check verification from step 1
        if (businessType === 'domain' && requireDomainVerification === true) {
            const verified = $('#domainVerified').val();
            if (!verified || verified !== '1') {
                shouldPreventSubmit = true;
                notify('error', '@lang("You must verify domain ownership before submitting the listing")');
                showStep(1);
            }
        }
        
        if (businessType === 'website' && requireWebsiteVerification === true) {
            const verified = $('#websiteVerified').val();
            if (!verified || verified !== '1') {
                shouldPreventSubmit = true;
                notify('error', '@lang("You must verify website ownership before submitting the listing")');
                showStep(1);
            }
        }
        
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
        
        // Allow form to submit naturally
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
    
    // ============================================
    // STEP 5: Verification Logic
    // ============================================
    
    let verificationData = {
        domain: null,
        website: null,
        token: null,
        filename: null,
        dnsName: null,
        type: null // 'domain' or 'website'
    };
    
    const requireDomainVerification = {{ \App\Models\MarketplaceSetting::requireDomainVerification() ? 'true' : 'false' }};
    const requireWebsiteVerification = {{ \App\Models\MarketplaceSetting::requireWebsiteVerification() ? 'true' : 'false' }};
    
    function setupDomainVerification(domain) {
        if (!domain) {
            $('#domainVerificationSection').hide();
            $('#step1ContinueBtn').prop('disabled', true);
            return;
        }
        
        verificationData.domain = domain;
        verificationData.type = 'domain';
        
        // Generate verification token
        generateVerificationToken('domain', domain);
        
        // Show domain verification section
        $('#domainVerificationSection').slideDown(300, function() {
            // After showing, update the display to show txt_file method by default
            $('#verificationMethodSelect').val('txt_file');
            updateVerificationDisplay();
        });
        
        // Keep continue button disabled until verified
        $('#step1ContinueBtn').prop('disabled', true);
    }
    
    function setupWebsiteVerification(websiteUrl, domain) {
        if (!domain || !websiteUrl) {
            $('#websiteVerificationSection').hide();
            $('#step1ContinueBtn').prop('disabled', true);
            return;
        }
        
        verificationData.website = websiteUrl;
        verificationData.domain = domain;
        verificationData.type = 'website';
        
        // Generate verification token
        generateVerificationToken('website', domain);
        
        // Show website verification section
        $('#websiteVerificationSection').slideDown(300, function() {
            // After showing, update the display to show txt_file method by default
            $('#websiteVerificationMethodSelect').val('txt_file');
            updateWebsiteVerificationDisplay();
        });
        
        // Keep continue button disabled until verified
        $('#step1ContinueBtn').prop('disabled', true);
    }
    
    function generateVerificationToken(type, domain) {
        // Generate simple token (40 chars alphanumeric)
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let token = '';
        for (let i = 0; i < 40; i++) {
            token += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        
        // Generate filename
        const randomStr = Math.random().toString(36).substring(2, 18);
        const filename = 'verification-' + randomStr + '.txt';
        
        // Generate DNS name
        const dnsRandom = Math.random().toString(36).substring(2, 10);
        const dnsName = '_verify' + dnsRandom;
        
        verificationData.token = token;
        verificationData.filename = filename;
        verificationData.dnsName = dnsName;
    }
    
    function updateVerificationDisplay() {
        // Check if verification section is visible
        if (!$('#domainVerificationSection').is(':visible')) {
            return; // Don't update if section is hidden
        }
        
        if (!verificationData.token || !verificationData.domain) {
            return; // Don't update if data not ready
        }
        
        const method = $('#verificationMethodSelect').val() || 'txt_file';
        
        if (method === 'txt_file') {
            $('#txtFileVerificationMethod').show();
            $('#dnsRecordVerificationMethod').hide();
            
            // Update file details
            $('#verificationFileName').text(verificationData.filename || '-');
            $('#verificationFileLocation').text(verificationData.domain + '/' + verificationData.filename);
            $('#verificationFileUrl').text('https://' + verificationData.domain + '/' + verificationData.filename);
            $('#verificationFileContent').text(verificationData.token || '-');
        } else if (method === 'dns_record') {
            $('#txtFileVerificationMethod').hide();
            $('#dnsRecordVerificationMethod').show();
            
            // Update DNS details
            $('#verificationDnsName').text(verificationData.dnsName || '-');
            $('#verificationDnsValue').text(verificationData.token || '-');
        }
        
        // Update hidden fields
        $('#verificationToken').val(verificationData.token);
        $('#verificationFilename').val(verificationData.filename);
        $('#verificationDnsNameInput').val(verificationData.dnsName);
    }
    
    function updateWebsiteVerificationDisplay() {
        // Check if verification section is visible
        if (!$('#websiteVerificationSection').is(':visible')) {
            return; // Don't update if section is hidden
        }
        
        if (!verificationData.token || !verificationData.domain) {
            return; // Don't update if data not ready
        }
        
        const method = $('#websiteVerificationMethodSelect').val() || 'txt_file';
        
        if (method === 'txt_file') {
            $('#websiteTxtFileVerificationMethod').show();
            $('#websiteDnsRecordVerificationMethod').hide();
            
            // Update file details
            $('#websiteVerificationFileName').text(verificationData.filename || '-');
            $('#websiteVerificationFileLocation').text(verificationData.domain + '/' + verificationData.filename);
            $('#websiteVerificationFileUrl').text('https://' + verificationData.domain + '/' + verificationData.filename);
            $('#websiteVerificationFileContent').text(verificationData.token || '-');
        } else if (method === 'dns_record') {
            $('#websiteTxtFileVerificationMethod').hide();
            $('#websiteDnsRecordVerificationMethod').show();
            
            // Update DNS details
            $('#websiteVerificationDnsName').text(verificationData.dnsName || '-');
            $('#websiteVerificationDnsValue').text(verificationData.token || '-');
        }
        
        // Update hidden fields
        $('#websiteVerificationToken').val(verificationData.token);
        $('#websiteVerificationFilename').val(verificationData.filename);
        $('#websiteVerificationDnsNameInput').val(verificationData.dnsName);
    }
    
    // Verification method change - use event delegation to handle dynamically added elements
    $(document).on('change', '#verificationMethodSelect', function() {
        updateVerificationDisplay();
    });
    
    $(document).on('change', '#websiteVerificationMethodSelect', function() {
        updateWebsiteVerificationDisplay();
    });
    
    // Download verification file
    $('#downloadVerificationFile, #downloadWebsiteVerificationFile').on('click', function() {
        if (!verificationData.token || !verificationData.filename) {
            notify('error', '@lang("Verification token not generated. Please refresh the page.")');
            return;
        }
        
        const blob = new Blob([verificationData.token], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = verificationData.filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        
        notify('success', '@lang("File downloaded successfully")');
    });
    
    // Verify ownership
    $('#verifyOwnershipBtn, #verifyWebsiteOwnershipBtn').on('click', function() {
        const btn = $(this);
        const isWebsite = btn.attr('id') === 'verifyWebsiteOwnershipBtn';
        const method = isWebsite ? $('#websiteVerificationMethodSelect').val() : $('#verificationMethodSelect').val();
        const domain = verificationData.domain;
        const token = verificationData.token;
        const filename = verificationData.filename;
        const dnsName = verificationData.dnsName;
        
        if (!domain || !token) {
            notify('error', '@lang("Verification data not ready. Please refresh the page.")');
            return;
        }
        
        btn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i>@lang("Verifying...")');
        const statusEl = isWebsite ? $('#websiteVerificationStatus') : $('#verificationStatus');
        statusEl.html('');
        
        $.ajax({
            url: '{{ route("user.verification.verify-ajax") }}',
            method: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                domain: domain,
                method: method,
                token: token,
                filename: filename,
                dns_name: dnsName
            },
            success: function(response) {
                if (response.success) {
                    if (isWebsite) {
                        $('#websiteVerified').val('1');
                        statusEl.html('<span class="badge bg-success"><i class="las la-check-circle"></i> @lang("Verified")</span>');
                    } else {
                        $('#domainVerified').val('1');
                        statusEl.html('<span class="badge bg-success"><i class="las la-check-circle"></i> @lang("Verified")</span>');
                    }
                    
                    notify('success', '@lang("Ownership verified successfully!")');
                    // Enable continue button in Step 1
                    $('#step1ContinueBtn').prop('disabled', false);
                } else {
                    if (isWebsite) {
                        $('#websiteVerified').val('0');
                        statusEl.html('<span class="badge bg-danger"><i class="las la-times-circle"></i> @lang("Not Verified")</span>');
                    } else {
                        $('#domainVerified').val('0');
                        statusEl.html('<span class="badge bg-danger"><i class="las la-times-circle"></i> @lang("Not Verified")</span>');
                    }
                    
                    let errorMsg = response.message || '@lang("Verification failed. Please check and try again.")';
                    errorMsg = errorMsg.replace(/\n/g, '<br>');
                    notify('error', errorMsg, 10000);
                }
            },
            error: function(xhr) {
                const statusEl = isWebsite ? $('#websiteVerificationStatus') : $('#verificationStatus');
                if (isWebsite) {
                    $('#websiteVerified').val('0');
                } else {
                    $('#domainVerified').val('0');
                }
                statusEl.html('<span class="badge bg-danger"><i class="las la-times-circle"></i> @lang("Error")</span>');
                
                let message = '@lang("An error occurred during verification")';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                notify('error', message);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="las la-check-circle me-1"></i>@lang("Verify Ownership")');
            }
        });
    });
    
    // Remove old verification code from step 2 - no longer needed
    // The old code that showed verification in step 2 has been removed
});
</script>
@endpush
