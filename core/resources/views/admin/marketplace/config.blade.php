@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <form action="{{ route('admin.marketplace.config.update') }}" method="POST">
            @csrf
            
            <!-- Sale Types & Business Types -->
            <div class="card mb-4">
                <div class="card-header bg--primary">
                    <h5 class="card-title text-white mb-0">
                        <i class="las la-toggle-on me-2"></i>Marketplace Features
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <h6 class="mb-3">Sale Types</h6>
                            <div class="form-group">
                                <label class="form-check-label d-flex align-items-center gap-2">
                                    <input type="checkbox" name="allow_auctions" class="form-check-input" 
                                           {{ ($settings['allow_auctions'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <span>Allow Auctions</span>
                                </label>
                                <small class="text-muted">Enable auction-style listings</small>
                            </div>
                            <div class="form-group mt-3">
                                <label class="form-check-label d-flex align-items-center gap-2">
                                    <input type="checkbox" name="allow_fixed_price" class="form-check-input"
                                           {{ ($settings['allow_fixed_price'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <span>Allow Fixed Price</span>
                                </label>
                                <small class="text-muted">Enable fixed price listings</small>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <h6 class="mb-3">Business Types Allowed</h6>
                            <div class="form-group">
                                <label class="form-check-label d-flex align-items-center gap-2">
                                    <input type="checkbox" name="allow_domain_listings" class="form-check-input"
                                           {{ ($settings['allow_domain_listings'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <span>Domain Names</span>
                                </label>
                            </div>
                            <div class="form-group mt-2">
                                <label class="form-check-label d-flex align-items-center gap-2">
                                    <input type="checkbox" name="allow_website_listings" class="form-check-input"
                                           {{ ($settings['allow_website_listings'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <span>Websites</span>
                                </label>
                            </div>
                            <div class="form-group mt-2">
                                <label class="form-check-label d-flex align-items-center gap-2">
                                    <input type="checkbox" name="allow_social_media_listings" class="form-check-input"
                                           {{ ($settings['allow_social_media_listings'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <span>Social Media Accounts</span>
                                </label>
                            </div>
                            <div class="form-group mt-2">
                                <label class="form-check-label d-flex align-items-center gap-2">
                                    <input type="checkbox" name="allow_mobile_app_listings" class="form-check-input"
                                           {{ ($settings['allow_mobile_app_listings'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <span>Mobile Apps</span>
                                </label>
                            </div>
                            <div class="form-group mt-2">
                                <label class="form-check-label d-flex align-items-center gap-2">
                                    <input type="checkbox" name="allow_desktop_app_listings" class="form-check-input"
                                           {{ ($settings['allow_desktop_app_listings'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <span>Desktop Apps</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Auction Settings -->
            <div class="card mb-4">
                <div class="card-header bg--info">
                    <h5 class="card-title text-white mb-0">
                        <i class="las la-gavel me-2"></i>Auction Settings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="form-label">Minimum Auction Days <span class="text-danger">*</span></label>
                                <input type="number" name="min_auction_days" class="form-control" 
                                       value="{{ $settings['min_auction_days'] ?? 1 }}" min="1" max="30" required>
                                <small class="text-muted">Minimum number of days an auction can run</small>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="form-label">Maximum Auction Days <span class="text-danger">*</span></label>
                                <input type="number" name="max_auction_days" class="form-control" 
                                       value="{{ $settings['max_auction_days'] ?? 30 }}" min="1" max="365" required>
                                <small class="text-muted">Maximum number of days an auction can run</small>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="form-label">Auto Extend Minutes</label>
                                <input type="number" name="auto_extend_auction_minutes" class="form-control" 
                                       value="{{ $settings['auto_extend_auction_minutes'] ?? 10 }}" min="0" max="60">
                                <small class="text-muted">Extend auction by this many minutes when bid placed near end (0 to disable)</small>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="form-label">Bid Extension Threshold (Minutes)</label>
                                <input type="number" name="bid_extension_threshold_minutes" class="form-control" 
                                       value="{{ $settings['bid_extension_threshold_minutes'] ?? 5 }}" min="0" max="60">
                                <small class="text-muted">Extend auction if bid placed within this many minutes of end</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verification Settings -->
            <div class="card mb-4">
                <div class="card-header bg--warning">
                    <h5 class="card-title text-white mb-0">
                        <i class="las la-shield-alt me-2"></i>Domain/Website Verification
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="form-check-label d-flex align-items-center gap-2">
                                    <input type="checkbox" name="require_domain_verification" class="form-check-input"
                                           {{ ($settings['require_domain_verification'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <span>Require Domain Verification</span>
                                </label>
                                <small class="text-muted">Users must verify ownership of domain listings</small>
                            </div>
                            <div class="form-group mt-3">
                                <label class="form-check-label d-flex align-items-center gap-2">
                                    <input type="checkbox" name="require_website_verification" class="form-check-input"
                                           {{ ($settings['require_website_verification'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <span>Require Website Verification</span>
                                </label>
                                <small class="text-muted">Users must verify ownership of website listings</small>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Allowed Verification Methods</label>
                            @php
                                $methods = json_decode($settings['domain_verification_methods'] ?? '["txt_file","dns_record"]', true) ?? ['txt_file', 'dns_record'];
                            @endphp
                            <div class="form-group">
                                <label class="form-check-label d-flex align-items-center gap-2">
                                    <input type="checkbox" name="verification_txt_file" class="form-check-input"
                                           {{ in_array('txt_file', $methods) ? 'checked' : '' }}>
                                    <span>TXT File Upload</span>
                                </label>
                                <small class="text-muted">Upload a verification file to domain root</small>
                            </div>
                            <div class="form-group mt-2">
                                <label class="form-check-label d-flex align-items-center gap-2">
                                    <input type="checkbox" name="verification_dns_record" class="form-check-input"
                                           {{ in_array('dns_record', $methods) ? 'checked' : '' }}>
                                    <span>DNS TXT Record</span>
                                </label>
                                <small class="text-muted">Add a TXT record to DNS settings</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Listing Settings -->
            <div class="card mb-4">
                <div class="card-header bg--success">
                    <h5 class="card-title text-white mb-0">
                        <i class="las la-list me-2"></i>Listing Settings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="form-check-label d-flex align-items-center gap-2">
                                    <input type="checkbox" name="listing_approval_required" class="form-check-input"
                                           {{ ($settings['listing_approval_required'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <span>Require Admin Approval</span>
                                </label>
                                <small class="text-muted">All new listings must be approved by admin</small>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="form-label">Max Images Per Listing</label>
                                <input type="number" name="max_images_per_listing" class="form-control" 
                                       value="{{ $settings['max_images_per_listing'] ?? 10 }}" min="1" max="50">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="form-label">Minimum Description Length</label>
                                <input type="number" name="min_listing_description" class="form-control" 
                                       value="{{ $settings['min_listing_description'] ?? 100 }}" min="10" max="1000">
                                <small class="text-muted">Minimum characters required for listing description</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fee Settings -->
            <div class="card mb-4">
                <div class="card-header bg--dark">
                    <h5 class="card-title text-white mb-0">
                        <i class="las la-dollar-sign me-2"></i>Fee Settings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="form-label">Listing Fee (%)</label>
                                <div class="input-group">
                                    <input type="number" name="listing_fee_percentage" class="form-control" 
                                           value="{{ $settings['listing_fee_percentage'] ?? 0 }}" min="0" max="100" step="0.01">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">Fee charged when listing is sold (0 for no fee)</small>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="form-label">Escrow Fee (%)</label>
                                <div class="input-group">
                                    <input type="number" name="escrow_fee_percentage" class="form-control" 
                                           value="{{ $settings['escrow_fee_percentage'] ?? 5 }}" min="0" max="50" step="0.01">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">Escrow service fee percentage</small>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="form-label">Featured Listing Fee</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ gs('cur_sym') }}</span>
                                    <input type="number" name="featured_listing_fee" class="form-control" 
                                           value="{{ $settings['featured_listing_fee'] ?? 0 }}" min="0" step="0.01">
                                </div>
                                <small class="text-muted">One-time fee to feature a listing (0 for free)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn--primary w-100 h-45">
                        <i class="las la-save me-2"></i>Save Configuration
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
<a href="{{ route('admin.marketplace.sections') }}" class="btn btn-sm btn--dark">
    <i class="las la-layer-group me-1"></i> Homepage Sections
</a>
@endpush

