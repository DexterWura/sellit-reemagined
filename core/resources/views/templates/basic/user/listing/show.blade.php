@extends($activeTemplate . 'layouts.frontend')

@section('content')
<div class="dashboard-body-part">
    <div class="row">
        <div class="col-lg-8">
            <!-- Listing Details Card -->
            <div class="card custom--card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ $listing->title }}</h5>
                    @php echo $listing->listingStatus @endphp
                </div>
                <div class="card-body">
                    <!-- Images Gallery -->
                    @if($listing->images->count() > 0)
                    <div class="listing-gallery mb-4">
                        <img src="{{ getImage(getFilePath('listing') . '/' . $listing->images->first()->image) }}" 
                             class="img-fluid rounded mb-3" style="max-height: 400px; width: 100%; object-fit: cover;">
                        @if($listing->images->count() > 1)
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach($listing->images as $image)
                                <img src="{{ getImage(getFilePath('listing') . '/' . $image->image) }}" 
                                     class="rounded" style="width: 80px; height: 60px; object-fit: cover; cursor: pointer;">
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Basic Info -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <strong>@lang('Listing Number'):</strong>
                            <span class="ms-2">{{ $listing->listing_number }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>@lang('Business Type'):</strong>
                            <span class="ms-2 badge badge--primary">{{ ucfirst(str_replace('_', ' ', $listing->business_type)) }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>@lang('Sale Type'):</strong>
                            <span class="ms-2">{{ $listing->sale_type === 'auction' ? __('Auction') : __('Fixed Price') }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>@lang('Created'):</strong>
                            <span class="ms-2">{{ showDateTime($listing->created_at, 'd M, Y') }}</span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <h6 class="mb-2">@lang('Description')</h6>
                        <p class="text-muted">{{ $listing->description }}</p>
                    </div>

                    @if($listing->rejection_reason)
                    <div class="alert alert-danger">
                        <strong>@lang('Rejection Reason'):</strong>
                        <p class="mb-0">{{ $listing->rejection_reason }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Questions -->
            @if($listing->questions->count() > 0)
            <div class="card custom--card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="las la-question-circle"></i> @lang('Questions')</h5>
                </div>
                <div class="card-body">
                    @foreach($listing->questions as $question)
                    <div class="question-item mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <strong>{{ $question->asker->username ?? 'User' }}</strong>
                            <small class="text-muted">{{ showDateTime($question->created_at, 'd M, Y') }}</small>
                        </div>
                        <p class="mb-2">{{ $question->question }}</p>
                        @if($question->answer)
                            <div class="bg-light p-2 rounded">
                                <small class="text-muted">@lang('Your Answer'):</small>
                                <p class="mb-0">{{ $question->answer }}</p>
                            </div>
                        @else
                            <form action="{{ route('user.listing.question.answer', $listing->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="question_id" value="{{ $question->id }}">
                                <div class="input-group">
                                    <input type="text" name="answer" class="form-control form-control-sm" 
                                           placeholder="@lang('Type your answer...')" required>
                                    <button type="submit" class="btn btn--primary btn-sm">@lang('Answer')</button>
                                </div>
                            </form>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Stats Card -->
            <div class="card custom--card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="las la-chart-bar"></i> @lang('Statistics')</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span>@lang('Views')</span>
                        <strong>{{ $stats['total_views'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>@lang('Watchers')</span>
                        <strong>{{ $stats['total_watchers'] }}</strong>
                    </div>
                    @if($listing->sale_type === 'auction')
                    <div class="d-flex justify-content-between mb-3">
                        <span>@lang('Total Bids')</span>
                        <strong>{{ $stats['total_bids'] }}</strong>
                    </div>
                    @else
                    <div class="d-flex justify-content-between mb-3">
                        <span>@lang('Offers Received')</span>
                        <strong>{{ $stats['total_offers'] }}</strong>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between">
                        <span>@lang('Questions')</span>
                        <strong>{{ $stats['total_questions'] }}</strong>
                    </div>
                </div>
            </div>

            <!-- Pricing Card -->
            <div class="card custom--card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="las la-dollar-sign"></i> @lang('Pricing')</h5>
                </div>
                <div class="card-body">
                    @if($listing->sale_type === 'auction')
                        <div class="mb-3">
                            <small class="text-muted">@lang('Starting Bid')</small>
                            <h4 class="mb-0">{{ showAmount($listing->starting_bid) }}</h4>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">@lang('Current Bid')</small>
                            <h4 class="mb-0 text--base">{{ showAmount($listing->current_bid ?: $listing->starting_bid) }}</h4>
                        </div>
                        @if($listing->reserve_price > 0)
                        <div class="mb-3">
                            <small class="text-muted">@lang('Reserve Price')</small>
                            <h5 class="mb-0">{{ showAmount($listing->reserve_price) }}</h5>
                            @if($listing->hasReserveBeenMet())
                                <small class="text-success">@lang('Reserve Met!')</small>
                            @else
                                <small class="text-warning">@lang('Reserve Not Met')</small>
                            @endif
                        </div>
                        @endif
                        @if($listing->buy_now_price > 0)
                        <div class="mb-3">
                            <small class="text-muted">@lang('Buy Now Price')</small>
                            <h5 class="mb-0 text-success">{{ showAmount($listing->buy_now_price) }}</h5>
                        </div>
                        @endif
                        @if($listing->auction_end)
                        <div class="mb-3">
                            <small class="text-muted">@lang('Auction Ends')</small>
                            <h6 class="mb-0">{{ showDateTime($listing->auction_end, 'd M, Y H:i') }}</h6>
                            <small class="{{ $listing->auction_end->isPast() ? 'text-danger' : 'text-info' }}">
                                {{ $listing->auction_end->diffForHumans() }}
                            </small>
                        </div>
                        @endif
                    @else
                        <div class="mb-3">
                            <small class="text-muted">@lang('Asking Price')</small>
                            <h3 class="mb-0 text--base">{{ showAmount($listing->asking_price) }}</h3>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card custom--card">
                <div class="card-body">
                    @if(in_array($listing->status, [\App\Constants\Status::LISTING_DRAFT, \App\Constants\Status::LISTING_PENDING, \App\Constants\Status::LISTING_REJECTED]))
                        <a href="{{ route('user.listing.edit', $listing->id) }}" class="btn btn--primary w-100 mb-2">
                            <i class="las la-edit"></i> @lang('Edit Listing')
                        </a>
                    @endif
                    @if($listing->status === \App\Constants\Status::LISTING_ACTIVE)
                        <a href="{{ route('marketplace.listing.show', $listing->slug) }}" class="btn btn--dark w-100 mb-2" target="_blank">
                            <i class="las la-external-link-alt"></i> @lang('View Public Page')
                        </a>
                    @endif
                    @if(in_array($listing->status, [\App\Constants\Status::LISTING_DRAFT, \App\Constants\Status::LISTING_PENDING, \App\Constants\Status::LISTING_ACTIVE]))
                        @if($listing->sale_type !== 'auction' || $listing->total_bids == 0)
                        <form action="{{ route('user.listing.cancel', $listing->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn--danger w-100" 
                                    onclick="return confirm('@lang('Are you sure you want to cancel this listing?')')">
                                <i class="las la-times"></i> @lang('Cancel Listing')
                            </button>
                        </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

