@php
    $requireWebsiteVerification = \App\Models\MarketplaceSetting::requireWebsiteVerification();
    $requireDomainVerification = \App\Models\MarketplaceSetting::requireDomainVerification();
@endphp
@if($requireWebsiteVerification || $requireDomainVerification)
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
                            <option value="txt_file" {{ (old('verification_method', 'txt_file') == 'txt_file') ? 'selected' : '' }}>@lang('Upload TXT File to Root')</option>
                        @endif
                        @if(in_array('dns_record', $allowedMethods))
                            <option value="dns_record" {{ (old('verification_method') == 'dns_record') ? 'selected' : '' }}>@lang('Add DNS TXT Record')</option>
                        @endif
                    </select>
                </div>
                
                <div id="websiteTxtFileMethod" class="verification-method-content" style="display: none;">
                    <div class="alert alert-info border">
                        <h6 class="mb-3"><i class="las la-file-alt me-2"></i>@lang('File Upload Method')</h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">@lang('Step 1: Download the verification file')</label>
                            <button type="button" class="btn btn-sm btn-primary" id="downloadWebsiteTxtFile">
                                <i class="las la-download me-1"></i>@lang('Download File')
                            </button>
                        </div>
                        
                        <div class="mb-3 p-3 bg-light rounded">
                            <small class="text-muted d-block mb-2"><strong>@lang('File Details'):</strong></small>
                            <table class="table table-sm table-bordered mb-0">
                                <tr>
                                    <td width="40%" class="fw-semibold">@lang('File Name'):</td>
                                    <td><code id="websiteTxtFileName" class="text-break">-</code></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">@lang('Upload Location'):</td>
                                    <td><code id="websiteTxtFileLocation" class="text-break">-</code></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">@lang('File Content'):</td>
                                    <td><code id="websiteTxtFileContent" class="text-break small">-</code></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="mb-0">
                            <label class="form-label fw-semibold">@lang('Step 2: Upload the file')</label>
                            <ol class="small mb-0">
                                <li>@lang('Upload the downloaded file to your website root directory')</li>
                                <li>@lang('Common locations:') <code>public_html/</code>, <code>www/</code>, <code>public/</code>, or <code>htdocs/</code></li>
                                <li>@lang('The file must be accessible at:') <code id="websiteTxtFileUrl">-</code></li>
                                <li>@lang('Make sure the file contains ONLY the verification token (no extra text)')</li>
                            </ol>
                        </div>
                    </div>
                </div>
                
                <div id="websiteDnsRecordMethod" class="verification-method-content" style="display: none;">
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
                                            <td><code id="websiteDnsRecordName" class="text-break">-</code></td>
                                            <td><code id="websiteDnsRecordValue" class="text-break small">-</code></td>
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
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <button type="button" class="btn btn--base" id="verifyWebsiteBtn">
                                <i class="las la-check-circle me-1"></i>@lang('Verify Ownership')
                            </button>
                            <span id="websiteVerificationStatus" class="ms-3"></span>
                        </div>
                        <small class="text-muted">
                            <i class="las la-info-circle"></i> @lang('You must verify ownership before submitting the listing')
                        </small>
                    </div>
                </div>
                
                <input type="hidden" name="domain_verified" id="websiteVerified" value="0">
                <input type="hidden" name="verification_token" id="websiteVerificationToken" value="">
                <input type="hidden" name="verification_filename" id="websiteVerificationFilename" value="">
                <input type="hidden" name="verification_dns_name" id="websiteVerificationDnsName" value="">
            </div>
        </div>
    </div>
@endif

