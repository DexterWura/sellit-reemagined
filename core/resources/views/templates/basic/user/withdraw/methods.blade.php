@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card b-radius--10">
                <div class="card-body">
                    <form action="{{ route('user.withdraw.money') }}" method="post" class="withdraw-form">
                        @csrf
                        <div class="gateway-card">
                            <div class="row justify-content-center gy-sm-4 gy-3">
                                <div class="col-12">
                                    <h5 class="payment-card-title">
                                        @lang('Withdraw')
                                        <span id="selectedGatewayName" class="text--base">
                                            @if($withdrawMethod->first())
                                                @lang('with') {{ __($withdrawMethod->first()->name) }}
                                            @endif
                                        </span>
                                    </h5>
                                </div>
                                <div class="col-lg-6">
                                    <div class="payment-system-list is-scrollable gateway-option-list">
                                        @foreach ($withdrawMethod as $data)
                                            <label for="{{ titleToKey($data->name) }}"
                                                class="payment-item @if ($loop->index > 4) d-none @endif gateway-option @if (old('method_code') == $data->id || ($loop->first && !old('method_code'))) active @endif">
                                                <div class="payment-item__info">
                                                    <span class="payment-item__check"></span>
                                                    <span class="payment-item__name">{{ __($data->name) }}</span>
                                                </div>
                                                <div class="payment-item__thumb">
                                                    <img class="payment-item__thumb-img"
                                                        src="{{ getImage(getFilePath('withdrawMethod') . '/' . $data->image) }}" alt="@lang('payment-thumb')">
                                                </div>
                                                <input class="payment-item__radio gateway-input" id="{{ titleToKey($data->name) }}" hidden
                                                    data-gateway='@json($data)' type="radio" name="method_code"
                                                    value="{{ $data->id }}"
                                                    @if (old('method_code')) @checked(old('method_code') == $data->id) @else @checked($loop->first) @endif
                                                    data-min-amount="{{ showAmount($data->min_limit) }}"
                                                    data-max-amount="{{ showAmount($data->max_limit) }}">
                                            </label>
                                        @endforeach
                                        @if ($withdrawMethod->count() > 4)
                                            <button type="button" class="payment-item__btn more-gateway-option">
                                                <p class="payment-item__btn-text">@lang('Show All Payment Options')</p>
                                                <span class="payment-item__btn__icon"><i class="fas fa-chevron-down"></i></i></span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="payment-system-list p-3">
                                        <div class="deposit-info">
                                            <div class="deposit-info__title">
                                                <p class="text mb-0">@lang('Amount')</p>
                                            </div>
                                            <div class="deposit-info__input">
                                                <div class="deposit-info__input-group input-group">
                                                    <span class="deposit-info__input-group-text">{{ gs('cur_sym') }}</span>
                                                    <input type="text" class="form-control form--control amount" name="amount"
                                                        placeholder="@lang('00.00')" value="{{ old('amount') }}" autocomplete="off">
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="deposit-info">
                                            <div class="deposit-info__title">
                                                <p class="text has-icon"> @lang('Limit')</p>
                                            </div>
                                            <div class="deposit-info__input">
                                                <p class="text"><span class="gateway-limit">@lang('0.00')</span> </p>
                                            </div>
                                        </div>
                                        <div class="deposit-info">
                                            <div class="deposit-info__title">
                                                <p class="text has-icon">@lang('Processing Charge')
                                                    <span data-bs-toggle="tooltip" title="@lang('Processing charge for withdraw method')" class="proccessing-fee-info"><i
                                                            class="las la-info-circle"></i> </span>
                                                </p>
                                            </div>
                                            <div class="deposit-info__input">
                                                <p class="text">{{ gs('cur_sym') }}<span class="processing-fee">@lang('0.00')</span>
                                                    {{ __(gs('cur_text')) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="deposit-info total-amount pt-3">
                                            <div class="deposit-info__title">
                                                <p class="text">@lang('Receivable')</p>
                                            </div>
                                            <div class="deposit-info__input">
                                                <p class="text">{{ gs('cur_sym') }}<span class="final-amount">@lang('0.00')</span>
                                                    {{ __(gs('cur_text')) }}</p>
                                            </div>
                                        </div>

                                        <div class="deposit-info gateway-conversion d-none total-amount pt-2">
                                            <div class="deposit-info__title">
                                                <p class="text">@lang('Conversion')
                                                </p>
                                            </div>
                                            <div class="deposit-info__input">
                                                <p class="text"></p>
                                            </div>
                                        </div>
                                        <div class="deposit-info conversion-currency d-none total-amount pt-2">
                                            <div class="deposit-info__title">
                                                <p class="text">
                                                    @lang('In') <span class="gateway-currency"></span>
                                                </p>
                                            </div>
                                            <div class="deposit-info__input">
                                                <p class="text">
                                                    <span class="in-currency"></span>
                                                </p>
                                            </div>
                                        </div>
                                        @if($withdrawMethod->count() > 0)
                                            <button type="submit" class="btn btn--base w-100 mt-3 fw-bold" id="confirmWithdrawBtn" disabled>
                                                <i class="las la-check-circle"></i> @lang('Confirm Withdraw')
                                            </button>
                                        @else
                                            <button type="button" class="btn btn--base w-100 mt-3 fw-bold" disabled>
                                                <i class="las la-ban"></i> @lang('No Methods Available')
                                            </button>
                                        @endif
                                        <div class="info-text pt-3">
                                            <p class="text">@lang('Safely withdraw your funds using our highly secure process and various withdrawal method')</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
@endpush

@push('style')
<style>
    .payment-item {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .payment-item:hover {
        background-color: rgba(var(--base-rgb, 70, 52, 255), 0.05);
    }
    
    .payment-item.active {
        background-color: rgba(var(--base-rgb, 70, 52, 255), 0.1) !important;
        border-left: 3px solid rgb(var(--base)) !important;
        border-color: rgb(var(--base)) !important;
        box-shadow: 0 2px 8px rgba(var(--base-rgb, 70, 52, 255), 0.15);
    }
    
    .payment-item.active .payment-item__check {
        border: 3px solid rgb(var(--base)) !important;
        background-color: rgb(var(--base)) !important;
        position: relative;
    }
    
    .payment-item.active .payment-item__check::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 6px;
        height: 6px;
        background-color: #fff;
        border-radius: 50%;
    }
    
    .payment-item.active .payment-item__name {
        font-weight: 600;
        color: rgb(var(--base));
    }
    
    .withdraw-form button[type=submit].btn--base {
        background: #{{ gs('base_color', '4bea76') }} !important;
        color: #fff !important;
        font-weight: 600 !important;
        border: none !important;
    }
    
    .withdraw-form button[type=submit].btn--base:hover:not(:disabled) {
        background: #{{ gs('base_color', '4bea76') }} !important;
        opacity: 0.9;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(75, 234, 118, 0.3);
    }
</style>
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@endpush

@push('script')
    <script>
        "use strict";
        (function($) {

            var amount = parseFloat($('.amount').val() || 0);
            var gateway, minAmount, maxAmount;


            $('.amount').on('input', function(e) {
                amount = parseFloat($(this).val());
                if (!amount) {
                    amount = 0;
                }
                calculation();
            });

            $('.gateway-input').on('change', function(e) {
                // Remove active class from all payment items
                $('.payment-item').removeClass('active');
                // Add active class to selected payment item
                $(this).closest('.payment-item').addClass('active');
                
                // Update title with selected gateway name
                const gatewayName = $(this).closest('.payment-item').find('.payment-item__name').text().trim();
                $('#selectedGatewayName').text('@lang("with") ' + gatewayName);
                
                gatewayChange();
            });
            
            // Initialize active state on page load
            $('.gateway-input:checked').closest('.payment-item').addClass('active');

            function gatewayChange() {
                let gatewayElement = $('.gateway-input:checked');
                
                if (!gatewayElement.length) {
                    $(".withdraw-form button[type=submit]").prop('disabled', true).addClass('disabled');
                    return;
                }
                
                let methodCode = gatewayElement.val();

                gateway = gatewayElement.data('gateway');
                
                if (!gateway) {
                    $(".withdraw-form button[type=submit]").prop('disabled', true).addClass('disabled');
                    return;
                }
                
                // Update title with selected gateway name
                const gatewayName = gatewayElement.closest('.payment-item').find('.payment-item__name').text().trim();
                $('#selectedGatewayName').text('@lang("with") ' + gatewayName);
                
                // Get min/max from gateway object directly, not from data attributes
                minAmount = parseFloat(gateway.min_limit || 0);
                maxAmount = parseFloat(gateway.max_limit || 999999999);

                let processingFeeInfo =
                    `${parseFloat(gateway.percent_charge || 0).toFixed(2)}% with ${parseFloat(gateway.fixed_charge || 0).toFixed(2)} {{ __(gs('cur_text')) }} charge for processing fees`
                $(".proccessing-fee-info").attr("data-bs-original-title", processingFeeInfo);

                calculation();
            }

            // Call gatewayChange on page load to initialize
            if ($('.gateway-input:checked').length) {
                gatewayChange();
            } else {
                $(".withdraw-form button[type=submit]").prop('disabled', true).addClass('disabled');
            }

            $(".more-gateway-option").on("click", function(e) {
                let paymentList = $(".gateway-option-list");
                paymentList.find(".gateway-option").removeClass("d-none");
                $(this).addClass('d-none');
                paymentList.animate({
                    scrollTop: (paymentList.height() - 60)
                }, 'slow');
            });

            function calculation() {
                if (!gateway) {
                    $(".withdraw-form button[type=submit]").prop('disabled', true).addClass('disabled');
                    return;
                }
                
                $(".gateway-limit").text(minAmount + " - " + maxAmount);
                let percentCharge = 0;
                let fixedCharge = 0;
                let totalPercentCharge = 0;

                if (amount) {
                    percentCharge = parseFloat(gateway.percent_charge || 0);
                    fixedCharge = parseFloat(gateway.fixed_charge || 0);
                    totalPercentCharge = parseFloat(amount / 100 * percentCharge);
                }

                let totalCharge = parseFloat(totalPercentCharge + fixedCharge);
                let totalAmount = parseFloat((amount || 0) - totalPercentCharge - fixedCharge);

                $(".final-amount").text(totalAmount.toFixed(2));
                $(".processing-fee").text(totalCharge.toFixed(2));
                
                if (gateway.currency) {
                    $("input[name=currency]").val(gateway.currency);
                    $(".gateway-currency").text(gateway.currency);
                }

                // Enable button if amount is within limits
                let minLimit = parseFloat(gateway.min_limit || 0);
                let maxLimit = parseFloat(gateway.max_limit || 999999999);
                
                // Check if amount is valid and within limits
                if (amount && !isNaN(amount) && amount > 0 && amount >= minLimit && amount <= maxLimit) {
                    $(".withdraw-form button[type=submit]").prop('disabled', false).removeClass('disabled');
                } else {
                    $(".withdraw-form button[type=submit]").prop('disabled', true).addClass('disabled');
                }

                if (gateway.currency != "{{ gs('cur_text') }}") {
                    $('.withdraw-form').addClass('adjust-height')
                    $(".gateway-conversion, .conversion-currency").removeClass('d-none');
                    $(".gateway-conversion").find('.deposit-info__input .text').html(
                        `1 {{ __(gs('cur_text')) }} = <span class="rate">${parseFloat(gateway.rate).toFixed(2)}</span>  <span class="method_currency">${gateway.currency}</span>`
                    );
                    $('.in-currency').text(parseFloat(totalAmount * gateway.rate).toFixed(2))
                } else {
                    $(".gateway-conversion, .conversion-currency").addClass('d-none');
                    $('.withdraw-form').removeClass('adjust-height')
                }
            }

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });


            $('.gateway-input').change();
        })(jQuery);
    </script>
@endpush
