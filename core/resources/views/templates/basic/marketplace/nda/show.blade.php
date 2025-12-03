@extends($activeTemplate . 'layouts.frontend')
@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- NDA Header -->
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <div class="d-flex align-items-center">
                            <i class="las la-file-signature la-2x me-3"></i>
                            <div>
                                <h4 class="mb-0">@lang('Non-Disclosure Agreement Required')</h4>
                                <small>@lang('You must sign an NDA to view this confidential listing')</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Listing Preview -->
                        <div class="listing-preview mb-4 p-3 bg-light rounded">
                            <div class="row">
                                <div class="col-md-4">
                                    @if($listing->primaryImage)
                                        <img src="{{ getImage(getFilePath('listing') . '/' . $listing->primaryImage->image) }}"
                                             alt="{{ $listing->title }}" class="img-fluid rounded">
                                    @else
                                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                            <i class="las la-image la-3x text-white"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-8">
                                    <h5 class="mb-2">{{ $listing->title }}</h5>
                                    <p class="text-muted mb-2">{{ Str::limit($listing->tagline ?: 'No description available', 100) }}</p>
                                    <div class="row text-sm">
                                        <div class="col-6">
                                            <span class="text-muted">@lang('Business Type'):</span><br>
                                            <strong>{{ ucfirst(str_replace('_', ' ', $listing->business_type)) }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted">@lang('Seller'):</span><br>
                                            <strong>{{ $listing->seller->username }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- NDA Content -->
                        <div class="nda-content mb-4">
                            <h5 class="mb-3">@lang('Non-Disclosure Agreement')</h5>

                            <div class="alert alert-info">
                                <i class="las la-info-circle"></i>
                                @lang('This listing contains confidential information. By signing this NDA, you agree to keep all information confidential and not share it with third parties.')
                            </div>

                            <div class="nda-terms p-3 border rounded bg-light">
                                <h6>@lang('Terms of Agreement')</h6>
                                <ol class="mb-0">
                                    <li>@lang('I agree to keep all information regarding this business listing confidential.')</li>
                                    <li>@lang('I will not share, disclose, or distribute any confidential information to third parties without written permission from the seller.')</li>
                                    <li>@lang('I understand that this NDA is legally binding and violations may result in legal action.')</li>
                                    <li>@lang('This agreement remains in effect for 1 year from the date of signing.')</li>
                                    <li>@lang('I acknowledge that I am signing this agreement electronically and it has the same legal effect as a handwritten signature.')</li>
                                </ol>
                            </div>
                        </div>

                        <!-- NDA Signing Form -->
                        @auth
                        <form action="{{ route('marketplace.nda.sign', $listing->id) }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="signature" class="form-label">@lang('Full Name (Digital Signature)') *</label>
                                        <input type="text" class="form-control" id="signature" name="signature"
                                               value="{{ auth()->user()->fullname }}" required>
                                        <small class="form-text text-muted">@lang('Enter your full legal name as your digital signature')</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Date')</label>
                                        <input type="text" class="form-control" value="{{ now()->format('Y-m-d') }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="agree_terms" name="agree_terms" value="1" required>
                                <label class="form-check-label" for="agree_terms">
                                    @lang('I have read and agree to the terms of this Non-Disclosure Agreement') *
                                </label>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="las la-signature"></i> @lang('Sign NDA & View Listing')
                                </button>
                                <a href="{{ route('marketplace.index') }}" class="btn btn-outline-secondary">
                                    <i class="las la-arrow-left"></i> @lang('Back to Marketplace')
                                </a>
                            </div>
                        </form>
                        @else
                        <div class="text-center">
                            <div class="alert alert-warning">
                                <i class="las la-exclamation-triangle"></i>
                                @lang('You must be logged in to sign an NDA.')
                            </div>
                            <a href="{{ route('user.login') }}" class="btn btn-primary">
                                <i class="las la-sign-in-alt"></i> @lang('Login to Continue')
                            </a>
                        </div>
                        @endauth
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title">@lang('What happens after signing?')</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="las la-check-circle text-success me-2"></i>
                                @lang('You will be able to view all listing details and contact the seller')
                            </li>
                            <li class="mb-2">
                                <i class="las la-check-circle text-success me-2"></i>
                                @lang('Your signed NDA will be legally binding for 1 year')
                            </li>
                            <li class="mb-2">
                                <i class="las la-check-circle text-success me-2"></i>
                                @lang('The seller will be notified of your NDA signature')
                            </li>
                            <li class="mb-0">
                                <i class="las la-info-circle text-info me-2"></i>
                                @lang('If you have questions, contact our support team')
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('script')
<script>
(function($) {
    "use strict";

    // Auto-fill signature with user's name
    $(document).ready(function() {
        $('#signature').on('focus', function() {
            if (!$(this).val()) {
                $(this).val('{{ auth()->check() ? auth()->user()->fullname : "" }}');
            }
        });
    });

})(jQuery);
</script>
@endpush
