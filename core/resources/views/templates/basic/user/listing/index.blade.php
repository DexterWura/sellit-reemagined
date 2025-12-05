@extends($activeTemplate . 'layouts.frontend')
@section('content')
<section class="section bg--light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">@lang('My Listings')</h4>
            <a href="{{ route('user.listing.create') }}" class="btn btn--base">
                <i class="las la-plus"></i> @lang('Create Listing')
            </a>
        </div>
        
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">@lang('All Status')</option>
                            <option value="0" @selected(request('status') === '0')>@lang('Draft')</option>
                            <option value="1" @selected(request('status') === '1')>@lang('Pending')</option>
                            <option value="2" @selected(request('status') === '2')>@lang('Active')</option>
                            <option value="3" @selected(request('status') === '3')>@lang('Sold')</option>
                            <option value="4" @selected(request('status') === '4')>@lang('Expired')</option>
                            <option value="6" @selected(request('status') === '6')>@lang('Rejected')</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="business_type" class="form-select">
                            <option value="">@lang('All Types')</option>
                            <option value="domain" @selected(request('business_type') == 'domain')>@lang('Domain')</option>
                            <option value="website" @selected(request('business_type') == 'website')>@lang('Website')</option>
                            <option value="social_media_account" @selected(request('business_type') == 'social_media_account')>@lang('Social Media')</option>
                            <option value="mobile_app" @selected(request('business_type') == 'mobile_app')>@lang('Mobile App')</option>
                            <option value="desktop_app" @selected(request('business_type') == 'desktop_app')>@lang('Desktop App')</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="@lang('Search...')">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn--base w-100"><i class="las la-filter"></i> @lang('Filter')</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Listings Table -->
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-responsive--lg custom--table">
                    <thead>
                        <tr>
                            <th>@lang('Listing')</th>
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
                                        @else
                                            <div class="bg-light me-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 45px; border-radius: 5px;">
                                                <i class="las la-image text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ Str::limit($listing->title, 40) }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $listing->listing_number }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ ucfirst(str_replace('_', ' ', $listing->business_type)) }}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        {{ $listing->sale_type === 'auction' ? __('Auction') : __('Fixed Price') }}
                                    </small>
                                </td>
                                <td>
                                    @if($listing->sale_type === 'auction')
                                        <strong>{{ showAmount($listing->current_bid ?: $listing->starting_bid) }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $listing->total_bids }} @lang('bids')</small>
                                    @else
                                        <strong>{{ showAmount($listing->asking_price) }}</strong>
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
                                    @if($listing->rejection_reason)
                                        <br>
                                        <small class="text-danger">{{ Str::limit($listing->rejection_reason, 30) }}</small>
                                    @endif
                                    @if($listing->requires_verification && !$listing->is_verified)
                                        <br>
                                        @if($listing->domainVerification)
                                            @if($listing->domainVerification->status == 0)
                                                <span class="badge badge--warning">
                                                    <i class="las la-shield-alt"></i> @lang('Pending Verification')
                                                </span>
                                            @elseif($listing->domainVerification->status == 2)
                                                <span class="badge badge--danger">
                                                    <i class="las la-times-circle"></i> @lang('Verification Failed')
                                                </span>
                                            @endif
                                        @else
                                            <span class="badge badge--info">
                                                <i class="las la-info-circle"></i> @lang('Needs Verification')
                                            </span>
                                        @endif
                                    @elseif($listing->is_verified)
                                        <br>
                                        <span class="badge badge--success">
                                            <i class="las la-check-circle"></i> @lang('Verified')
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('user.listing.show', $listing->id) }}" class="btn btn-sm btn-outline-primary" title="@lang('View')">
                                            <i class="las la-eye"></i>
                                        </a>
                                        @if($listing->requires_verification && !$listing->is_verified && $listing->domainVerification)
                                            <a href="{{ route('user.verification.show', $listing->domainVerification->id) }}" 
                                               class="btn btn-sm btn-outline-success" title="@lang('Verify Domain')">
                                                <i class="las la-shield-alt"></i>
                                            </a>
                                        @endif
                                        @if($listing->requires_verification && !$listing->is_verified)
                                            <a href="{{ route('user.verification.index') }}" class="btn btn-sm btn-outline-warning" title="@lang('Verify Ownership')">
                                                <i class="las la-shield-alt"></i>
                                            </a>
                                        @endif
                                        @if(in_array($listing->status, [\App\Constants\Status::LISTING_DRAFT, \App\Constants\Status::LISTING_PENDING, \App\Constants\Status::LISTING_REJECTED]))
                                            <a href="{{ route('user.listing.edit', $listing->id) }}" class="btn btn-sm btn-outline-secondary" title="@lang('Edit')">
                                                <i class="las la-edit"></i>
                                            </a>
                                        @endif
                                        @if(in_array($listing->status, [\App\Constants\Status::LISTING_DRAFT, \App\Constants\Status::LISTING_PENDING, \App\Constants\Status::LISTING_ACTIVE]))
                                            <form action="{{ route('user.listing.cancel', $listing->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('@lang('Are you sure?')')" title="@lang('Cancel')">
                                                    <i class="las la-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <img src="{{ asset('assets/images/empty_list.png') }}" alt="" style="max-width: 120px;">
                                    <p class="mt-2 text-muted">@lang('No listings found')</p>
                                    <a href="{{ route('user.listing.create') }}" class="btn btn--base btn-sm">
                                        <i class="las la-plus"></i> @lang('Create Your First Listing')
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($listings->hasPages())
            <div class="mt-4">
                {{ $listings->links() }}
            </div>
        @endif
    </div>
</section>
@endsection

