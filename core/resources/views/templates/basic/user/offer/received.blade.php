@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="{{ route('user.offer.index') }}" class="btn btn--base btn-sm">
                    <i class="las la-paper-plane"></i> @lang('Offers Made')
                </a>
            </div>
            
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table--light style--two">
                    <thead>
                        <tr>
                            <th>@lang('Listing')</th>
                            <th>@lang('Buyer')</th>
                            <th>@lang('Offer')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Date')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($offers as $offer)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($offer->listing->images->first())
                                            <img src="{{ getImage(getFilePath('listing') . '/' . $offer->listing->images->first()->image) }}" 
                                                 alt="" class="me-3" style="width: 60px; height: 45px; object-fit: cover; border-radius: 5px;">
                                        @endif
                                        <div>
                                            <a href="{{ route('marketplace.listing.show', $offer->listing->slug) }}">
                                                {{ Str::limit($offer->listing->title, 40) }}
                                            </a>
                                            <br>
                                            <small class="text-muted">@lang('Asking'): {{ showAmount($offer->listing->asking_price) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('marketplace.seller', $offer->buyer->username ?? '') }}">
                                        {{ $offer->buyer->username ?? 'N/A' }}
                                    </a>
                                </td>
                                <td>
                                    <strong>{{ showAmount($offer->amount) }}</strong>
                                    @if($offer->message)
                                        <br>
                                        <small class="text-muted" title="{{ $offer->message }}">
                                            <i class="las la-comment"></i> {{ Str::limit($offer->message, 30) }}
                                        </small>
                                    @endif
                                </td>
                                <td>@php echo $offer->offerStatus @endphp</td>
                                <td>{{ $offer->created_at->format('M d, Y') }}</td>
                                <td>
                                    @if($offer->status == \App\Constants\Status::OFFER_PENDING)
                                        <div class="btn-group-vertical btn-group-sm">
                                            <form action="{{ route('user.offer.accept', $offer->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success w-100 mb-1" 
                                                        onclick="return confirm('@lang('Accept this offer for') {{ showAmount($offer->amount) }}?')">
                                                    <i class="las la-check"></i> @lang('Accept')
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-info w-100 mb-1 counterBtn"
                                                    data-id="{{ $offer->id }}" data-amount="{{ $offer->amount }}">
                                                <i class="las la-exchange-alt"></i> @lang('Counter')
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger w-100 rejectBtn"
                                                    data-id="{{ $offer->id }}">
                                                <i class="las la-times"></i> @lang('Reject')
                                            </button>
                                        </div>
                                    @elseif($offer->status == \App\Constants\Status::OFFER_ACCEPTED && $offer->escrow_id)
                                        <a href="{{ route('user.escrow.details', $offer->escrow_id) }}" class="btn btn-sm btn--base">
                                            <i class="las la-hand-holding-usd"></i> @lang('View Escrow')
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <img src="{{ asset('assets/images/empty_list.png') }}" alt="" style="max-width: 120px;">
                                    <p class="mt-2 text-muted">@lang('No offers received yet')</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
            
            @if($offers->hasPages())
                <div class="mt-4">{{ $offers->links() }}</div>
            @endif
        </div>
    </div>
    </div>
</section>

<!-- Counter Offer Modal -->
<div class="modal fade" id="counterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Counter Offer')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" id="counterForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">@lang('Original Offer')</label>
                        <input type="text" class="form-control" id="originalAmount" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">@lang('Your Counter Offer') <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">{{ gs()->cur_sym }}</span>
                            <input type="number" name="counter_amount" class="form-control" step="0.01" min="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">@lang('Message')</label>
                        <textarea name="counter_message" class="form-control" rows="3" 
                                  placeholder="@lang('Explain your counter offer...')"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--base">@lang('Send Counter Offer')</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Reject Offer')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" id="rejectForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">@lang('Reason') (@lang('optional'))</label>
                        <textarea name="reason" class="form-control" rows="3" 
                                  placeholder="@lang('Why are you rejecting this offer?')"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn-danger">@lang('Reject Offer')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        $('.counterBtn').on('click', function() {
            var id = $(this).data('id');
            var amount = $(this).data('amount');
            var url = "{{ route('user.offer.counter', ':id') }}";
            url = url.replace(':id', id);
            
            $('#counterForm').attr('action', url);
            $('#originalAmount').val('{{ gs()->cur_sym }}' + parseFloat(amount).toFixed(2));
            $('#counterModal').modal('show');
        });
        
        $('.rejectBtn').on('click', function() {
            var id = $(this).data('id');
            var url = "{{ route('user.offer.reject', ':id') }}";
            url = url.replace(':id', id);
            
            $('#rejectForm').attr('action', url);
            $('#rejectModal').modal('show');
        });
    });
</script>
@endpush

