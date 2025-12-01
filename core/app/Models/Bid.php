<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Bid extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_auto_bid' => 'boolean',
        'is_buy_now' => 'boolean',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bidder()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', Status::BID_ACTIVE);
    }

    public function scopeOutbid($query)
    {
        return $query->where('status', Status::BID_OUTBID);
    }

    public function scopeWinning($query)
    {
        return $query->where('status', Status::BID_WINNING);
    }

    public function scopeWon($query)
    {
        return $query->where('status', Status::BID_WON);
    }

    public function scopeLost($query)
    {
        return $query->where('status', Status::BID_LOST);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', Status::BID_CANCELLED);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Accessors
    public function bidStatus(): Attribute
    {
        return new Attribute(
            get: function () {
                $statusClasses = [
                    Status::BID_ACTIVE => ['badge--info', 'Active'],
                    Status::BID_OUTBID => ['badge--warning', 'Outbid'],
                    Status::BID_WINNING => ['badge--success', 'Winning'],
                    Status::BID_WON => ['badge--primary', 'Won'],
                    Status::BID_LOST => ['badge--dark', 'Lost'],
                    Status::BID_CANCELLED => ['badge--danger', 'Cancelled'],
                ];

                $class = $statusClasses[$this->status] ?? ['badge--secondary', 'Unknown'];
                return '<span class="badge ' . $class[0] . '">' . trans($class[1]) . '</span>';
            }
        );
    }
}

