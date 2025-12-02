@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="section bg--light">
        <div class="container">
            <div class="row gy-4">
                @if($escrow->listing)
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1"><a href="{{ route('marketplace.listing.show', $escrow->listing->slug) }}" class="text-decoration-none">{{ $escrow->listing->title }}</a></h5>
                                    <p class="text-muted mb-0">@lang('Listing #'): {{ $escrow->listing->listing_number }}</p>
                                </div>
                                <a href="{{ route('marketplace.listing.show', $escrow->listing->slug) }}" class="btn btn--base btn-sm">
                                    <i class="las la-external-link-alt"></i> @lang('View Listing')
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="col-md-12">
                    @if ($escrow->status == Status::ESCROW_ACCEPTED && $escrow->restAmount() && $escrow->buyer_id == auth()->id())
                        <div class="text-end mb-4">
                            <button class="btn btn--base btn-sm" data-bs-toggle="modal" data-bs-target="#newModal">@lang('Create Milestone')</button>
                        </div>
                    @endif

                    <table class="table custom--table table-responsive--md">
                        <thead>
                            <tr>
                                <th>@lang('Date')</th>
                                <th>@lang('Note')</th>
                                <th>@lang('Amount')</th>
                                <th>@lang('Payment Status')</th>

                                @if ($escrow->buyer_id == auth()->id() && $escrow->restAmount())
                                    <th>@lang('Action')</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($milestones as $milestone)
                                <tr>
                                    <td>{{ showDateTime($milestone->created_at, 'Y-m-d') }}</td>

                                    <td>{{ $milestone->note }}</td>

                                    <td>{{ showAmount($milestone->amount) }}</td>

                                    <td>
                                        @if ($milestone->payment_status == Status::MILESTONE_FUNDED)
                                            <span class="badge badge--success">@lang('Funded')</span>
                                        @else
                                            @if ($milestone->deposit && $milestone->deposit->status == Status::PAYMENT_PENDING)
                                                <span class="badge badge--warning">@lang('Payment Pending')</span>
                                            @else
                                                <span class="badge badge--danger">@lang('Unfunded')</span>
                                            @endif
                                        @endif
                                    </td>

                                    @if ($escrow->buyer_id == auth()->id() && $escrow->restAmount())
                                        <td>
                                            <button class="btn btn--primary btn-sm payBtn" @disabled($milestone->payment_status == Status::MILESTONE_FUNDED || optional($milestone->deposit)->status == Status::PAYMENT_PENDING) data-id="{{ $milestone->id }}">
                                                @lang('Pay Now')
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center">{{ __($emptyMessage) }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination removed - milestones are not paginated --}}
            </div>
        </div>
    </section>

    <div class="modal custom--modal fade " id="newModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('New Milestone')</h5>
                    <button role="button" class="close"><i class="las la-times" data-bs-dismiss="modal"></i></button>
                </div>
                <form action="{{ route('user.escrow.milestone.create', $escrow->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">@lang('Note')</label>
                            <input type="text" name="note" placeholder="@lang('Enter note')" class="form-control form--control" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">@lang('Rest Amount')</label>
                            <input type="text" class="form-control form--control" value="{{ $restAmount }}" readonly>
                        </div>

                        <div class="form-group">
                            <label class="form-label">@lang('Amount')</label>
                            <div class="input-group">
                                <input type="number" step="any" class="form-control form--control" name="amount" required>
                                <span class="input-group-text ">{{ __(gs('cur_text')) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--base w-100 h-45">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade " id="payModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Pay Milestone')</h5>
                    <button role="button" class="close"><i class="las la-times" data-bs-dismiss="modal"></i></button>
                </div>

                <form action="" method="POST">
                    @csrf
                    <div class="modal-body ">
                        <div class="form-group select2-parent">
                            <label class="d-block mb-2 sm-text">@lang('Select Payment Type')</label>
                            <select name="pay_via" class="form-select form--select select2-basic" data-minimum-results-for-search="-1" required>
                                <option value="1">@lang('Wallet') - {{ showAmount(auth()->user()->balance) }}
                                </option>
                                <option value="2">@lang('Direct Payment')</option>
                            </select>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--base h-45 w-100">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@endpush


@push('script')
    <script>
        (function($) {
            "use strict"

            $('.payBtn').on('click', function() {
                var modal = $('#payModal');
                modal.find('form')[0].action = `{{ route('user.escrow.milestone.pay', '') }}/${$(this).data('id')}`;
                modal.modal('show');
            })
        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        span.select2.select2-container{
            width: 100% !important;
        }
    </style>
@endpush
