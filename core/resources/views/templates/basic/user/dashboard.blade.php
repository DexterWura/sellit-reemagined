@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    @php
        $kycContent = getContent('kyc.content', true);
    @endphp

    {{-- KYC Alerts --}}
    @if (auth()->user()->kv == Status::KYC_UNVERIFIED && auth()->user()->kyc_rejection_reason)
        <div class="row">
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
        </div>
    @elseif(auth()->user()->kv == Status::KYC_UNVERIFIED)
        <div class="row">
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
        </div>
    @elseif(auth()->user()->kv == Status::KYC_PENDING)
        <div class="row">
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
        </div>
    @endif

    {{-- Pending Escrow Actions Reminders --}}
    @if(isset($pendingActions) && count($pendingActions) > 0)
        @foreach($pendingActions as $action)
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-{{ $action['priority'] == 'high' ? 'danger' : 'warning' }} mb-0">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div class="flex-grow-1">
                                <h4 class="alert-heading text--{{ $action['priority'] == 'high' ? 'danger' : 'warning' }} m-0">
                                    @if($action['type'] == 'escrow_accept_buyer')
                                        @lang('Action Required: Accept Escrow')
                                    @elseif($action['type'] == 'escrow_accept_seller')
                                        @lang('Action Required: Accept Escrow')
                                    @elseif($action['type'] == 'escrow_payment_required')
                                        @lang('Payment Required')
                                    @elseif($action['type'] == 'milestones_pending_approval' || $action['type'] == 'milestones_pending_approval_seller')
                                        @lang('Milestones Pending Approval')
                                    @elseif($action['type'] == 'milestones_ready_payment')
                                        @lang('Milestones Ready for Payment')
                                    @else
                                        @lang('Action Required')
                                    @endif
                                </h4>
                            </div>
                        </div>
                        <hr>
                        <p class="mb-2">
                            <strong>{{ $action['listing_title'] }}</strong>
                        </p>
                        <p class="mb-0">
                            {{ $action['message'] }}
                            <a href="{{ $action['link'] }}" class="fw-bold">
                                {{ $action['linkText'] }}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    {{-- Financial Overview --}}
    <div class="row gy-4">
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="6" link="{{ route('user.deposit.index') }}" icon="las la-wallet" title="Balance" value="{{ showAmount($data['balance']) }}"
                bg="primary" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="6" link="{{ route('user.deposit.history', 'pending') }}" icon="las la-pause-circle" title="Pending Deposits"
                value="{{ $data['pendingDeposit'] }}" bg="warning" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="6" link="{{ route('user.withdraw.history', 'pending') }}" icon="las la-pause-circle" title="Pending Withdrawals"
                value="{{ $data['pendingWithdrawals'] }}" bg="danger" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="6" link="{{ route('user.transactions') }}" icon="las la-exchange-alt" title="Transactions"
                value="{{ $transactions->count() }}" bg="info" />
        </div>
    </div>

    {{-- Marketplace Statistics (Primary) --}}
    <div class="row gy-4 mt-2">
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="primary" icon="las la-store" link="{{ route('user.listing.index') }}" style="7" type="2"
                title="My Listings" value="{{ number_format($data['my_listings']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="success" icon="las la-check-circle" link="{{ route('user.listing.index', ['status' => Status::LISTING_ACTIVE]) }}" style="7" type="2"
                title="Active Listings" value="{{ number_format($data['active_listings']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="info" icon="las la-check-double" link="{{ route('user.listing.index', ['status' => Status::LISTING_SOLD]) }}" style="7" type="2"
                title="Sold Listings" value="{{ number_format($data['sold_listings']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="success" icon="las la-dollar-sign" link="{{ route('user.listing.index', ['status' => Status::LISTING_SOLD]) }}" style="7" type="2"
                title="Total Sales Value" value="{{ showAmount($data['total_sales_value']) }}" />
        </div>
    </div>

    <div class="row gy-4 mt-2">
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="primary" icon="las la-gavel" link="{{ route('user.bid.index') }}" style="7" type="2"
                title="My Bids" value="{{ number_format($data['my_bids']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="warning" icon="las la-trophy" link="{{ route('user.bid.index', ['status' => Status::BID_WINNING]) }}" style="7" type="2"
                title="Winning Bids" value="{{ number_format($data['winning_bids']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="info" icon="las la-handshake" link="{{ route('user.offer.index') }}" style="7" type="2"
                title="My Offers" value="{{ number_format($data['my_offers']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="danger" icon="las la-heart" link="{{ route('user.watchlist.index') }}" style="7" type="2"
                title="Watchlist Items" value="{{ number_format($data['watchlist_items']) }}" />
        </div>
    </div>

    <div class="row gy-4 mt-2">
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="success" icon="las la-eye" link="{{ route('user.listing.index') }}" style="7" type="2"
                title="Total Views" value="{{ number_format($data['total_listing_views']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="primary" icon="las la-file-signature" link="{{ route('user.nda.index') }}" style="7" type="2"
                title="Signed NDAs" value="{{ number_format($data['signed_ndas']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="info" icon="las la-shopping-cart" link="{{ route('user.escrow.index', 'accepted') }}" style="7" type="2"
                title="Active Escrows" value="{{ number_format($data['active_escrows']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="success" icon="las la-check-double" link="{{ route('user.escrow.index', 'completed') }}" style="7" type="2"
                title="Completed Escrows" value="{{ number_format($data['completed_escrows']) }}" />
        </div>
    </div>

    {{-- Latest Transactions --}}
    <div class="row mb-none-30 mt-30">
        <div class="col-xl-12 mb-30">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Date')</th>
                                    <th>@lang('Transaction ID')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Details')</th>
                                    <th>@lang('Balance')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $trx)
                                    <tr>
                                        <td>
                                            <span>{{ showDateTime($trx->created_at, 'd M, Y h:i A') }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $trx->trx }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold @if($trx->trx_type == '+') text--success @else text--danger @endif">
                                                {{ $trx->trx_type == '+' ? '+' : '-' }}{{ showAmount($trx->amount) }} {{ __(gs('cur_text')) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span>{{ __($trx->details) }}</span>
                                        </td>
                                        <td>
                                            <span>{{ showAmount($trx->post_balance) }} {{ __(gs('cur_text')) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
