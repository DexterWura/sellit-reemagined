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
                    <div class="alert alert-light border">
                        <h6 class="mb-2">@lang('Step 1: Download Verification File')</h6>
                        <p class="mb-2 small">@lang('Download the verification file and upload it to your website root directory.')</p>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="downloadWebsiteTxtFile">
                            <i class="las la-download me-1"></i>@lang('Download TXT File')
                        </button>
                        <div class="mt-2">
                            <small class="text-muted">
                                <strong>@lang('File name'):</strong> <code id="websiteTxtFileName">-</code><br>
                                <strong>@lang('Upload to'):</strong> <code id="websiteTxtFileLocation">-</code><br>
                                <strong>@lang('File content'):</strong> <code id="websiteTxtFileContent" class="text-break">-</code>
                            </small>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <strong>@lang('Instructions'):</strong>
                                <ol class="small mb-0 mt-2">
                                    <li>@lang('Click the download button above to download the verification file')</li>
                                    <li>@lang('Upload the file to your website root directory (public_html, www, or public folder)')</li>
                                    <li>@lang('Make sure the file is accessible at the URL shown above')</li>
                                    <li>@lang('Click "Verify Ownership" button below to check')</li>
                                </ol>
                            </small>
                        </div>
                    </div>
                </div>
                
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

