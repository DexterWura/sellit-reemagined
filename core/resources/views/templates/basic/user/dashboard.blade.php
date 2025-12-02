@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="section bg--light">
        <div class="container">
            <div class="row gy-4">
                @php
                    $kycContent = getContent('kyc.content', true);
                @endphp
                <div class="notice"></div>
                @if (auth()->user()->kv == Status::KYC_UNVERIFIED && auth()->user()->kyc_rejection_reason)
                    <div class="col-12">
                        <div class="alert alert-danger mb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="alert-heading text--danger m-0">@lang('KYC Verification Required')</h4>
                                <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#kycRejectionReason">
                                    @lang('Show Reason')
                                </button>
                            </div>
                            <hr>
                            <p class="mb-0">
                                {{ __(@$kycContent->data_values->reject) }}
                                <a href="{{ route('user.kyc.form') }}">
                                    @lang('Click Here to Re-submit Documents')
                                </a>
                            </p>
                        </div>
                    </div>
                @elseif(auth()->user()->kv == Status::KYC_UNVERIFIED)
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            <h4 class="alert-heading text--danger">@lang('KYC Verification Required')</h4>
                            <hr>
                            <p class="mb-0">
                                {{ __(@$kycContent->data_values->required) }}
                                <a href="{{ route('user.kyc.form') }}">
                                    @lang('Click Here to Verify')
                                </a>
                            </p>
                        </div>
                    </div>
                @elseif(auth()->user()->kv == Status::KYC_PENDING)
                    <div class="col-12">
                        <div class="alert alert-warning mb-0">
                            <h4 class="alert-heading text--warning">@lang('KYC Verification Pending')</h4>
                            <hr>
                            <p class="mb-0">
                                {{ __(@$kycContent->data_values->pending) }}
                                <a href="{{ route('user.kyc.data') }}">@lang('See KYC Data')</a>
                            </p>
                        </div>
                    </div>
                @endif


                <div class="col-lg-8 col-xl-9">


                    <div class="d-flex flex-wrap gap-4">

                        <div class="dash-card">
                            <div class="dash-card__header">
                                <h6 class="dash-card__value">{{ showAmount($data['balance']) }} </h6>
                                <div class="dash-card__icon icon icon--circle icon--md"><i class="la la-wallet"></i></div>
                            </div>
                            <div class="dash-card__body">
                                <h5 class="dash-card__title">@lang('Balance')</h5>
                            </div>
                        </div>

                        <div class="dash-card">
                            <div class="dash-card__header">
                                <h6 class="dash-card__value">{{ $data['pendingDeposit'] }}</h6>
                                <div class="dash-card__icon icon icon--circle icon--md"><i class="la la-pause-circle"></i></div>
                            </div>
                            <div class="dash-card__body">
                                <h5 class="dash-card__title">@lang('Pending Deposits')</h5>
                                <a href="{{ route('user.deposit.history', 'pending') }}">@lang('View All')</a>
                            </div>
                        </div>

                        <div class="dash-card">
                            <div class="dash-card__header">
                                <h6 class="dash-card__value">{{ $data['pendingWithdrawals'] }}</h6>
                                <div class="dash-card__icon icon icon--circle icon--md"><i class="fa fa-pause-circle"></i></div>
                            </div>
                            <div class="dash-card__body">
                                <h5 class="dash-card__title">@lang('Pending Withdrawals')</h5>
                                <a href="{{ route('user.withdraw.history', 'pending') }}">@lang('View All')</a>
                            </div>
                        </div>

                        {{-- Marketplace Statistics (Primary) --}}
                        <div class="dash-card">
                            <div class="dash-card__header">
                                <h6 class="dash-card__value">{{ $data['my_listings'] }}</h6>
                                <div class="dash-card__icon icon icon--circle icon--md">
                                    <i class="las la-store"></i>
                                </div>
                            </div>
                            <div class="dash-card__body">
                                <h5 class="dash-card__title">@lang('My Listings')</h5>
                                <a href="{{ route('user.listing.index') }}">@lang('View All')</a>
                            </div>
                        </div>

                        <div class="dash-card">
                            <div class="dash-card__header">
                                <h6 class="dash-card__value">{{ $data['active_listings'] }}</h6>
                                <div class="dash-card__icon icon icon--circle icon--md">
                                    <i class="las la-check-circle"></i>
                                </div>
                            </div>
                            <div class="dash-card__body">
                                <h5 class="dash-card__title">@lang('Active Listings')</h5>
                                <a href="{{ route('user.listing.index', ['status' => Status::LISTING_ACTIVE]) }}">@lang('View All')</a>
                            </div>
                        </div>

                        <div class="dash-card">
                            <div class="dash-card__header">
                                <h6 class="dash-card__value">{{ $data['sold_listings'] }}</h6>
                                <div class="dash-card__icon icon icon--circle icon--md">
                                    <i class="las la-check-double"></i>
                                </div>
                            </div>
                            <div class="dash-card__body">
                                <h5 class="dash-card__title">@lang('Sold Listings')</h5>
                                <a href="{{ route('user.listing.index', ['status' => Status::LISTING_SOLD]) }}">@lang('View All')</a>
                            </div>
                        </div>

                        <div class="dash-card">
                            <div class="dash-card__header">
                                <h6 class="dash-card__value">{{ showAmount($data['total_sales_value']) }}</h6>
                                <div class="dash-card__icon icon icon--circle icon--md">
                                    <i class="las la-dollar-sign"></i>
                                </div>
                            </div>
                            <div class="dash-card__body">
                                <h5 class="dash-card__title">@lang('Total Sales Value')</h5>
                                <a href="{{ route('user.listing.index', ['status' => Status::LISTING_SOLD]) }}">@lang('View Details')</a>
                            </div>
                        </div>

                        <div class="dash-card">
                            <div class="dash-card__header">
                                <h6 class="dash-card__value">{{ $data['my_bids'] }}</h6>
                                <div class="dash-card__icon icon icon--circle icon--md">
                                    <i class="las la-gavel"></i>
                                </div>
                            </div>
                            <div class="dash-card__body">
                                <h5 class="dash-card__title">@lang('My Bids')</h5>
                                <a href="{{ route('user.bid.index') }}">@lang('View All')</a>
                            </div>
                        </div>

                        <div class="dash-card">
                            <div class="dash-card__header">
                                <h6 class="dash-card__value">{{ $data['winning_bids'] }}</h6>
                                <div class="dash-card__icon icon icon--circle icon--md">
                                    <i class="las la-trophy"></i>
                                </div>
                            </div>
                            <div class="dash-card__body">
                                <h5 class="dash-card__title">@lang('Winning Bids')</h5>
                                <a href="{{ route('user.bid.index', ['status' => Status::BID_WINNING]) }}">@lang('View All')</a>
                            </div>
                        </div>

                        <div class="dash-card">
                            <div class="dash-card__header">
                                <h6 class="dash-card__value">{{ $data['my_offers'] }}</h6>
                                <div class="dash-card__icon icon icon--circle icon--md">
                                    <i class="las la-handshake"></i>
                                </div>
                            </div>
                            <div class="dash-card__body">
                                <h5 class="dash-card__title">@lang('My Offers')</h5>
                                <a href="{{ route('user.offer.index') }}">@lang('View All')</a>
                            </div>
                        </div>

                        <div class="dash-card">
                            <div class="dash-card__header">
                                <h6 class="dash-card__value">{{ $data['watchlist_items'] }}</h6>
                                <div class="dash-card__icon icon icon--circle icon--md">
                                    <i class="las la-heart"></i>
                                </div>
                            </div>
                            <div class="dash-card__body">
                                <h5 class="dash-card__title">@lang('Watchlist Items')</h5>
                                <a href="{{ route('user.watchlist.index') }}">@lang('View All')</a>
                            </div>
                        </div>

                        <div class="dash-card">
                            <div class="dash-card__header">
                                <h6 class="dash-card__value">{{ number_format($data['total_listing_views']) }}</h6>
                                <div class="dash-card__icon icon icon--circle icon--md">
                                    <i class="las la-eye"></i>
                                </div>
                            </div>
                            <div class="dash-card__body">
                                <h5 class="dash-card__title">@lang('Total Views')</h5>
                                <a href="{{ route('user.listing.index') }}">@lang('View Details')</a>
                            </div>
                        </div>

                        {{-- Escrow (Secondary) --}}
                        <div class="dash-card">
                            <div class="dash-card__header">
                                <h6 class="dash-card__value">{{ $data['active_escrows'] }}</h6>
                                <div class="dash-card__icon icon icon--circle icon--md">
                                    <i class="las la-handshake"></i>
                                </div>
                            </div>
                            <div class="dash-card__body">
                                <h5 class="dash-card__title">@lang('Active Escrows')</h5>
                                <a href="{{ route('user.escrow.index', 'accepted') }}">@lang('View All')</a>
                            </div>
                        </div>

                        <div class="dash-card">
                            <div class="dash-card__header">
                                <h6 class="dash-card__value">{{ $data['completed_escrows'] }}</h6>
                                <div class="dash-card__icon icon icon--circle icon--md">
                                    <i class="las la-check-double"></i>
                                </div>
                            </div>
                            <div class="dash-card__body">
                                <h5 class="dash-card__title">@lang('Completed Escrows')</h5>
                                <a href="{{ route('user.escrow.index', 'completed') }}">@lang('View All')</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-xl-3">
                    <div class="transaction--card">
                        <div class="body">
                            <h6 class="title">@lang('Latest Transactions')</h6>
                            <div class="list-group list-group-flush">
                                @forelse($transactions as $trx)
                                    <li class="list-group-item">
                                        @if ($trx->trx_type == '+')
                                            <span class="d-block fw-md text--success">+{{ showAmount($trx->amount) }}</span>
                                        @else
                                            <span class="d-block fw-md text--danger">-{{ showAmount($trx->amount) }}</span>
                                        @endif
                                        <a href="{{ route('user.transactions') }}?search={{ $trx->trx }}">
                                            <small>
                                                {{ __($trx->details) }}
                                            </small>
                                        </a>
                                    </li>
                                @empty
                                    <li class="list-group-item">@lang('No transaction yet')</li>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </section>

    @if (auth()->user()->kv == Status::KYC_UNVERIFIED && auth()->user()->kyc_rejection_reason)
        <div class="modal fade custom--modal" id="kycRejectionReason">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="existModalLongTitle">@lang('KYC Document Rejection Reason')</h5>
                        <span type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </span>
                    </div>
                    <div class="modal-body">
                        <p class="py-3">{{ auth()->user()->kyc_rejection_reason }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('style')
    <style>
        a {
            color: #68a3f9;
            text-decoration: none;
        }

        .transaction--card {
            background-color: #fff;
            border-radius: 5px;
            border: 1px solid #f3f3f3;
            box-shadow: 0 0 5px 10px hsl(var(--black)/.01)
        }

        .transaction--card .title {
            padding: 1rem;
            border-bottom: 1px solid #f3f3f3;
        }

        .transaction--card .list-group-item {
            border-bottom: 1px solid hsl(var(--border)/.3);
        }

        .transaction--card .list-group-item:last-child {
            border-radius: 0 0 5px 5px;
        }
    </style>
@endpush
