<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;

class SocialMediaVerification extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    const STATUS_PENDING = 0;
    const STATUS_VERIFIED = 1;
    const STATUS_FAILED = 2;

    const PLATFORM_INSTAGRAM = 'instagram';
    const PLATFORM_YOUTUBE = 'youtube';
    const PLATFORM_TIKTOK = 'tiktok';
    const PLATFORM_TWITTER = 'twitter';
    const PLATFORM_FACEBOOK = 'facebook';

    // Relationships
    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeVerified($query)
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Create verification for a listing
     */
    public static function createForListing(Listing $listing, $platform, $accountId = null)
    {
        // Check if verification already exists
        $verification = self::where('listing_id', $listing->id)
            ->where('platform', $platform)
            ->first();

        if ($verification) {
            return $verification;
        }

        $verification = new self();
        $verification->listing_id = $listing->id;
        $verification->user_id = $listing->user_id;
        $verification->platform = $platform;
        $verification->account_id = $accountId;
        $verification->status = self::STATUS_PENDING;
        $verification->save();

        return $verification;
    }

    /**
     * Create verification without listing (for pre-verification)
     */
    public static function createForUser($userId, $platform, $accountId = null)
    {
        $verification = new self();
        $verification->user_id = $userId;
        $verification->platform = $platform;
        $verification->account_id = $accountId;
        $verification->status = self::STATUS_PENDING;
        $verification->save();

        return $verification;
    }

    /**
     * Mark as verified
     */
    public function markAsVerified($accountId = null, $accountUsername = null)
    {
        $this->status = self::STATUS_VERIFIED;
        $this->verified_at = now();
        if ($accountId) {
            $this->account_id = $accountId;
        }
        if ($accountUsername) {
            $this->account_username = $accountUsername;
        }
        $this->save();

        // Update listing
        if ($this->listing) {
            $this->listing->is_verified = true;
            $this->listing->verification_notes = 'Social media account verified via ' . ucfirst($this->platform) . ' OAuth';
            $this->listing->save();
        }
    }

    /**
     * Mark as failed
     */
    public function markAsFailed($errorMessage = null)
    {
        $this->status = self::STATUS_FAILED;
        if ($errorMessage) {
            $this->error_message = $errorMessage;
        }
        $this->save();
    }
}

