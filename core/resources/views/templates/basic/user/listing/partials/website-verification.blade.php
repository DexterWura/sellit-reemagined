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
                        <h6 class="mb-3"><i class="las la-file-alt me-2"></i>@lang('TXT File Upload Method')</h6>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">@lang('Create a file named zimadsense.txt and upload it to your domain root')</label>
                            <button type="button" class="btn btn-sm btn-primary me-2" id="downloadWebsiteTxtFile">
                                <i class="las la-download me-1"></i>@lang('Download zimadsense.txt')
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.WebsiteVerificationHandler.copyVerificationToken()">
                                <i class="las la-copy me-1"></i>@lang('Copy Token')
                            </button>
                        </div>

                        <div class="mb-3 p-3 bg-light rounded">
                            <small class="text-muted d-block mb-2"><strong>@lang('Verification Details'):</strong></small>
                            <div class="mb-2">
                                <strong>@lang('File URL'):</strong> <code id="websiteTxtFileUrl" class="text-break">http://yourdomain.com/zimadsense.txt</code>
                            </div>
                            <div class="mb-2">
                                <strong>@lang('Token'):</strong> <code id="verificationToken" class="text-break text-danger fw-bold">zimadsense-verification=...</code>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">@lang('Instructions')</label>
                            <ol class="small mb-0">
                                <li>@lang('Click "Download zimadsense.txt" to get the verification file')</li>
                                <li>@lang('Upload the file to your website root directory')</li>
                                <li>@lang('Common locations:') <code>public_html/</code>, <code>www/</code>, <code>public/</code>, or <code>htdocs/</code></li>
                                <li>@lang('The file must be accessible at the URL shown above')</li>
                                <li>@lang('The file should contain ONLY the verification token')</li>
                            </ol>
                        </div>
                    </div>
                </div>
                
                <div id="websiteDnsRecordMethod" class="verification-method-content" style="display: none;">
                    <div class="alert alert-info border">
                        <h6 class="mb-3"><i class="las la-server me-2"></i>@lang('DNS TXT Record Method')</h6>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">@lang('Add DNS TXT Record')</label>
                            <p class="small mb-2">@lang('Add the following TXT record to your domain\'s DNS settings:')</p>

                            <div class="mb-2">
                                <strong>@lang('Record Name/Host'):</strong> <code id="websiteDnsRecordName" class="text-break">_zimadsense_verification.yourdomain.com.</code>
                            </div>
                            <div class="mb-2">
                                <strong>@lang('Value/Content'):</strong> <code id="dnsVerificationToken" class="text-break text-danger fw-bold">zimadsense-verification=...</code>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">@lang('Instructions')</label>
                            <ul class="small mb-0">
                                <li>@lang('Go to your domain registrar\'s DNS settings')</li>
                                <li>@lang('Add a new TXT record with the details shown above')</li>
                                <li>@lang('DNS changes typically take 5-30 minutes, but can take up to 48 hours')</li>
                                <li>@lang('After adding the record, wait a few minutes before clicking "Verify Ownership"')</li>
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

