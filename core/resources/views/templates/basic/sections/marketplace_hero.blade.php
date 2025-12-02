@php
    $content = getContent('marketplace_hero.content', true);
    if(!@$content->data_values->status) return;
    
    // Get marketplace stats
    $totalListings = \App\Models\Listing::where('status', \App\Constants\Status::LISTING_ACTIVE)->count();
    $totalSold = \App\Models\Listing::where('status', \App\Constants\Status::LISTING_SOLD)->count();
@endphp

<section class="flippa-hero">
    <div class="hero-background">
        <div class="wave-pattern"></div>
    </div>
    
    <div class="container position-relative">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8 text-center">
                
                {{-- Main Heading --}}
                <h1 class="hero-title">
                    #1 Platform to Buy & Sell
                    <span class="highlight">Online Businesses</span>
                </h1>
                
                {{-- Search Bar --}}
                <div class="hero-search-wrapper">
                    <form action="{{ route('marketplace.browse') }}" method="GET" class="hero-search-form">
                        <div class="search-input-wrapper">
                            <input type="text" name="search" class="hero-search-input" 
                                   placeholder="@lang('e.g. Shopify Stores, SaaS, Blogs...')">
                            <button type="submit" class="hero-search-btn">
                                <i class="las la-search"></i>
                                <span>@lang('Search')</span>
                            </button>
                        </div>
                    </form>
                </div>
                
                {{-- Trending Tags --}}
                <div class="trending-tags">
                    <span class="trending-label">@lang('Trending'):</span>
                    <div class="tags-wrapper">
                        <a href="{{ route('marketplace.browse', ['search' => 'SaaS']) }}" class="trend-tag">SaaS</a>
                        <a href="{{ route('marketplace.browse', ['search' => 'Blog']) }}" class="trend-tag">Blog</a>
                        <a href="{{ route('marketplace.browse', ['search' => 'Shopify']) }}" class="trend-tag">Shopify</a>
                        <a href="{{ route('marketplace.browse', ['search' => 'AdSense']) }}" class="trend-tag">AdSense</a>
                        <a href="{{ route('marketplace.browse', ['search' => 'Amazon']) }}" class="trend-tag">Amazon</a>
                        <a href="{{ route('marketplace.browse', ['search' => 'YouTube']) }}" class="trend-tag">YouTube</a>
                    </div>
                </div>
                
                {{-- CTA Button --}}
                <div class="hero-cta">
                    @auth
                        <a href="{{ route('user.listing.create') }}" class="cta-btn">
                            @lang('Start Selling Now')
                        </a>
                    @else
                        <a href="{{ route('user.register') }}" class="cta-btn">
                            @lang('Sign up for free. No credit card required')
                        </a>
                    @endauth
                </div>
                
                {{-- Stats --}}
                <div class="hero-stats">
                    <p class="stats-text">
                        @lang('Over') <strong>{{ number_format($totalSold > 0 ? $totalSold : 1000) }}+</strong> @lang('online acquisitions globally')
                    </p>
                </div>
                
                {{-- Business Type Icons --}}
                <div class="business-types">
                    <a href="{{ route('marketplace.type', 'domain') }}" class="type-icon" title="@lang('Domains')">
                        <i class="las la-globe"></i>
                    </a>
                    <a href="{{ route('marketplace.type', 'website') }}" class="type-icon" title="@lang('Websites')">
                        <i class="las la-laptop"></i>
                    </a>
                    <a href="{{ route('marketplace.type', 'mobile_app') }}" class="type-icon" title="@lang('Mobile Apps')">
                        <i class="las la-mobile-alt"></i>
                    </a>
                    <a href="{{ route('marketplace.type', 'desktop_app') }}" class="type-icon" title="@lang('Desktop Apps')">
                        <i class="las la-desktop"></i>
                    </a>
                    <a href="{{ route('marketplace.type', 'social_media_account') }}" class="type-icon" title="@lang('Social Media')">
                        <i class="las la-share-alt"></i>
                    </a>
                    <a href="{{ route('marketplace.auctions') }}" class="type-icon type-icon--highlight" title="@lang('Live Auctions')">
                        <i class="las la-gavel"></i>
                    </a>
                </div>
                
            </div>
        </div>
    </div>
</section>

@push('style')
<style>
    .hero-title .highlight {
        color: #{{ gs('base_color') }} !important;
    }
    
    .hero-search-btn {
        background: #{{ gs('base_color') }} !important;
    }
    
    .hero-search-btn:hover {
        background: #{{ gs('base_color') }} !important;
        filter: brightness(0.85);
        box-shadow: 0 10px 30px #{{ gs('base_color') }}66;
    }
</style>
@endpush
