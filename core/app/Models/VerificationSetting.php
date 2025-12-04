<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationSetting extends Model
{
    protected $fillable = [
        'require_verification',
        'allowed_methods',
        'max_verification_attempts',
        'verification_timeout_seconds',
    ];

    protected $casts = [
        'require_verification' => 'boolean',
        'allowed_methods' => 'array',
        'max_verification_attempts' => 'integer',
        'verification_timeout_seconds' => 'integer',
    ];

    /**
     * Get the current verification settings (singleton pattern)
     */
    public static function current()
    {
        return static::first() ?? static::create([
            'require_verification' => false,
            'allowed_methods' => ['file', 'dns'],
            'max_verification_attempts' => 5,
            'verification_timeout_seconds' => 300,
        ]);
    }

    /**
     * Check if domain verification is required
     */
    public static function isRequired()
    {
        return static::current()->require_verification;
    }

    /**
     * Get allowed verification methods
     */
    public static function getAllowedMethods()
    {
        return static::current()->allowed_methods ?? ['file', 'dns'];
    }

    /**
     * Check if a method is allowed
     */
    public static function isMethodAllowed($method)
    {
        return in_array($method, static::getAllowedMethods());
    }

    /**
     * Get maximum verification attempts
     */
    public static function getMaxAttempts()
    {
        return static::current()->max_verification_attempts ?? 5;
    }

    /**
     * Get verification timeout in seconds
     */
    public static function getTimeoutSeconds()
    {
        return static::current()->verification_timeout_seconds ?? 300;
    }
}
