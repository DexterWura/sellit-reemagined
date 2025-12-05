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

    /**
     * Boot the model and add event listeners for data integrity
     */
    protected static function boot()
    {
        parent::boot();

        // Validate data before creating
        static::creating(function ($listing) {
            $listing->validateDataIntegrity();
        });

        // Validate data before saving (light validation for updates)
        static::saving(function ($listing) {
            $listing->validateUpdateDataIntegrity();
        });

        // Clean up related data when deleting
        static::deleting(function ($listing) {
            // Cancel all active bids
            $listing->bids()->whereIn('status', [0, 2])->update(['status' => 5]); // BID_CANCELLED

            // Cancel escrow if exists and not completed
            if ($listing->escrow && !in_array($listing->escrow->status, [1, 8])) { // Not completed or disputed
                $listing->escrow->update(['status' => 9]); // ESCROW_CANCELLED
            }
        });
    }

    /**
     * Validate data integrity before creating
     */
    protected function validateDataIntegrity()
    {
        // Financial validation
        $financialFields = [
            'asking_price', 'starting_bid', 'reserve_price', 'buy_now_price',
            'current_bid', 'final_price', 'monthly_revenue', 'monthly_profit',
            'yearly_revenue', 'yearly_profit'
        ];

        foreach ($financialFields as $field) {
            if ($this->$field < 0) {
                throw new \InvalidArgumentException("{$field} cannot be negative");
            }
        }

        // Business logic validation moved to controller level for listing creation only

        // Auction validation
        if ($this->sale_type === 'auction') {
            if ($this->starting_bid <= 0) {
                throw new \InvalidArgumentException("Starting bid must be greater than 0 for auctions");
            }

            if ($this->reserve_price > 0 && $this->reserve_price < $this->starting_bid) {
                throw new \InvalidArgumentException("Reserve price cannot be less than starting bid");
            }

            if ($this->buy_now_price > 0 && $this->buy_now_price < $this->starting_bid) {
                throw new \InvalidArgumentException("Buy now price cannot be less than starting bid");
            }
        }

        // Fixed price validation
        if ($this->sale_type === 'fixed_price' && $this->asking_price <= 0) {
            throw new \InvalidArgumentException("Asking price must be greater than 0 for fixed price listings");
        }

        // Status validation
        $validStatuses = [0, 1, 2, 3, 4, 5, 6]; // DRAFT, PENDING, ACTIVE, SOLD, EXPIRED, CANCELLED, REJECTED
        if (!in_array($this->status, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid listing status");
        }

        // Business type validation
        $validBusinessTypes = ['domain', 'website', 'social_media_account', 'mobile_app', 'desktop_app'];
        if (!in_array($this->business_type, $validBusinessTypes)) {
            throw new \InvalidArgumentException("Invalid business type");
        }
    }

    /**
     * Validate data integrity before updating (lighter validation)
     */
    protected function validateUpdateDataIntegrity()
    {
        // Only validate fields that are being updated or basic integrity checks

        // Financial validation for negative values
        $financialFields = [
            'asking_price', 'starting_bid', 'reserve_price', 'buy_now_price',
            'current_bid', 'final_price', 'monthly_revenue', 'monthly_profit',
            'yearly_revenue', 'yearly_profit'
        ];

        foreach ($financialFields as $field) {
            // Only validate if the field is being set and is negative
            if ($this->isDirty($field) && $this->$field < 0) {
                throw new \InvalidArgumentException("{$field} cannot be negative");
            }
        }

        // Status validation
        $validStatuses = [
            Status::LISTING_DRAFT, Status::LISTING_PENDING, Status::LISTING_ACTIVE,
            Status::LISTING_SOLD, Status::LISTING_EXPIRED, Status::LISTING_CANCELLED,
            Status::LISTING_REJECTED
        ];
        if ($this->isDirty('status') && !in_array($this->status, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid listing status");
        }

        // Business type validation (only if being changed)
        if ($this->isDirty('business_type')) {
            $validBusinessTypes = ['domain', 'website', 'social_media_account', 'mobile_app', 'desktop_app'];
            if (!in_array($this->business_type, $validBusinessTypes)) {
                throw new \InvalidArgumentException("Invalid business type");
            }
        }
    }

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

    public function socialMediaVerification()
    {
        return $this->hasOne(SocialMediaVerification::class);
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
        // Exclude listings that are in active escrow process
        // Show listings where:
        // 1. escrow_id is null (no escrow), OR
        // 2. escrow doesn't exist (orphaned escrow_id), OR
        // 3. escrow exists but is cancelled (escrow was cancelled, listing can be shown again)
        // Hide listings where escrow exists and is active (not accepted, accepted, disputed, or completed)
        // Note: Completed escrows should have listings marked as SOLD, not shown as active
        return $query->where('status', Status::LISTING_ACTIVE)
                     ->where(function ($q) {
                         $q->whereNull('escrow_id')
                           ->orWhereDoesntHave('escrow') // Escrow doesn't exist (orphaned escrow_id)
                           ->orWhereHas('escrow', function ($escrowQuery) {
                               // Only show if escrow is cancelled (listing can be shown again)
                               $escrowQuery->where('status', Status::ESCROW_CANCELLED);
                           });
                     });
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
        $searchTerm = trim($search);
        if (empty($searchTerm)) {
            return $query;
        }

        return $query->where(function ($q) use ($searchTerm) {
            $q->where('title', 'LIKE', "%{$searchTerm}%")
                ->orWhere('tagline', 'LIKE', "%{$searchTerm}%")
                ->orWhere('domain_name', 'LIKE', "%{$searchTerm}%")
                ->orWhere('niche', 'LIKE', "%{$searchTerm}%")
                ->orWhere('listing_number', 'LIKE', "%{$searchTerm}%")
                ->orWhereHas('seller', function ($sellerQuery) use ($searchTerm) {
                    $sellerQuery->where('username', 'LIKE', "%{$searchTerm}%")
                        ->orWhereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", ["%{$searchTerm}%"]);
                })
                ->orWhereHas('listingCategory', function ($catQuery) use ($searchTerm) {
                    $catQuery->where('name', 'LIKE', "%{$searchTerm}%");
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

    /**
     * Optimized method to get active listings with eager loading
     */
    public function scopeActiveWithDetails($query)
    {
        return $query->active()
            ->with([
                'user:id,username,firstname,lastname,email',
                'listingCategory:id,name,slug',
                'primaryImage:id,listing_id,image_path',
                'seller:id,username,firstname,lastname'
            ])
            ->select([
                'id', 'listing_number', 'title', 'slug', 'tagline', 'business_type',
                'sale_type', 'asking_price', 'starting_bid', 'current_bid', 'reserve_price',
                'buy_now_price', 'user_id', 'listing_category_id', 'status', 'is_featured',
                'featured_until', 'auction_start', 'auction_end', 'domain_name', 'website_url',
                'monthly_revenue', 'monthly_profit', 'view_count', 'created_at'
            ]);
    }

    /**
     * Get listings for marketplace browse page with pagination
     */
    public function scopeForBrowse($query, $filters = [])
    {
        $query = $query->activeWithDetails();

        // Apply filters
        if (!empty($filters['business_type'])) {
            $query->where('business_type', $filters['business_type']);
        }

        if (!empty($filters['sale_type'])) {
            $query->where('sale_type', $filters['sale_type']);
        }

        if (!empty($filters['category'])) {
            $query->where('listing_category_id', $filters['category']);
        }

        if (!empty($filters['min_price'])) {
            if ($filters['sale_type'] === 'auction') {
                $query->where(function ($q) use ($filters) {
                    $q->where('current_bid', '>=', $filters['min_price'])
                      ->orWhere('starting_bid', '>=', $filters['min_price']);
                });
            } else {
                $query->where('asking_price', '>=', $filters['min_price']);
            }
        }

        if (!empty($filters['max_price'])) {
            if ($filters['sale_type'] === 'auction') {
                $query->where(function ($q) use ($filters) {
                    $q->where('current_bid', '<=', $filters['max_price'])
                      ->orWhere('starting_bid', '<=', $filters['max_price']);
                });
            } else {
                $query->where('asking_price', '<=', $filters['max_price']);
            }
        }

        // Apply sorting
        $sortBy = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';

        switch ($sortBy) {
            case 'price':
                if ($filters['sale_type'] === 'auction') {
                    $query->orderByRaw('COALESCE(current_bid, starting_bid) ' . $sortDirection);
                } else {
                    $query->orderBy('asking_price', $sortDirection);
                }
                break;
            case 'views':
                $query->orderBy('view_count', $sortDirection);
                break;
            case 'ending_soon':
                $query->auction()->orderBy('auction_end', 'asc');
                break;
            default:
                $query->orderBy($sortBy, $sortDirection);
        }

        return $query;
    }
}

