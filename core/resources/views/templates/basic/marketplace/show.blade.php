@extends($activeTemplate . 'layouts.frontend')
@section('content')
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Listing Header -->
                <div class="listing-header mb-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-secondary mb-2">
                                {{ ucfirst(str_replace('_', ' ', $listing->business_type)) }}
                            </span>
                            @if($listing->is_verified)
                                <span class="badge bg-success mb-2">
                                    <i class="las la-check-circle"></i> @lang('Verified')
                                </span>
                            @endif
                            @if($listing->is_featured)
                                <span class="badge bg-warning mb-2">
                                    <i class="las la-star"></i> @lang('Featured')
                                </span>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            @auth
                                <form action="{{ route('user.watchlist.toggle', $listing->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                                        <i class="las la-heart{{ $isWatching ? ' text-danger' : '' }}"></i>
                                        {{ $isWatching ? __('Watching') : __('Watch') }}
                                    </button>
                                </form>
                            @endauth
                            <button class="btn btn-outline-secondary btn-sm" onclick="shareUrl()">
                                <i class="las la-share"></i> @lang('Share')
                            </button>
                        </div>
                    </div>
                    
                    <h1 class="h2 mb-2">{{ $listing->title }}</h1>
                    @if($listing->tagline)
                        <p class="lead text-muted">{{ $listing->tagline }}</p>
                    @endif
                    
                    <div class="listing-meta text-muted small">
                        <span><i class="las la-eye"></i> {{ number_format($listing->view_count) }} @lang('views')</span>
                        <span class="mx-2">|</span>
                        <span><i class="las la-heart"></i> {{ number_format($listing->watchlist_count) }} @lang('watchers')</span>
                        <span class="mx-2">|</span>
                        <span>@lang('Listed') {{ $listing->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                
                <!-- Images -->
                @if($listing->business_type !== 'domain' && $listing->images->count() > 0)
                <div class="listing-images mb-4">
                    @if($listing->images->count() === 1)
                        <!-- Single Image - Simple Display -->
                        <div class="main-image-container mb-3 position-relative">
                            <img src="{{ getImage(getFilePath('listing') . '/' . $listing->images->first()->image) }}"
                                 alt="{{ $listing->title }}"
                                 class="img-fluid rounded w-100 main-image"
                                 id="mainImage"
                                 style="cursor: pointer;"
                                 onclick="openFullscreenGallery(0)"
                                 onerror="this.onerror=null; this.src='{{ asset('assets/images/default.png') }}';">
                        </div>
                    @else
                        <!-- Multiple Images - Gallery Display -->
                        <div class="main-image-container mb-3 position-relative">
                            <img src="{{ getImage(getFilePath('listing') . '/' . $listing->images->first()->image) }}"
                                 alt="{{ $listing->title }}"
                                 class="img-fluid rounded w-100 main-image"
                                 id="mainImage"
                                 style="cursor: pointer;"
                                 onclick="openFullscreenGallery(currentIndex)"
                                 onerror="this.onerror=null; this.src='{{ asset('assets/images/default.png') }}';">

                            <!-- Navigation arrows -->
                            <button class="btn btn-dark btn-sm position-absolute top-50 start-0 translate-middle-y ms-2 nav-arrow prev-arrow"
                                    id="prevImage" style="display: none; opacity: 0.7;">
                                <i class="las la-chevron-left"></i>
                            </button>
                            <button class="btn btn-dark btn-sm position-absolute top-50 end-0 translate-middle-y me-2 nav-arrow next-arrow"
                                    id="nextImage" style="opacity: 0.7;">
                                <i class="las la-chevron-right"></i>
                            </button>

                            <!-- Image counter -->
                            <div class="position-absolute bottom-0 end-0 mb-2 me-2">
                                <span class="badge bg-dark bg-opacity-75 text-white px-2 py-1">
                                    <span id="currentImageIndex">1</span> / {{ $listing->images->count() }}
                                </span>
                            </div>
                        </div>

                        <!-- Thumbnail Gallery -->
                        <div class="thumbnail-container">
                            <div class="thumbnail-images d-flex gap-2 overflow-auto pb-2" id="thumbnailStrip">
                                @foreach($listing->images as $index => $image)
                                    @php
                                        $thumbPath = getFilePath('listing') . '/' . $image->image;
                                        $thumbUrl = getImage($thumbPath);
                                    @endphp
                                    <div class="thumbnail-wrapper position-relative" data-index="{{ $index }}">
                                        <img src="{{ $thumbUrl }}"
                                             alt="{{ $listing->title }}"
                                             class="img-thumbnail thumbnail-item {{ $index === 0 ? 'active' : '' }}"
                                             style="width: 80px; height: 60px; object-fit: cover; cursor: pointer; border: 2px solid {{ $index === 0 ? '#007bff' : 'transparent' }}; transition: all 0.3s ease;"
                                             data-index="{{ $index }}"
                                             onerror="this.onerror=null; this.src='{{ asset('assets/images/default.png') }}';">

                                        <!-- Active indicator -->
                                        @if($index === 0)
                                        <div class="position-absolute top-0 end-0 mt-1 me-1">
                                            <i class="las la-check-circle text-primary" style="font-size: 14px;"></i>
                                        </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <!-- Thumbnail scroll indicators -->
                            @if($listing->images->count() > 6)
                            <div class="thumbnail-scroll-hint text-center mt-2">
                                <small class="text-muted">
                                    <i class="las la-arrows-alt-h"></i> Scroll for more images
                                </small>
                            </div>
                            @endif
                        </div>
                    @endif
                </div>
                @elseif($listing->business_type === 'domain')
                <!-- Domain preview with gradient background -->
                @php
                    // Generate consistent color based on domain name
                    $domainName = $listing->domain_name ?? 'example.com';
                    $hash = 0;
                    for ($i = 0; $i < strlen($domainName); $i++) {
                        $hash = ord($domainName[$i]) + (($hash << 5) - $hash);
                    }
                    
                    // Predefined color gradients
                    $gradients = [
                        ['#667eea', '#764ba2'], // Purple
                        ['#f093fb', '#f5576c'], // Pink
                        ['#4facfe', '#00f2fe'], // Blue
                        ['#43e97b', '#38f9d7'], // Green
                        ['#fa709a', '#fee140'], // Pink-Yellow
                        ['#30cfd0', '#330867'], // Cyan-Purple
                        ['#a8edea', '#fed6e3'], // Light Blue-Pink
                        ['#ff9a9e', '#fecfef'], // Red-Pink
                        ['#ffecd2', '#fcb69f'], // Orange
                        ['#ff6e7f', '#bfe9ff'], // Red-Blue
                    ];
                    
                    // Ensure hash is positive and calculate safe index
                    $hash = abs($hash);
                    $gradientCount = count($gradients);
                    $index = $gradientCount > 0 ? ($hash % $gradientCount) : 0;
                    
                    // Double-check index is valid
                    if ($index < 0 || $index >= $gradientCount) {
                        $index = 0;
                    }
                    
                    $colors = $gradients[$index] ?? $gradients[0];
                @endphp
                <div class="domain-preview mb-4 rounded overflow-hidden position-relative" 
                     style="height: 400px; background: linear-gradient(135deg, {{ $colors[0] }} 0%, {{ $colors[1] }} 100%);">
                    <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center text-white" style="z-index: 1;">
                        <i class="las la-globe mb-3" style="font-size: 5rem; opacity: 0.3;"></i>
                        <div class="position-relative">
                            <div class="position-absolute top-0 start-50 translate-middle-x" style="width: 100px; height: 2px; background: rgba(255,255,255,0.5); transform: translateX(-50%);"></div>
                        </div>
                        <h2 class="mb-0 mt-4 fw-bold text-white" style="font-size: 2.5rem; text-shadow: 0 2px 8px rgba(0,0,0,0.3);">
                            {{ $listing->domain_name ?? $listing->title }}
                        </h2>
                    </div>
                </div>
                @endif
                
                <!-- Description -->
                <div class="listing-description card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">@lang('Description')</h4>
                        <div class="description-content">
                            {!! nl2br(e($listing->description)) !!}
                        </div>
                    </div>
                </div>
                
                <!-- Business Details -->
                <div class="business-details card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">@lang('Business Details')</h4>
                        <div class="row g-3">
                            @if($listing->business_type === 'domain')
                                @if($listing->domain_name)
                                    <div class="col-md-6">
                                        <strong>@lang('Domain'):</strong> {{ $listing->domain_name }}
                                    </div>
                                @endif
                                @if($listing->domain_registrar)
                                    <div class="col-md-6">
                                        <strong>@lang('Registrar'):</strong> {{ $listing->domain_registrar }}
                                    </div>
                                @endif
                                @if($listing->domain_expiry)
                                    <div class="col-md-6">
                                        <strong>@lang('Expires'):</strong> {{ $listing->domain_expiry->format('M d, Y') }}
                                    </div>
                                @endif
                                @if($listing->domain_age_years)
                                    <div class="col-md-6">
                                        <strong>@lang('Age'):</strong> {{ $listing->domain_age_years }} @lang('years')
                                    </div>
                                @endif
                            @elseif($listing->business_type === 'website')
                                @if($listing->url)
                                    <div class="col-md-6">
                                        <strong>@lang('URL'):</strong> 
                                        <a href="{{ $listing->url }}" target="_blank" rel="nofollow">{{ $listing->url }}</a>
                                    </div>
                                @endif
                                @if($listing->niche)
                                    <div class="col-md-6">
                                        <strong>@lang('Niche'):</strong> {{ $listing->niche }}
                                    </div>
                                @endif
                                @if($listing->tech_stack)
                                    <div class="col-md-6">
                                        <strong>@lang('Tech Stack'):</strong> {{ $listing->tech_stack }}
                                    </div>
                                @endif
                            @elseif($listing->business_type === 'social_media_account')
                                @if($listing->platform)
                                    <div class="col-md-6">
                                        <strong>@lang('Platform'):</strong> {{ ucfirst($listing->platform) }}
                                    </div>
                                @endif
                                @if($listing->niche)
                                    <div class="col-md-6">
                                        <strong>@lang('Niche'):</strong> {{ $listing->niche }}
                                    </div>
                                @endif
                                @if($listing->followers_count)
                                    <div class="col-md-6">
                                        <strong>@lang('Followers'):</strong> {{ number_format($listing->followers_count) }}
                                    </div>
                                @endif
                                @if($listing->engagement_rate)
                                    <div class="col-md-6">
                                        <strong>@lang('Engagement Rate'):</strong> {{ $listing->engagement_rate }}%
                                    </div>
                                @endif
                            @elseif(in_array($listing->business_type, ['mobile_app', 'desktop_app']))
                                @if($listing->app_store_url)
                                    <div class="col-md-6">
                                        <strong>@lang('App Store'):</strong> 
                                        <a href="{{ $listing->app_store_url }}" target="_blank">@lang('View')</a>
                                    </div>
                                @endif
                                @if($listing->play_store_url)
                                    <div class="col-md-6">
                                        <strong>@lang('Play Store'):</strong> 
                                        <a href="{{ $listing->play_store_url }}" target="_blank">@lang('View')</a>
                                    </div>
                                @endif
                                @if($listing->downloads_count)
                                    <div class="col-md-6">
                                        <strong>@lang('Downloads'):</strong> {{ number_format($listing->downloads_count) }}
                                    </div>
                                @endif
                                @if($listing->app_rating)
                                    <div class="col-md-6">
                                        <strong>@lang('Rating'):</strong> {{ $listing->app_rating }}/5
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Financials -->
                @if($listing->monthly_revenue > 0 || $listing->monthly_profit > 0)
                <div class="financials card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">@lang('Financials')</h4>
                        <div class="row g-3">
                            @if($listing->monthly_revenue > 0)
                                <div class="col-md-3 col-6">
                                    <div class="stat-card text-center p-3 bg-light rounded">
                                        <small class="text-muted d-block">@lang('Monthly Revenue')</small>
                                        <strong class="fs-5">{{ showAmount($listing->monthly_revenue) }}</strong>
                                    </div>
                                </div>
                            @endif
                            @if($listing->monthly_profit > 0)
                                <div class="col-md-3 col-6">
                                    <div class="stat-card text-center p-3 bg-light rounded">
                                        <small class="text-muted d-block">@lang('Monthly Profit')</small>
                                        <strong class="fs-5">{{ showAmount($listing->monthly_profit) }}</strong>
                                    </div>
                                </div>
                            @endif
                            @if($listing->yearly_revenue > 0)
                                <div class="col-md-3 col-6">
                                    <div class="stat-card text-center p-3 bg-light rounded">
                                        <small class="text-muted d-block">@lang('Yearly Revenue')</small>
                                        <strong class="fs-5">{{ showAmount($listing->yearly_revenue) }}</strong>
                                    </div>
                                </div>
                            @endif
                            @if($listing->yearly_profit > 0)
                                <div class="col-md-3 col-6">
                                    <div class="stat-card text-center p-3 bg-light rounded">
                                        <small class="text-muted d-block">@lang('Yearly Profit')</small>
                                        <strong class="fs-5">{{ showAmount($listing->yearly_profit) }}</strong>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Traffic -->
                @if($listing->monthly_visitors > 0 || $listing->monthly_page_views > 0)
                <div class="traffic card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">@lang('Traffic')</h4>
                        <div class="row g-3">
                            @if($listing->monthly_visitors > 0)
                                <div class="col-md-4 col-6">
                                    <div class="stat-card text-center p-3 bg-light rounded">
                                        <small class="text-muted d-block">@lang('Monthly Visitors')</small>
                                        <strong class="fs-5">{{ number_format($listing->monthly_visitors) }}</strong>
                                    </div>
                                </div>
                            @endif
                            @if($listing->monthly_page_views > 0)
                                <div class="col-md-4 col-6">
                                    <div class="stat-card text-center p-3 bg-light rounded">
                                        <small class="text-muted d-block">@lang('Page Views')</small>
                                        <strong class="fs-5">{{ number_format($listing->monthly_page_views) }}</strong>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Q&A Section -->
                <div class="qa-section card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">@lang('Questions & Answers')</h4>
                        
                        @if($listing->questions->count() > 0)
                            <div class="questions-list mb-4">
                                @foreach($listing->questions as $question)
                                    <div class="question-item border-bottom pb-3 mb-3">
                                        <div class="question">
                                            <strong><i class="las la-question-circle text--base"></i> {{ $question->question }}</strong>
                                            <small class="text-muted d-block">
                                                @lang('Asked by') {{ $question->asker->username ?? 'Anonymous' }} 
                                                {{ $question->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                        @if($question->answer)
                                            <div class="answer mt-2 ps-4">
                                                <i class="las la-reply text-muted"></i> {{ $question->answer }}
                                                <small class="text-muted d-block">
                                                    @lang('Answered') {{ $question->answered_at->diffForHumans() }}
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">@lang('No questions yet. Be the first to ask!')</p>
                        @endif
                        
                        @auth
                            @if(auth()->id() !== $listing->user_id && $listing->status == \App\Constants\Status::LISTING_ACTIVE)
                                <form action="{{ route('marketplace.listing.question', $listing->id) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <textarea name="question" class="form-control" rows="2" 
                                                  placeholder="@lang('Ask the seller a question...')" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn--base btn-sm">
                                        <i class="las la-paper-plane"></i> @lang('Ask Question')
                                    </button>
                                </form>
                            @endif
                        @else
                            <p class="text-muted">
                                <a href="{{ route('user.login') }}">@lang('Login')</a> @lang('to ask a question')
                            </p>
                        @endauth
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Price Card -->
                <div class="price-card card shadow-sm mb-4 sticky-top" style="top: 20px;">
                    <div class="card-body">
                        @if($listing->sale_type === 'auction')
                            <div class="auction-info">
                                <div class="current-bid mb-3">
                                    <small class="text-muted d-block">@lang('Current Bid')</small>
                                    <span class="fs-2 fw-bold text--base">
                                        {{ $listing->current_bid > 0 ? showAmount($listing->current_bid) : showAmount($listing->starting_bid) }}
                                    </span>
                                </div>
                                
                                <div class="auction-timer mb-3 p-3 bg-light rounded">
                                    <small class="text-muted d-block">@lang('Time Remaining')</small>
                                    @if($listing->auction_end && $listing->auction_end->isFuture())
                                        <span class="fs-5 text-danger" id="countdownTimer">
                                            {{ $listing->auction_end->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="fs-5 text-muted">@lang('Auction Ended')</span>
                                    @endif
                                </div>
                                
                                <div class="bid-stats mb-3">
                                    <span class="me-3"><i class="las la-gavel"></i> {{ $listing->total_bids }} @lang('bids')</span>
                                    @if($listing->reserve_price > 0)
                                        @if($listing->hasReserveBeenMet())
                                            <span class="text-success"><i class="las la-check"></i> @lang('Reserve Met')</span>
                                        @else
                                            <span class="text-warning"><i class="las la-times"></i> @lang('Reserve Not Met')</span>
                                        @endif
                                    @endif
                                </div>
                                
                                @if($listing->status == \App\Constants\Status::LISTING_ACTIVE && $listing->auction_end && $listing->auction_end->isFuture())
                                    @auth
                                        @if(auth()->id() !== $listing->user_id)
                                            <form action="{{ route('user.bid.place', $listing->id) }}" method="POST">
                                                @csrf
                                                <div class="mb-3">
                                                    <label class="form-label">@lang('Your Bid') (@lang('Min'): {{ showAmount($listing->minimum_bid) }})</label>
                                                    <input type="number" name="amount" class="form-control form-control-lg" 
                                                           min="{{ $listing->minimum_bid }}" step="0.01" required
                                                           placeholder="{{ showAmount($listing->minimum_bid, currencyFormat: false) }}">
                                                </div>
                                                <button type="submit" class="btn btn--base btn-lg w-100 mb-2">
                                                    <i class="las la-gavel"></i> @lang('Place Bid')
                                                </button>
                                            </form>
                                            
                                            @if($listing->buy_now_price > 0)
                                                <form action="{{ route('user.bid.buy.now', $listing->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success btn-lg w-100" 
                                                            onclick="return confirm('@lang('Buy now for') {{ showAmount($listing->buy_now_price) }}?')">
                                                        <i class="las la-shopping-cart"></i> @lang('Buy Now') - {{ showAmount($listing->buy_now_price) }}
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <div class="alert alert-info">@lang('This is your listing')</div>
                                        @endif
                                    @else
                                        <a href="{{ route('user.login') }}" class="btn btn--base btn-lg w-100">
                                            @lang('Login to Bid')
                                        </a>
                                    @endauth
                                @endif
                            </div>
                        @else
                            <div class="fixed-price-info">
                                <div class="asking-price mb-3">
                                    <small class="text-muted d-block">@lang('Asking Price')</small>
                                    <span class="fs-2 fw-bold text--base">{{ showAmount($listing->asking_price) }}</span>
                                </div>
                                
                                @if($listing->status == \App\Constants\Status::LISTING_ACTIVE)
                                    @auth
                                        @if(auth()->id() !== $listing->user_id)
                                            <form action="{{ route('user.offer.make', $listing->id) }}" method="POST" class="mb-3">
                                                @csrf
                                                <div class="mb-3">
                                                    <label class="form-label">@lang('Your Offer')</label>
                                                    <input type="number" name="amount" class="form-control form-control-lg" 
                                                           min="1" step="0.01" required
                                                           placeholder="@lang('Enter your offer')">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">@lang('Message') (@lang('optional'))</label>
                                                    <textarea name="message" class="form-control" rows="2" 
                                                              placeholder="@lang('Add a message to the seller')"></textarea>
                                                </div>
                                                <button type="submit" class="btn btn--base btn-lg w-100">
                                                    <i class="las la-paper-plane"></i> @lang('Make Offer')
                                                </button>
                                            </form>
                                        @else
                                            <div class="alert alert-info">@lang('This is your listing')</div>
                                        @endif
                                    @else
                                        <a href="{{ route('user.login') }}" class="btn btn--base btn-lg w-100">
                                            @lang('Login to Make Offer')
                                        </a>
                                    @endauth
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Seller Card -->
                <div class="seller-card card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">@lang('About the Seller')</h5>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 60px; height: 60px;">
                                <span class="text-white fs-4">{{ strtoupper(substr($seller->username, 0, 1)) }}</span>
                            </div>
                            <div>
                                <h6 class="mb-0">
                                    <a href="{{ route('marketplace.seller', $seller->username) }}">{{ $seller->fullname }}</a>
                                </h6>
                                <small class="text-muted">{{ '@' . $seller->username }}</small>
                                @if($seller->is_verified_seller)
                                    <span class="badge bg-success">
                                        <i class="las la-check-circle"></i> @lang('Verified')
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="seller-stats">
                            <div class="row g-2 text-center">
                                <div class="col-4">
                                    <div class="p-2 bg-light rounded">
                                        <strong>{{ $sellerStats['total_sales'] }}</strong>
                                        <small class="d-block text-muted">@lang('Sales')</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 bg-light rounded">
                                        <strong>{{ number_format($sellerStats['avg_rating'], 1) }}</strong>
                                        <small class="d-block text-muted">@lang('Rating')</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 bg-light rounded">
                                        <strong>{{ $sellerStats['active_listings'] }}</strong>
                                        <small class="d-block text-muted">@lang('Active')</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="las la-calendar"></i> @lang('Member since') {{ $sellerStats['member_since'] }}
                            </small>
                        </div>
                        
                        <a href="{{ route('marketplace.seller', $seller->username) }}" class="btn btn-outline-secondary w-100 mt-3">
                            @lang('View Profile')
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Similar Listings -->
        @if($similarListings->count() > 0)
        <div class="similar-listings mt-5">
            <h3 class="mb-4">@lang('Similar Listings')</h3>
            <div class="row g-4">
                @foreach($similarListings as $similar)
                    @include($activeTemplate . 'partials.listing_card', ['listing' => $similar])
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>
@endsection

@push('script')
<script>
    function shareUrl() {
        if (navigator.share) {
            navigator.share({
                title: '{{ $listing->title }}',
                url: window.location.href
            });
        } else {
            navigator.clipboard.writeText(window.location.href);
            alert('@lang("Link copied to clipboard!")');
        }
    }

    // Fullscreen Gallery Functions (for all listings with images)
    @if($listing->business_type !== 'domain' && $listing->images->count() > 0)
    const allImageUrls = @json($listing->images->map(function($img) {
        return getImage(getFilePath('listing') . '/' . $img->image);
    })->toArray());
    let fullscreenCurrentIndex = 0;

    window.openFullscreenGallery = function(startIndex = 0) {
        const galleryHtml = `
            <div id="fullscreenGallery" class="fullscreen-gallery">
                <div class="gallery-overlay" onclick="closeFullscreenGallery()"></div>
                <div class="gallery-content">
                    <button class="gallery-close" onclick="closeFullscreenGallery()">
                        <i class="las la-times"></i>
                    </button>

                    ${allImageUrls.length > 1 ? `
                    <button class="gallery-nav gallery-prev" onclick="navigateFullscreen(-1)">
                        <i class="las la-chevron-left"></i>
                    </button>
                    ` : ''}

                    <img id="fullscreenImage" src="" alt="" class="gallery-image">

                    ${allImageUrls.length > 1 ? `
                    <button class="gallery-nav gallery-next" onclick="navigateFullscreen(1)">
                        <i class="las la-chevron-right"></i>
                    </button>

                    <div class="gallery-indicators">
                        <span id="fullscreenCounter">1 / ${allImageUrls.length}</span>
                    </div>

                    <div class="gallery-thumbnails">
                        ${allImageUrls.map((imageUrl, index) => `
                            <img src="${imageUrl}"
                                 alt=""
                                 class="gallery-thumb ${index === startIndex ? 'active' : ''}"
                                 onclick="goToFullscreenImage(${index})"
                                 onerror="this.onerror=null; this.src='{{ asset('assets/images/default.png') }}';">
                        `).join('')}
                    </div>
                    ` : ''}
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', galleryHtml);
        document.body.style.overflow = 'hidden';

        // Initialize fullscreen gallery
        fullscreenCurrentIndex = startIndex;
        updateFullscreenImage();

        // Add keyboard support
        document.addEventListener('keydown', handleFullscreenKeydown);
    };

    window.closeFullscreenGallery = function() {
        const gallery = document.getElementById('fullscreenGallery');
        if (gallery) {
            gallery.remove();
            document.body.style.overflow = '';
            document.removeEventListener('keydown', handleFullscreenKeydown);
        }
    };

    window.navigateFullscreen = function(direction) {
        const newIndex = fullscreenCurrentIndex + direction;
        if (newIndex >= 0 && newIndex < allImageUrls.length) {
            fullscreenCurrentIndex = newIndex;
            updateFullscreenImage();
        }
    };

    window.goToFullscreenImage = function(index) {
        fullscreenCurrentIndex = index;
        updateFullscreenImage();
    };

    function updateFullscreenImage() {
        const fullscreenImage = document.getElementById('fullscreenImage');
        const fullscreenCounter = document.getElementById('fullscreenCounter');
        const galleryThumbs = document.querySelectorAll('.gallery-thumb');

        if (fullscreenImage && allImageUrls[fullscreenCurrentIndex]) {
            fullscreenImage.src = allImageUrls[fullscreenCurrentIndex];
            fullscreenImage.onerror = function() {
                this.onerror = null;
                this.src = '{{ asset("assets/images/default.png") }}';
            };
        }

        if (fullscreenCounter) {
            fullscreenCounter.textContent = `${fullscreenCurrentIndex + 1} / ${allImageUrls.length}`;
        }

        // Update thumbnail indicators
        galleryThumbs.forEach((thumb, index) => {
            thumb.classList.toggle('active', index === fullscreenCurrentIndex);
        });
    }

    function handleFullscreenKeydown(e) {
        switch(e.key) {
            case 'Escape':
                closeFullscreenGallery();
                break;
            case 'ArrowLeft':
                if (allImageUrls.length > 1) navigateFullscreen(-1);
                break;
            case 'ArrowRight':
                if (allImageUrls.length > 1) navigateFullscreen(1);
                break;
        }
    }

    // Touch gesture support for fullscreen
    let fullscreenTouchStartX = 0;
    let fullscreenTouchEndX = 0;

    document.addEventListener('touchstart', function(e) {
        if (document.getElementById('fullscreenGallery')) {
            fullscreenTouchStartX = e.changedTouches[0].screenX;
        }
    });

    document.addEventListener('touchend', function(e) {
        if (document.getElementById('fullscreenGallery')) {
            fullscreenTouchEndX = e.changedTouches[0].screenX;
            handleFullscreenSwipe();
        }
    });

    function handleFullscreenSwipe() {
        if (allImageUrls.length <= 1) return;
        const swipeThreshold = 50;
        if (fullscreenTouchEndX < fullscreenTouchStartX - swipeThreshold) {
            navigateFullscreen(1); // Swipe left - next
        } else if (fullscreenTouchEndX > fullscreenTouchStartX + swipeThreshold) {
            navigateFullscreen(-1); // Swipe right - previous
        }
    }
    @endif

    // Enhanced Image Gallery with Animations (only for non-domain listings with multiple images)
    @if($listing->business_type !== 'domain' && $listing->images->count() > 1)
    document.addEventListener('DOMContentLoaded', function() {
        const imageUrls = @json($listing->images->map(function($img) {
            return getImage(getFilePath('listing') . '/' . $img->image);
        })->toArray());
        let currentIndex = 0;
        const mainImage = document.getElementById('mainImage');
        const thumbnailItems = document.querySelectorAll('.thumbnail-item');
        const prevArrow = document.getElementById('prevImage');
        const nextArrow = document.getElementById('nextImage');
        const currentImageIndex = document.getElementById('currentImageIndex');
        const thumbnailStrip = document.getElementById('thumbnailStrip');

        // Image navigation functions
        function updateMainImage(index, animate = true) {
            if (!mainImage || index < 0 || index >= imageUrls.length) return;

            if (animate) {
                // Fade out current image
                mainImage.style.opacity = '0.5';

                setTimeout(() => {
                    mainImage.src = imageUrls[index];
                    mainImage.onload = function() {
                        mainImage.style.opacity = '1';
                    };
                    mainImage.onerror = function() {
                        this.onerror = null;
                        this.src = '{{ asset("assets/images/default.png") }}';
                        mainImage.style.opacity = '1';
                    };
                }, 150);
            } else {
                mainImage.src = imageUrls[index];
                mainImage.onerror = function() {
                    this.onerror = null;
                    this.src = '{{ asset("assets/images/default.png") }}';
                };
            }

            // Update thumbnails
            thumbnailItems.forEach((thumb, i) => {
                if (i === index) {
                    thumb.classList.add('active');
                    thumb.style.borderColor = '#007bff';
                    thumb.style.transform = 'scale(1.05)';
                } else {
                    thumb.classList.remove('active');
                    thumb.style.borderColor = 'transparent';
                    thumb.style.transform = 'scale(1)';
                }
            });

            // Update counter
            if (currentImageIndex) {
                currentImageIndex.textContent = index + 1;
            }

            // Update navigation arrows
            if (prevArrow) {
                prevArrow.style.display = index === 0 ? 'none' : 'block';
            }
            if (nextArrow) {
                nextArrow.style.display = index === imageUrls.length - 1 ? 'none' : 'block';
            }

            // Auto-scroll thumbnail into view
            const activeThumbnail = document.querySelector('.thumbnail-item.active');
            if (activeThumbnail) {
                activeThumbnail.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                    inline: 'center'
                });
            }
        }

        // Thumbnail click handlers
        thumbnailItems.forEach((thumbnail, index) => {
            thumbnail.addEventListener('click', function() {
                currentIndex = index;
                updateMainImage(currentIndex);
            });

            // Hover effects
            thumbnail.addEventListener('mouseenter', function() {
                if (!this.classList.contains('active')) {
                    this.style.transform = 'scale(1.1)';
                    this.style.borderColor = '#6c757d';
                }
            });

            thumbnail.addEventListener('mouseleave', function() {
                if (!this.classList.contains('active')) {
                    this.style.transform = 'scale(1)';
                    this.style.borderColor = 'transparent';
                }
            });
        });

        // Navigation arrows
        if (prevArrow) {
            prevArrow.addEventListener('click', function() {
                if (currentIndex > 0) {
                    currentIndex--;
                    updateMainImage(currentIndex);
                }
            });
        }

        if (nextArrow) {
            nextArrow.addEventListener('click', function() {
                if (currentIndex < images.length - 1) {
                    currentIndex++;
                    updateMainImage(currentIndex);
                }
            });
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft' && currentIndex > 0) {
                currentIndex--;
                updateMainImage(currentIndex);
            } else if (e.key === 'ArrowRight' && currentIndex < imageUrls.length - 1) {
                currentIndex++;
                updateMainImage(currentIndex);
            }
        });

        // Touch/swipe support for mobile
        let touchStartX = 0;
        let touchEndX = 0;

        mainImage.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        });

        mainImage.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });

        function handleSwipe() {
            const swipeThreshold = 50;
            if (touchEndX < touchStartX - swipeThreshold && currentIndex < imageUrls.length - 1) {
                // Swipe left - next image
                currentIndex++;
                updateMainImage(currentIndex);
            } else if (touchEndX > touchStartX + swipeThreshold && currentIndex > 0) {
                // Swipe right - previous image
                currentIndex--;
                updateMainImage(currentIndex);
            }
        }

        // Auto-play functionality (optional)
        let autoPlayInterval = null;
        const autoPlayDelay = 4000; // 4 seconds

        function startAutoPlay() {
            if (imageUrls.length > 1) {
                autoPlayInterval = setInterval(() => {
                    currentIndex = (currentIndex + 1) % imageUrls.length;
                    updateMainImage(currentIndex);
                }, autoPlayDelay);
            }
        }

        function stopAutoPlay() {
            if (autoPlayInterval) {
                clearInterval(autoPlayInterval);
                autoPlayInterval = null;
            }
        }

        // Pause auto-play on user interaction
        const galleryContainer = document.querySelector('.listing-images');
        if (galleryContainer) {
            ['click', 'touchstart', 'keydown'].forEach(event => {
                galleryContainer.addEventListener(event, stopAutoPlay, { passive: true });
            });
        }

        // Start auto-play after 5 seconds of inactivity
        let autoPlayTimeout = setTimeout(startAutoPlay, 5000);

        function resetAutoPlayTimer() {
            clearTimeout(autoPlayTimeout);
            stopAutoPlay();
            autoPlayTimeout = setTimeout(startAutoPlay, 5000);
        }

        // Reset auto-play timer on any user interaction
        if (galleryContainer) {
            galleryContainer.addEventListener('mouseenter', resetAutoPlayTimer);
            galleryContainer.addEventListener('touchstart', resetAutoPlayTimer, { passive: true });
        }

        // Preload images for better performance
        function preloadImages() {
            imageUrls.forEach((imageUrl, index) => {
                if (index !== 0) { // Skip first image as it's already loaded
                    const img = new Image();
                    img.src = imageUrl;
                }
            });
        }

        preloadImages();

        // Initialize gallery state
        updateMainImage(0, false);

        // Initialize gallery state
        updateMainImage(0, false);
    });
    @endif
</script>





@endpush

