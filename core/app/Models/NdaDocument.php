<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NdaDocument extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'signed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSigned($query)
    {
        return $query->where('status', 'signed');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'signed')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->where('status', 'signed')
                    ->where('expires_at', '<=', now());
            });
    }

    public function isActive()
    {
        return $this->status === 'signed' 
            && ($this->expires_at === null || $this->expires_at > now());
    }

    public function isExpired()
    {
        return $this->status === 'expired' 
            || ($this->status === 'signed' && $this->expires_at && $this->expires_at <= now());
    }
}

