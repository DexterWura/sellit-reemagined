@extends($activeTemplate . 'layouts.frontend')
@section('content')
<section class="section bg--light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header bg--base">
                        <h5 class="mb-0 text-white"><i class="las la-edit"></i> @lang('Edit Listing')</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('user.listing.update', $listing->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- Basic Information -->
                            <div class="section-header mb-3">
                                <h6 class="text--base"><i class="las la-info-circle"></i> @lang('Basic Information')</h6>
                            </div>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-8">
                                    <label class="form-label">@lang('Title') <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" value="{{ old('title', $listing->title) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">@lang('Business Type')</label>
                                    <input type="text" class="form-control" value="{{ ucfirst(str_replace('_', ' ', $listing->business_type)) }}" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">@lang('Category')</label>
                                    <select name="listing_category_id" class="form-select">
                                        <option value="">@lang('Select Category')</option>
                                        @foreach($listingCategories as $category)
                                            @if($category->business_type == $listing->business_type)
                                                <option value="{{ $category->id }}" @selected($listing->listing_category_id == $category->id)>
                                                    {{ $category->name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">@lang('Tagline')</label>
                                    <input type="text" name="tagline" class="form-control" value="{{ old('tagline', $listing->tagline) }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">@lang('Description') <span class="text-danger">*</span></label>
                                    <textarea name="description" class="form-control" rows="6" required>{{ old('description', $listing->description) }}</textarea>
                                </div>
                            </div>
                            
                            <!-- Pricing -->
                            <div class="section-header mb-3">
                                <h6 class="text--base"><i class="las la-dollar-sign"></i> @lang('Pricing')</h6>
                            </div>
                            
                            <div class="row g-3 mb-4">
                                @if($listing->sale_type === 'fixed_price')
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('Asking Price') <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{ gs()->cur_sym }}</span>
                                            <input type="number" name="asking_price" class="form-control" 
                                                   value="{{ old('asking_price', $listing->asking_price) }}" step="0.01" min="1" required>
                                        </div>
                                    </div>
                                @else
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('Starting Bid') <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{ gs()->cur_sym }}</span>
                                            <input type="number" name="starting_bid" class="form-control" 
                                                   value="{{ old('starting_bid', $listing->starting_bid) }}" step="0.01" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('Reserve Price')</label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{ gs()->cur_sym }}</span>
                                            <input type="number" name="reserve_price" class="form-control" 
                                                   value="{{ old('reserve_price', $listing->reserve_price) }}" step="0.01" min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('Buy Now Price')</label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{ gs()->cur_sym }}</span>
                                            <input type="number" name="buy_now_price" class="form-control" 
                                                   value="{{ old('buy_now_price', $listing->buy_now_price) }}" step="0.01" min="0">
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Business-Specific Fields -->
                            @if($listing->business_type == 'domain')
                                <div class="section-header mb-3">
                                    <h6 class="text--base"><i class="las la-globe"></i> @lang('Domain Details')</h6>
                                </div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label">@lang('Domain Name')</label>
                                        <input type="text" name="domain_name" class="form-control" 
                                               value="{{ old('domain_name', $listing->domain_name) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">@lang('Registrar')</label>
                                        <input type="text" name="domain_registrar" class="form-control" 
                                               value="{{ old('domain_registrar', $listing->domain_registrar) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">@lang('Expiry Date')</label>
                                        <input type="date" name="domain_expiry" class="form-control" 
                                               value="{{ old('domain_expiry', $listing->domain_expiry?->format('Y-m-d')) }}">
                                    </div>
                                </div>
                            @elseif($listing->business_type == 'website')
                                <div class="section-header mb-3">
                                    <h6 class="text--base"><i class="las la-laptop"></i> @lang('Website Details')</h6>
                                </div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label">@lang('Website URL')</label>
                                        <input type="url" name="url" class="form-control" 
                                               value="{{ old('url', $listing->url) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">@lang('Niche')</label>
                                        <input type="text" name="niche" class="form-control" 
                                               value="{{ old('niche', $listing->niche) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">@lang('Tech Stack')</label>
                                        <input type="text" name="tech_stack" class="form-control" 
                                               value="{{ old('tech_stack', $listing->tech_stack) }}">
                                    </div>
                                </div>
                            @elseif($listing->business_type == 'social_media_account')
                                <div class="section-header mb-3">
                                    <h6 class="text--base"><i class="las la-share-alt"></i> @lang('Social Media Details')</h6>
                                </div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('Platform')</label>
                                        <select name="platform" class="form-select">
                                            @foreach($platforms as $key => $name)
                                                <option value="{{ $key }}" @selected($listing->platform == $key)>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('Account URL')</label>
                                        <input type="url" name="url" class="form-control" value="{{ old('url', $listing->url) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">@lang('Followers Count')</label>
                                        <input type="number" name="followers_count" class="form-control" 
                                               value="{{ old('followers_count', $listing->followers_count) }}" min="0">
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Financials -->
                            <div class="section-header mb-3">
                                <h6 class="text--base"><i class="las la-chart-line"></i> @lang('Financials')</h6>
                            </div>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">@lang('Monthly Revenue')</label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ gs()->cur_sym }}</span>
                                        <input type="number" name="monthly_revenue" class="form-control" 
                                               value="{{ old('monthly_revenue', $listing->monthly_revenue) }}" step="0.01" min="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">@lang('Monthly Profit')</label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ gs()->cur_sym }}</span>
                                        <input type="number" name="monthly_profit" class="form-control" 
                                               value="{{ old('monthly_profit', $listing->monthly_profit) }}" step="0.01" min="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">@lang('Monthly Visitors')</label>
                                    <input type="number" name="monthly_visitors" class="form-control" 
                                           value="{{ old('monthly_visitors', $listing->monthly_visitors) }}" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">@lang('Page Views/Month')</label>
                                    <input type="number" name="monthly_page_views" class="form-control" 
                                           value="{{ old('monthly_page_views', $listing->monthly_page_views) }}" min="0">
                                </div>
                            </div>
                            
                            <!-- Existing Images -->
                            @if($listing->images->count() > 0)
                            <div class="section-header mb-3">
                                <h6 class="text--base"><i class="las la-images"></i> @lang('Current Images')</h6>
                            </div>
                            <div class="row g-3 mb-4">
                                @foreach($listing->images as $image)
                                    <div class="col-md-3 position-relative">
                                        <img src="{{ getImage(getFilePath('listing') . '/' . $image->image) }}" 
                                             class="img-fluid rounded" style="height: 150px; width: 100%; object-fit: cover;">
                                        @if($image->is_primary)
                                            <span class="badge bg-success position-absolute top-0 start-0 m-2">@lang('Primary')</span>
                                        @endif
                                        <div class="mt-2 d-flex gap-2">
                                            @if(!$image->is_primary)
                                                <button type="button" class="btn btn-sm btn-outline-primary set-primary-btn" 
                                                        data-id="{{ $image->id }}">
                                                    @lang('Set Primary')
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-image-btn" 
                                                    data-id="{{ $image->id }}">
                                                <i class="las la-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @endif
                            
                            <!-- Add More Images -->
                            <div class="section-header mb-3">
                                <h6 class="text--base"><i class="las la-images"></i> @lang('Add More Images')</h6>
                            </div>
                            
                            <div class="mb-4">
                                <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                                <small class="text-muted">@lang('Upload additional images. Max 2MB each.')</small>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('user.listing.index') }}" class="btn btn-secondary">@lang('Cancel')</a>
                                <button type="submit" class="btn btn--base">
                                    <i class="las la-save"></i> @lang('Update Listing')
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('style')
<style>
    /* Fix form label colors - ensure all labels are visible with black text */
    .form-label {
        color: #000000 !important;
    }
    
    label.form-label {
        color: #000000 !important;
    }
</style>
@endpush

@push('script')
<script>
    $(document).ready(function() {
        // Delete image
        $('.delete-image-btn').on('click', function() {
            if (!confirm('@lang("Are you sure you want to delete this image?")')) return;
            
            const btn = $(this);
            const id = btn.data('id');
            
            $.ajax({
                url: '{{ route("user.listing.image.delete", "") }}/' + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        btn.closest('.col-md-3').remove();
                        notify('success', response.message);
                    }
                },
                error: function() {
                    notify('error', 'Failed to delete image');
                }
            });
        });
        
        // Set primary image
        $('.set-primary-btn').on('click', function() {
            const btn = $(this);
            const id = btn.data('id');
            
            $.ajax({
                url: '{{ route("user.listing.image.primary", "") }}/' + id,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                error: function() {
                    notify('error', 'Failed to set primary image');
                }
            });
        });
    });
</script>
@endpush

