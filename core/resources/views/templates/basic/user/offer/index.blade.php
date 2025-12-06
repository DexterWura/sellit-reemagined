@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="{{ route('user.offer.received') }}" class="btn btn--base btn-sm">
                    <i class="las la-inbox"></i> @lang('Received Offers')
                </a>
            </div>
            
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table--light style--two">
                    <thead>
                        <tr>
                            <th>@lang('Listing')</th>
                            <th>@lang('My Offer')</th>
                            <th>@lang('Asking Price')</th>
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
                                            <small class="text-muted">@lang('Seller'): {{ $offer->seller->username ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ showAmount($offer->amount) }}</strong>
                                    @if($offer->counter_amount > 0)
                                        <br>
                                        <small class="text-info">@lang('Counter'): {{ showAmount($offer->counter_amount) }}</small>
                                    @endif
                                </td>
                                <td>{{ showAmount($offer->listing->asking_price) }}</td>
                                <td>@php echo $offer->offerStatus @endphp</td>
                                <td>{{ $offer->created_at->format('M d, Y') }}</td>
                                <td>
                                    @if($offer->status == \App\Constants\Status::OFFER_COUNTERED)
                                        <form action="{{ route('user.offer.accept.counter', $offer->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" 
                                                    onclick="return confirm('@lang('Accept counter offer of') {{ showAmount($offer->counter_amount) }}?')">
                                                <i class="las la-check"></i> @lang('Accept')
                                            </button>
                                        </form>
                                    @endif
                                    @if(in_array($offer->status, [\App\Constants\Status::OFFER_PENDING, \App\Constants\Status::OFFER_COUNTERED]))
                                        <form action="{{ route('user.offer.cancel', $offer->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                    onclick="return confirm('@lang('Cancel this offer?')')">
                                                <i class="las la-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($offer->status == \App\Constants\Status::OFFER_ACCEPTED && $offer->escrow_id)
                                        <a href="{{ route('user.escrow.details', $offer->escrow_id) }}" class="btn btn-sm btn--base">
                                            <i class="las la-hand-holding-usd"></i> @lang('Pay')
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <img src="{{ asset('assets/images/empty_list.png') }}" alt="" style="max-width: 120px;">
                                    <p class="mt-2 text-muted">@lang('No offers made yet')</p>
                                    <a href="{{ route('marketplace.browse') }}" class="btn btn--base btn-sm">
                                        <i class="las la-search"></i> @lang('Browse Listings')
                                    </a>
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
@endsection

