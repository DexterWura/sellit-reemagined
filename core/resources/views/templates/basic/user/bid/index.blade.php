@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">@lang('My Bids')</h4>
            <a href="{{ route('user.bid.won') }}" class="btn btn--base btn-sm">
                <i class="las la-trophy"></i> @lang('Won Auctions')
            </a>
        </div>
        
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-responsive--lg custom--table">
                    <thead>
                        <tr>
                            <th>@lang('Listing')</th>
                            <th>@lang('My Bid')</th>
                            <th>@lang('Current Bid')</th>
                            <th>@lang('Ends In')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bids as $bid)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($bid->listing->images->first())
                                            <img src="{{ getImage(getFilePath('listing') . '/' . $bid->listing->images->first()->image) }}" 
                                                 alt="" class="me-3" style="width: 60px; height: 45px; object-fit: cover; border-radius: 5px;">
                                        @endif
                                        <div>
                                            <a href="{{ route('marketplace.listing.show', $bid->listing->slug) }}">
                                                {{ Str::limit($bid->listing->title, 40) }}
                                            </a>
                                            <br>
                                            <small class="text-muted">@lang('by') {{ $bid->listing->seller->username ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong class="{{ $bid->status == \App\Constants\Status::BID_WINNING ? 'text-success' : '' }}">
                                        {{ showAmount($bid->amount) }}
                                    </strong>
                                    @if($bid->max_bid > 0)
                                        <br>
                                        <small class="text-muted">@lang('Max'): {{ showAmount($bid->max_bid) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ showAmount($bid->listing->current_bid) }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $bid->listing->total_bids }} @lang('bids')</small>
                                </td>
                                <td>
                                    @if($bid->listing->auction_end)
                                        @if($bid->listing->auction_end->isFuture())
                                            <span class="text-danger">{{ $bid->listing->auction_end->diffForHumans() }}</span>
                                        @else
                                            <span class="text-muted">@lang('Ended')</span>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>@php echo $bid->bidStatus @endphp</td>
                                <td>
                                    <a href="{{ route('marketplace.listing.show', $bid->listing->slug) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="las la-eye"></i> @lang('View')
                                    </a>
                                    @if(in_array($bid->status, [\App\Constants\Status::BID_ACTIVE, \App\Constants\Status::BID_WINNING]))
                                        @if($bid->listing->auction_end && $bid->listing->auction_end->diffInHours(now()) >= 24)
                                            <form action="{{ route('user.bid.cancel', $bid->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('@lang('Are you sure?')')">
                                                    <i class="las la-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <img src="{{ asset('assets/images/empty_list.png') }}" alt="" style="max-width: 120px;">
                                    <p class="mt-2 text-muted">@lang('No bids placed yet')</p>
                                    <a href="{{ route('marketplace.auctions') }}" class="btn btn--base btn-sm">
                                        <i class="las la-gavel"></i> @lang('Browse Auctions')
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($bids->hasPages())
            <div class="mt-4">{{ $bids->links() }}</div>
        @endif
@endsection

