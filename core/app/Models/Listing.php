<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Listing extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_verified' => 'boolean',
        'requires_verification' => 'boolean',
        'revenue_verified' => 'boolean',
        'traffic_verified' => 'boolean',
        'is_featured' => 'boolean',
        'is_confidential' => 'boolean',
        'requires_nda' => 'boolean',
        'featured_until' => 'datetime',
        'auction_start' => 'datetime',
        'auction_end' => 'datetime',
        'approved_at' => 'datetime',
        'sold_at' => 'datetime',
        'domain_expiry' => 'date',
        'traffic_sources' => 'array',
        'monetization_methods' => 'array',
        'assets_included' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function listingCategory()
    {
        return $this->belongsTo(ListingCategory::class);
    }

    public function images()
    {
        return $this->hasMany(ListingImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ListingImage::class)->where('is_primary', true);
    }

    public function metrics()
    {
        return $this->hasMany(ListingMetric::class)->orderBy('period_date', 'desc');
    }

    public function bids()
    {
        return $this->hasMany(Bid::class)->orderBy('amount', 'desc');
    }

    public function winningBid()
    {
        return $this->hasOne(Bid::class)->where('status', Status::BID_WON);
    }

    public function highestBid()
    {
        return $this->hasOne(Bid::class)->orderBy('amount', 'desc');
    }

    public function domainVerification()
    {
        return $this->hasOne(DomainVerification::class);
    }

    public function highestBidder()
    {
        return $this->belongsTo(User::class, 'highest_bidder_id');
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function acceptedOffer()
    {
        return $this->hasOne(Offer::class)->where('status', Status::OFFER_ACCEPTED);
    }

    public function watchlistUsers()
    {
        return $this->belongsToMany(User::class, 'watchlist')->withTimestamps();
    }

    public function watchlist()
    {
        return $this->hasMany(Watchlist::class);
    }

    public function views()
    {
        return $this->hasMany(ListingView::class);
    }

    public function questions()
    {
        return $this->hasMany(ListingQuestion::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function escrow()
    {
        return $this->belongsTo(Escrow::class);
    }

    public function ndaDocuments()
    {
        return $this->hasMany(NdaDocument::class);
    }

    public function signedNdas()
    {
        return $this->hasMany(NdaDocument::class)->active();
    }

    public function hasSignedNda($userId = null)
    {
        $userId = $userId ?? auth()->id();
        if (!$userId) {
            return false;
        }
        
        return $this->ndaDocuments()
            ->where('user_id', $userId)
            ->active()
            ->exists();
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', Status::LISTING_DRAFT);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', Status::LISTING_PENDING);
    }

    public function scopeActive($query)
    {
        // Exclude listings that are in escrow process (have escrow_id set)
        // These will be marked as SOLD when escrow is completed
        return $query->where('status', Status::LISTING_ACTIVE)
                     ->whereNull('escrow_id');
    }

    public function scopeSold($query)
    {
        return $query->where('status', Status::LISTING_SOLD);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', Status::LISTING_EXPIRED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', Status::LISTING_CANCELLED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', Status::LISTING_REJECTED);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->where('featured_until', '>', now());
    }

    public function scopeAuction($query)
    {
        return $query->where('sale_type', 'auction');
    }

    public function scopeFixedPrice($query)
    {
        return $query->where('sale_type', 'fixed_price');
    }

    public function scopeByBusinessType($query, $type)
    {
        return $query->where('business_type', $type);
    }

    public function scopeActiveAuctions($query)
    {
        return $query->auction()
            ->active()
            ->where('auction_end', '>', now());
    }

    public function scopeEndingSoon($query, $hours = 24)
    {
        return $query->activeAuctions()
            ->where('auction_end', '<=', now()->addHours($hours));
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%")
                ->orWhere('tagline', 'LIKE', "%{$search}%")
                ->orWhere('domain_name', 'LIKE', "%{$search}%")
                ->orWhere('niche', 'LIKE', "%{$search}%")
                ->orWhere('listing_number', 'LIKE', "%{$search}%")
                ->orWhereHas('seller', function ($sellerQuery) use ($search) {
                    $sellerQuery->where('username', 'LIKE', "%{$search}%")
                        ->orWhere('fullname', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('listingCategory', function ($catQuery) use ($search) {
                    $catQuery->where('name', 'LIKE', "%{$search}%");
                });
        });
    }

    // Accessors
    public function listingStatus(): Attribute
    {
        return new Attribute(
            get: function () {
                $statusClasses = [
                    Status::LISTING_DRAFT => ['badge--secondary', 'Draft'],
                    Status::LISTING_PENDING => ['badge--warning', 'Pending Approval'],
                    Status::LISTING_ACTIVE => ['badge--success', 'Active'],
                    Status::LISTING_SOLD => ['badge--primary', 'Sold'],
                    Status::LISTING_EXPIRED => ['badge--dark', 'Expired'],
                    Status::LISTING_CANCELLED => ['badge--danger', 'Cancelled'],
                    Status::LISTING_REJECTED => ['badge--danger', 'Rejected'],
                ];

                $class = $statusClasses[$this->status] ?? ['badge--secondary', 'Unknown'];
                return '<span class="badge ' . $class[0] . '">' . trans($class[1]) . '</span>';
            }
        );
    }

    public function currentPrice(): Attribute
    {
        return new Attribute(
            get: fn() => $this->sale_type === 'auction'
                ? ($this->current_bid > 0 ? $this->current_bid : $this->starting_bid)
                : $this->asking_price
        );
    }

    public function isAuction(): Attribute
    {
        return new Attribute(
            get: fn() => $this->sale_type === 'auction'
        );
    }

    public function isAuctionEnded(): Attribute
    {
        return new Attribute(
            get: fn() => $this->is_auction && $this->auction_end && $this->auction_end->isPast()
        );
    }

    public function isAuctionActive(): Attribute
    {
        return new Attribute(
            get: fn() => $this->is_auction
                && $this->status === Status::LISTING_ACTIVE
                && $this->auction_start
                && $this->auction_start->isPast()
                && $this->auction_end
                && $this->auction_end->isFuture()
        );
    }

    public function timeRemaining(): Attribute
    {
        return new Attribute(
            get: function () {
                if (!$this->is_auction || !$this->auction_end) {
                    return null;
                }
                return $this->auction_end->diffForHumans();
            }
        );
    }

    public function minimumBid(): Attribute
    {
        return new Attribute(
            get: fn() => $this->current_bid > 0
                ? $this->current_bid + $this->bid_increment
                : $this->starting_bid
        );
    }

    // Helper Methods
    public function isWatchedBy($userId)
    {
        return $this->watchlist()->where('user_id', $userId)->exists();
    }

    public function hasReserveBeenMet()
    {
        if ($this->reserve_price <= 0) {
            return true;
        }
        return $this->current_bid >= $this->reserve_price;
    }

    public function canBuyNow()
    {
        return $this->buy_now_price > 0 && $this->status === Status::LISTING_ACTIVE;
    }

    public function canPlaceBid()
    {
        return $this->is_auction_active;
    }

    public function canMakeOffer()
    {
        return $this->sale_type === 'fixed_price' && $this->status === Status::LISTING_ACTIVE;
    }

    public function incrementViews()
    {
        $this->increment('view_count');
    }
}

