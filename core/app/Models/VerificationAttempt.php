<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationAttempt extends Model
{
    protected $fillable = [
        'domain_verification_id',
        'attempt_number',
        'method',
        'request_data',
        'response_data',
        'error_message',
        'ip_address',
        'user_agent',
        'attempted_at',
        'duration_ms',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'attempted_at' => 'datetime',
        'ip_address' => 'string',
    ];

    public $timestamps = false;

    /**
     * Relationship with domain verification
     */
    public function domainVerification()
    {
        return $this->belongsTo(DomainVerification::class);
    }

    /**
     * Create a new attempt log
     */
    public static function logAttempt(DomainVerification $verification, $method, $requestData = null, $responseData = null, $errorMessage = null, $durationMs = null)
    {
        return static::create([
            'domain_verification_id' => $verification->id,
            'attempt_number' => $verification->attempt_count,
            'method' => $method,
            'request_data' => $requestData,
            'response_data' => $responseData,
            'error_message' => $errorMessage,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'attempted_at' => now(),
            'duration_ms' => $durationMs,
        ]);
    }

    /**
     * Get successful attempts
     */
    public function scopeSuccessful($query)
    {
        return $query->whereNull('error_message');
    }

    /**
     * Get failed attempts
     */
    public function scopeFailed($query)
    {
        return $query->whereNotNull('error_message');
    }

    /**
     * Get attempts within time range
     */
    public function scopeWithinDays($query, $days)
    {
        return $query->where('attempted_at', '>=', now()->subDays($days));
    }
}
