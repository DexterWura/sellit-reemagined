@extends($activeTemplate . 'layouts.frontend')

@section('content')
@push('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('user.home') }}">@lang('Dashboard')</a>
</li>
<li class="breadcrumb-item">
    <a href="{{ route('user.verification.index') }}">@lang('Domain Verifications')</a>
</li>
<li class="breadcrumb-item active" aria-current="page">@lang('Verify: :domain', ['domain' => $verification->domain])</li>
@endpush
<section class="section bg--light">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Verification Status Card -->
            <div class="card custom--card mb-4">
                <div class="card-header bg--primary">
                    <h5 class="card-title text-white mb-0">
                        <i class="las la-shield-alt me-2"></i>@lang('Verify Domain Ownership')
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center mb-4">
                        <div class="col-md-6">
                            <h4 class="mb-1">{{ $verification->domain }}</h4>
                            @if($verification->listing)
                                <p class="text-muted mb-0">
                                    @lang('For listing'): {{ $verification->listing->title }}
                                </p>
                            @endif
                        </div>
                        <div class="col-md-6 text-md-end">
                            @switch($verification->status)
                                @case('pending')
                                    <span class="badge badge--warning badge--lg">
                                        <i class="las la-clock me-1"></i>@lang('Pending Verification')
                                    </span>
                                    @break
                                @case('verified')
                                    <span class="badge badge--success badge--lg">
                                        <i class="las la-check-circle me-1"></i>@lang('Verified')
                                    </span>
                                    @break
                                @case('failed')
                                    <span class="badge badge--danger badge--lg">
                                        <i class="las la-times-circle me-1"></i>@lang('Failed')
                                    </span>
                                    @break
                                @case('expired')
                                    <span class="badge badge--secondary badge--lg">
                                        <i class="las la-calendar-times me-1"></i>@lang('Expired')
                                    </span>
                                    @break
                            @endswitch
                        </div>
                    </div>

                    @if($verification->error_message)
                        <div class="alert alert-danger">
                            <i class="las la-exclamation-triangle me-2"></i>
                            <strong>@lang('Last Error'):</strong> {{ $verification->error_message }}
                        </div>
                    @endif

                    @if($verification->expires_at)
                        <div class="alert alert-info">
                            <i class="las la-clock me-2"></i>
                            @lang('This verification expires on'): <strong>{{ showDateTime($verification->expires_at, 'd M, Y h:i A') }}</strong>
                            @if($verification->expires_at->isPast())
                                <span class="text-danger">(@lang('Expired - Please create a new listing'))</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            @if($verification->status == 0 && $verification->isValid())
            <!-- Instructions Card -->
            <div class="card custom--card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="las la-list-ol me-2"></i>@lang('Verification Instructions') - {{ $instructions['method'] }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="verification-steps">
                        @foreach($instructions['steps'] as $step)
                            <div class="step-item mb-3 d-flex">
                                <div class="step-number me-3">
                                    <span class="badge badge--dark rounded-circle" style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                        {{ $loop->iteration }}
                                    </span>
                                </div>
                                <div class="step-content">
                                    <p class="mb-0">{!! $step !!}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($verification->verification_method == 'txt_file')
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6 class="mb-2">@lang('File Contents'):</h6>
                            <div class="d-flex align-items-center gap-2">
                                <code class="flex-grow-1 p-2 bg-white border rounded" id="tokenContent">{{ $instructions['download_content'] }}</code>
                                <button type="button" class="btn btn--sm btn--dark" onclick="copyToClipboard('tokenContent')">
                                    <i class="las la-copy"></i>
                                </button>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('user.verification.download', $verification->id) }}" 
                                   class="btn btn--primary">
                                    <i class="las la-download me-1"></i>@lang('Download Verification File')
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6 class="mb-2">@lang('DNS Record Details'):</h6>
                            <table class="table table-bordered mb-0">
                                <tr>
                                    <th style="width: 150px;">@lang('Record Type')</th>
                                    <td><code>TXT</code></td>
                                </tr>
                                <tr>
                                    <th>@lang('Name/Host')</th>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <code id="dnsName" class="flex-grow-1">{{ $instructions['record_name'] }}</code>
                                            <button type="button" class="btn btn--sm btn--dark" onclick="copyToClipboard('dnsName')">
                                                <i class="las la-copy"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>@lang('Value/Content')</th>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <code id="dnsValue" class="flex-grow-1" style="word-break: break-all;">{{ $instructions['record_value'] }}</code>
                                            <button type="button" class="btn btn--sm btn--dark" onclick="copyToClipboard('dnsValue')">
                                                <i class="las la-copy"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="las la-info-circle me-2"></i>
                                @lang('DNS changes can take up to 24-48 hours to propagate. Please wait before verifying.')
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card custom--card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <form action="{{ route('user.verification.verify', $verification->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn--success w-100 h-45">
                                    <i class="las la-check-circle me-2"></i>@lang('Verify Now')
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn--dark w-100 h-45" data-bs-toggle="modal" data-bs-target="#changeMethodModal">
                                <i class="las la-exchange-alt me-2"></i>@lang('Change Verification Method')
                            </button>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            @lang('Attempts'): {{ $verification->attempts }}
                            @if($verification->last_attempt_at)
                                | @lang('Last attempt'): {{ showDateTime($verification->last_attempt_at, 'd M, Y h:i A') }}
                            @endif
                        </small>
                    </div>
                </div>
            </div>

            <!-- Change Method Modal -->
            <div class="modal fade" id="changeMethodModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">@lang('Change Verification Method')</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('user.verification.change.method', $verification->id) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <p class="text-muted mb-3">@lang('Select a different verification method. A new token will be generated.')</p>
                                
                                <div class="form-group">
                                    <label class="form-label">@lang('Verification Method')</label>
                                    <select name="verification_method" class="form-select" required>
                                        @php
                                            $methods = \App\Models\MarketplaceSetting::getDomainVerificationMethods();
                                        @endphp
                                        @if(in_array('txt_file', $methods))
                                            <option value="txt_file" {{ $verification->verification_method == 'txt_file' ? 'selected' : '' }}>
                                                @lang('TXT File Upload') - @lang('Upload a file to your domain')
                                            </option>
                                        @endif
                                        @if(in_array('dns_record', $methods))
                                            <option value="dns_record" {{ $verification->verification_method == 'dns_record' ? 'selected' : '' }}>
                                                @lang('DNS TXT Record') - @lang('Add a DNS record')
                                            </option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Cancel')</button>
                                <button type="submit" class="btn btn--primary">@lang('Change Method')</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
</section>
@endsection

@push('script')
<script>
    function copyToClipboard(elementId) {
        const element = document.getElementById(elementId);
        const text = element.textContent || element.innerText;
        
        navigator.clipboard.writeText(text).then(() => {
            notify('success', 'Copied to clipboard!');
        }).catch(err => {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            notify('success', 'Copied to clipboard!');
        });
    }
</script>
@endpush


