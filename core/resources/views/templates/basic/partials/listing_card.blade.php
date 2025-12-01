<div class="col-md-6 col-lg-4">
    <div class="listing-card card h-100 shadow-sm border-0 overflow-hidden">
        <div class="card-img-top position-relative overflow-hidden">
            @if($listing->primaryImage)
                <a href="{{ route('marketplace.listing.show', $listing->slug) }}">
                    <img src="{{ getImage(getFilePath('listing') . '/' . $listing->primaryImage->image) }}" 
                         alt="{{ $listing->title }}" class="img-fluid listing-img" style="height: 200px; object-fit: cover; width: 100%; transition: transform 0.3s;">
                </a>
            @else
                <a href="{{ route('marketplace.listing.show', $listing->slug) }}" class="placeholder-img bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                    @if($listing->business_type == 'domain')
                        <i class="las la-globe text-muted" style="font-size: 4rem;"></i>
                    @elseif($listing->business_type == 'website')
                        <i class="las la-laptop text-muted" style="font-size: 4rem;"></i>
                    @elseif($listing->business_type == 'social_media_account')
                        <i class="las la-share-alt text-muted" style="font-size: 4rem;"></i>
                    @elseif($listing->business_type == 'mobile_app')
                        <i class="las la-mobile-alt text-muted" style="font-size: 4rem;"></i>
                    @else
                        <i class="las la-desktop text-muted" style="font-size: 4rem;"></i>
                    @endif
                </a>
            @endif
            
            <div class="listing-badges position-absolute top-0 start-0 end-0 p-2 d-flex justify-content-between">
                <div>
                    @if($listing->is_featured)
                        <span class="badge bg-warning text-dark shadow-sm">
                            <i class="las la-star"></i> @lang('Featured')
                        </span>
                    @endif
                    @if($listing->is_verified)
                        <span class="badge bg-success shadow-sm">
                            <i class="las la-check-circle"></i> @lang('Verified')
                        </span>
                    @endif
                </div>
                <div>
                    @if($listing->sale_type === 'auction')
                        <span class="badge bg-danger shadow-sm">
                            <i class="las la-gavel"></i> @lang('Auction')
                        </span>
                    @endif
                </div>
            </div>
            
            @if($listing->sale_type === 'auction' && $listing->auction_end)
                <div class="auction-timer position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-75 text-white p-2 text-center">
                    <small>
                        <i class="las la-clock"></i>
                        @if($listing->auction_end->isPast())
                            @lang('Auction Ended')
                        @else
                            @lang('Ends'): {{ $listing->auction_end->diffForHumans() }}
                        @endif
                    </small>
                </div>
            @endif
        </div>
        
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <a href="{{ route('marketplace.type', $listing->business_type) }}" class="badge bg--base bg-opacity-10 text--base text-decoration-none">
                    @if($listing->business_type == 'domain')
                        <i class="las la-globe"></i>
                    @elseif($listing->business_type == 'website')
                        <i class="las la-laptop"></i>
                    @elseif($listing->business_type == 'social_media_account')
                        <i class="las la-share-alt"></i>
                    @elseif($listing->business_type == 'mobile_app')
                        <i class="las la-mobile-alt"></i>
                    @else
                        <i class="las la-desktop"></i>
                    @endif
                    {{ ucfirst(str_replace('_', ' ', $listing->business_type)) }}
                </a>
                @if($listing->listingCategory)
                    <small class="text-muted">
                        <a href="{{ route('marketplace.category', $listing->listingCategory->slug) }}" class="text-muted text-decoration-none">
                            {{ $listing->listingCategory->name }}
                        </a>
                    </small>
                @endif
            </div>
            
            <h5 class="card-title mb-2 fw-semibold" style="min-height: 48px;">
                <a href="{{ route('marketplace.listing.show', $listing->slug) }}" class="text-dark text-decoration-none stretched-link-title">
                    {{ Str::limit($listing->title, 50) }}
                </a>
            </h5>
            
            @if($listing->tagline)
                <p class="card-text text-muted small mb-3" style="min-height: 40px;">{{ Str::limit($listing->tagline, 80) }}</p>
            @else
                <div style="min-height: 40px;"></div>
            @endif
            
            {{-- Metrics --}}
            <div class="listing-stats d-flex flex-wrap gap-1 mb-3">
                @if($listing->monthly_revenue > 0)
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                        <i class="las la-chart-line"></i> {{ gs('cur_sym') }}{{ shortNumber($listing->monthly_revenue) }}/mo
                    </span>
                @endif
                @if($listing->monthly_visitors > 0)
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                        <i class="las la-users"></i> {{ shortNumber($listing->monthly_visitors) }}
                    </span>
                @endif
                @if($listing->followers_count > 0)
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                        <i class="las la-user-friends"></i> {{ shortNumber($listing->followers_count) }}
                    </span>
                @endif
                @if($listing->downloads_count > 0)
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                        <i class="las la-download"></i> {{ shortNumber($listing->downloads_count) }}
                    </span>
                @endif
            </div>
            
            {{-- Price Section --}}
            <div class="price-section pt-3 border-top">
                @if($listing->sale_type === 'auction')
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">@lang('Current Bid')</small>
                            <strong class="text--base fs-5">
                                {{ $listing->current_bid > 0 ? showAmount($listing->current_bid) : showAmount($listing->starting_bid) }}
                            </strong>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">{{ $listing->total_bids }} @lang('bids')</small>
                            @if($listing->buy_now_price > 0)
                                <small class="text-success">
                                    <i class="las la-bolt"></i> @lang('Buy Now'): {{ showAmount($listing->buy_now_price) }}
                                </small>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">@lang('Asking Price')</small>
                            <strong class="text--base fs-5">{{ showAmount($listing->asking_price) }}</strong>
                        </div>
                        @if($listing->monthly_profit > 0)
                            <div class="text-end">
                                <small class="text-muted d-block">@lang('Multiple')</small>
                                <span class="text-success fw-semibold">
                                    {{ number_format($listing->asking_price / ($listing->monthly_profit * 12), 1) }}x
                                </span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card-footer bg-light border-top d-flex justify-content-between align-items-center py-2">
            <div class="seller-info d-flex align-items-center">
                <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center me-2" 
                     style="width: 28px; height: 28px; background: var(--base-color); opacity: 0.8;">
                    <span class="text-white small fw-bold">{{ strtoupper(substr($listing->seller->username ?? 'U', 0, 1)) }}</span>
                </div>
                <small>
                    <a href="{{ route('marketplace.seller', ['username' => $listing->seller->username ?? 'unknown']) }}" class="text-muted text-decoration-none">
                        {{ $listing->seller->username ?? 'Unknown' }}
                    </a>
                    @if($listing->seller && $listing->seller->is_verified_seller)
                        <i class="las la-check-circle text-primary" title="@lang('Verified Seller')"></i>
                    @endif
                </small>
            </div>
            <div class="listing-meta d-flex gap-3">
                <small class="text-muted" title="@lang('Views')">
                    <i class="las la-eye"></i> {{ shortNumber($listing->view_count) }}
                </small>
                <small class="text-muted" title="@lang('Watching')">
                    <i class="las la-heart"></i> {{ shortNumber($listing->watchlist_count) }}
                </small>
            </div>
        </div>
    </div>
</div>

@push('style-lib')
<style>
.listing-card:hover .listing-img {
    transform: scale(1.05);
}
.listing-card .stretched-link-title:hover {
    color: var(--base-color) !important;
}
</style>
@endpush

