@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Listing')</th>
                                    <th>@lang('Seller')</th>
                                    <th>@lang('Type')</th>
                                    <th>@lang('Price')</th>
                                    <th>@lang('Stats')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($listings as $listing)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($listing->images->first())
                                                    <img src="{{ getImage(getFilePath('listing') . '/' . $listing->images->first()->image) }}" 
                                                         alt="" class="me-3" style="width: 60px; height: 45px; object-fit: cover; border-radius: 5px;">
                                                @endif
                                                <div>
                                                    <span class="fw-bold">{{ Str::limit($listing->title, 40) }}</span>
                                                    <br>
                                                    <small class="text-muted">{{ $listing->listing_number }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($listing->user)
                                                <a href="{{ route('admin.users.detail', $listing->user->id) }}">
                                                    {{ $listing->user->username }}
                                                </a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge--secondary">
                                                {{ ucfirst(str_replace('_', ' ', $listing->business_type)) }}
                                            </span>
                                            <br>
                                            <small>{{ $listing->sale_type === 'auction' ? 'Auction' : 'Fixed Price' }}</small>
                                        </td>
                                        <td>
                                            @if($listing->sale_type === 'auction')
                                                {{ showAmount($listing->current_bid ?: $listing->starting_bid) }}
                                                <br>
                                                <small>{{ $listing->total_bids }} @lang('bids')</small>
                                            @else
                                                {{ showAmount($listing->asking_price) }}
                                            @endif
                                        </td>
                                        <td>
                                            <small>
                                                <i class="las la-eye"></i> {{ $listing->view_count }}<br>
                                                <i class="las la-heart"></i> {{ $listing->watchlist_count }}
                                            </small>
                                        </td>
                                        <td>
                                            @php echo $listing->listingStatus @endphp
                                            @if($listing->is_featured)
                                                <br><span class="badge badge--warning">@lang('Featured')</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="button--group">
                                                <a href="{{ route('admin.listing.details', $listing->id) }}" 
                                                   class="btn btn-sm btn-outline--primary">
                                                    <i class="las la-eye"></i> @lang('Details')
                                                </a>
                                                
                                                @if($listing->status == \App\Constants\Status::LISTING_PENDING)
                                                    <button type="button" class="btn btn-sm btn-outline--success confirmationBtn"
                                                            data-action="{{ route('admin.listing.approve', $listing->id) }}"
                                                            data-question="@lang('Are you sure to approve this listing?')">
                                                        <i class="las la-check"></i> @lang('Approve')
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline--danger rejectBtn"
                                                            data-id="{{ $listing->id }}">
                                                        <i class="las la-times"></i> @lang('Reject')
                                                    </button>
                                                @endif
                                            </div>
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
                @if ($listings->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($listings) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Reject Listing')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="" method="POST" id="rejectForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Rejection Reason')</label>
                            <textarea name="reason" class="form-control" rows="4" required 
                                      placeholder="@lang('Provide a reason for rejection...')"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn--danger">@lang('Reject')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Search listing..." />
@endpush

@push('script')
<script>
    (function($) {
        "use strict";
        
        $('.rejectBtn').on('click', function() {
            var id = $(this).data('id');
            var url = "{{ route('admin.listing.reject', ':id') }}";
            url = url.replace(':id', id);
            
            $('#rejectForm').attr('action', url);
            $('#rejectModal').modal('show');
        });
    })(jQuery);
</script>
@endpush

