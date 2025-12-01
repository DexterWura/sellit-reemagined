@extends($activeTemplate . 'layouts.frontend')
@section('content')
    @php
        $register = getContent('register.content', true);
        $policyPages = getContent('policy_pages.element', orderById: true);
    @endphp
    @if (gs('registration'))
        <div class="section ">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="login-section__content">
                            <div class="row justify-content-center">
                                <div class="col-xl-6 col-lg-5 d-none d-lg-block">
                                    <div class="text-center login-section__image">
                                        <img alt="image" class="img-fluid login-section__image-is"
                                            src="{{ frontendImage('register', @$register->data_values->image, '425x600') }}">
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-7 col-md-10 ">
                                    <div class="login-form">
                                        <div class="login-form__head mb-4">
                                            <h4 class="mt-lg-0 text-center">
                                                {{ __(@$register->data_values->heading) }}</h4>
                                            <p class="text-center section__para mx-auto">
                                                {{ __(@$register->data_values->subheading) }}
                                            </p>
                                        </div>
                                        @include($activeTemplate . 'partials.social_login')

                                        <form action="{{ route('user.register') }}" method="POST" class="verify-gcaptcha disableSubmission">
                                            @csrf
                                            <div class="row">
                                                @if (session()->get('reference') != null)
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="referenceBy" class="form-label">@lang('Reference By')</label>
                                                            <input type="text" name="referBy" id="referenceBy" class="form-control form--control"
                                                                value="{{ session()->get('reference') }}" readonly>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="form-group col-sm-6">
                                                    <label class="form-label">@lang('First Name')</label>
                                                    <input type="text" class="form-control form--control" name="firstname"
                                                        value="{{ old(' firstname') }}" required>
                                                </div>
                                                <div class="form-group col-sm-6">
                                                    <label class="form-label">@lang('Last Name')</label>
                                                    <input type="text" class="form-control form--control" name="lastname"
                                                        value="{{ old(' lastname') }}" required>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="form-label">@lang('E-Mail Address')</label>
                                                        <input type="email" class="form-control form--control checkUser" name="email"
                                                            value="{{ old('email',@request()->invite_email) }}" required @readonly(@request()->invite_email)>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label">@lang('Password')</label>
                                                        <input type="password"
                                                            class="form-control form--control @if (gs('secure_password')) secure-password @endif"
                                                            name="password" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label">@lang('Confirm Password')</label>
                                                        <input type="password" class="form-control form--control" name="password_confirmation" required>
                                                    </div>
                                                </div>

                                                <x-captcha />

                                            </div>

                                            @if (gs('agree'))
                                                @php
                                                    $policyPages = getContent('policy_pages.element', false, orderById: true);
                                                @endphp
                                                <div class="form-group">
                                                    <input type="checkbox" id="agree" @checked(old('agree')) name="agree" required>
                                                    <label for="agree">@lang('I agree with')</label> <span>
                                                        @foreach ($policyPages as $policy)
                                                            <a href="{{ route('policy.pages', $policy->slug) }}"
                                                                target="_blank">{{ __($policy->data_values->title) }}</a>
                                                            @if (!$loop->last)
                                                                ,
                                                            @endif
                                                        @endforeach
                                                    </span>
                                                </div>
                                            @endif
                                            <div class="form-group">
                                                <button type="submit" id="recaptcha" class="btn btn--base h-45 w-100">
                                                    @lang('Register')</button>
                                            </div>
                                            <p class="mb-0">@lang('Already have an account?') <a href="{{ route('user.login') }}">@lang('Login')</a></p>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        @include($activeTemplate . 'partials.registration_disabled')

    @endif


    <div class="modal fade" id="existModalCenter" tabindex="-1" role="dialog" aria-labelledby="existModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="existModalLongTitle">@lang('You are with us')</h5>
                    <span type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <h6 class="text-center">@lang('You already have an account please Login ')</h6>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark btn-sm" data-bs-dismiss="modal">@lang('Close')</button>
                    <a href="{{ route('user.login') }}" class="btn btn--base btn-sm">@lang('Login')</a>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('style')
    <style>
        .social-login-btn {
            border: 1px solid #cbc4c4;
        }
    </style>
@endpush

@if (gs('secure_password'))
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif

@push('script')
    <script>
        "use strict";
        (function($) {

            $('.checkUser').on('focusout', function(e) {
                var url = '{{ route('user.checkUser') }}';
                var value = $(this).val();
                var token = '{{ csrf_token() }}';

                var data = {
                    email: value,
                    _token: token
                }

                $.post(url, data, function(response) {
                    if (response.data != false) {
                        $('#existModalCenter').modal('show');
                    }
                });
            });
        })(jQuery);
    </script>
@endpush
