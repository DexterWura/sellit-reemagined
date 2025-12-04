<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DomainVerification extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'verification_data' => 'array',
        'last_attempt_at' => 'datetime',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_VERIFIED = 'verified';
    const STATUS_FAILED = 'failed';
    const STATUS_EXPIRED = 'expired';

    const METHOD_FILE = 'file';
    const METHOD_DNS = 'dns';

    // Legacy constants for backward compatibility
    const STATUS_PENDING_LEGACY = 0;
    const STATUS_VERIFIED_LEGACY = 1;
    const STATUS_FAILED_LEGACY = 2;
    const METHOD_TXT_FILE = 'txt_file';
    const METHOD_DNS_RECORD = 'dns_record';

    // Relationships
    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attempts()
    {
        return $this->hasMany(VerificationAttempt::class);
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

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED)
                    ->orWhere('expires_at', '<', now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where('status', '!=', self::STATUS_EXPIRED)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    // Helper Methods
    /**
     * Generate a cryptographically secure verification token
     */
    public static function generateToken()
    {
        return Str::random(40);
    }

    /**
     * Generate filename for verification file
     */
    public static function generateFilename()
    {
        return 'verification-' . Str::random(32) . '.txt';
    }

    /**
     * Generate DNS record name
     */
    public static function generateDnsRecordName()
    {
        return '_verify' . Str::random(16);
    }

    /**
     * Create a new verification for a domain
     */
    public static function createForDomain($domain, $userId, $method = self::METHOD_FILE, $listingId = null)
    {
        if (!$domain) {
            return null;
        }

        $token = self::generateToken();
        $verificationData = [];

        if ($method === self::METHOD_FILE) {
            $verificationData = [
                'filename' => self::generateFilename(),
                'expected_path' => "https://{$domain}/.well-known/{$token}.txt"
            ];
        } elseif ($method === self::METHOD_DNS) {
            $verificationData = [
                'record_name' => self::generateDnsRecordName(),
                'record_value' => $token
            ];
        }

        $verification = self::create([
            'domain' => $domain,
            'user_id' => $userId,
            'listing_id' => $listingId,
            'verification_method' => $method,
            'verification_token' => $token,
            'verification_data' => $verificationData,
            'status' => self::STATUS_PENDING,
            'attempt_count' => 0,
            'expires_at' => now()->addDays(7),
        ]);

        return $verification;
    }

    /**
     * Create a new verification for a listing (legacy method for backward compatibility)
     */
    public static function createForListing(Listing $listing, $method = self::METHOD_TXT_FILE)
    {
        $domain = self::extractDomain($listing);

        if (!$domain) {
            return null;
        }

        // Convert legacy method names
        $newMethod = $method === self::METHOD_TXT_FILE ? self::METHOD_FILE : self::METHOD_DNS;

        return self::createForDomain($domain, $listing->user_id, $newMethod, $listing->id);
    }

    /**
     * Extract domain from listing
     */
    public static function extractDomain(Listing $listing)
    {
        if ($listing->business_type === 'domain') {
            // Use stored domain_name if available, otherwise extract from URL
            if ($listing->domain_name) {
                return $listing->domain_name;
            }
            if ($listing->url) {
                return extractDomain($listing->url);
            }
        }

        if ($listing->business_type === 'website' && $listing->url) {
            // Use stored domain_name if available, otherwise extract from URL
            if ($listing->domain_name) {
                return $listing->domain_name;
            }
            return extractDomain($listing->url);
        }

        return null;
    }

    /**
     * Verify the domain ownership
     */
    public function verify()
    {
        $startTime = microtime(true);

        $this->attempt_count++;
        $this->last_attempt_at = now();
        $this->save();

        try {
            $result = false;

            if ($this->verification_method === self::METHOD_FILE) {
                $service = app(\App\Services\FileVerificationService::class);
                $result = $service->verifyDomain($this->domain, $this->verification_token);
            } elseif ($this->verification_method === self::METHOD_DNS) {
                $service = app(\App\Services\DNSVerificationService::class);
                $result = $service->verifyDomain($this->domain, $this->verification_token);
            }

            $duration = (microtime(true) - $startTime) * 1000;

            // Log the attempt
            VerificationAttempt::logAttempt(
                $this,
                $this->verification_method,
                ['domain' => $this->domain, 'token' => $this->verification_token],
                ['success' => $result],
                $result ? null : 'Verification failed',
                $duration
            );

            if ($result) {
                $this->status = self::STATUS_VERIFIED;
                $this->verified_at = now();
                $this->error_message = null;
                $this->save();
                $this->updateListingStatus();
            } else {
                // Check if max attempts reached
                if ($this->attempt_count >= \App\Models\VerificationSetting::getMaxAttempts()) {
                    $this->status = self::STATUS_FAILED;
                    $this->error_message = 'Maximum verification attempts exceeded';
                }
                $this->save();
            }

            return $result;
        } catch (\Exception $e) {
            $duration = (microtime(true) - $startTime) * 1000;

            VerificationAttempt::logAttempt(
                $this,
                $this->verification_method,
                ['domain' => $this->domain, 'token' => $this->verification_token],
                ['error' => $e->getMessage()],
                $e->getMessage(),
                $duration
            );

            $this->error_message = $e->getMessage();
            $this->save();
            return false;
        }
    }

    /**
     * Verify via TXT file upload
     * Simplified, more reliable verification
     */
    protected function verifyTxtFile()
    {
        // Try these locations in order (most common first)
        $urls = [
            'https://' . $this->domain . '/' . $this->txt_filename,
            'http://' . $this->domain . '/' . $this->txt_filename,
            'https://' . $this->domain . '/.well-known/' . $this->txt_filename,
            'http://' . $this->domain . '/.well-known/' . $this->txt_filename,
        ];

        $lastError = null;
        $foundContent = null;

        foreach ($urls as $url) {
            try {
                // Use cURL for better control
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 3,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_CONNECTTIMEOUT => 8,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; VerificationBot/1.0)',
                    CURLOPT_HTTPHEADER => [
                        'Accept: text/plain, */*',
                    ],
                ]);
                
                $content = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);
                
                // Check if we got a successful response
                if ($content !== false && $httpCode >= 200 && $httpCode < 300) {
                    $foundContent = $content;
                    
                    // Simple normalization: trim whitespace and newlines
                    $cleanContent = trim($content);
                    $cleanContent = preg_replace('/\s+/', '', $cleanContent); // Remove all whitespace
                    
                    $cleanToken = trim($this->verification_token);
                    $cleanToken = preg_replace('/\s+/', '', $cleanToken); // Remove all whitespace
                    
                    // Compare
                    if ($cleanContent === $cleanToken) {
                        $this->markAsVerified();
                        return true;
                    }
                } else {
                    if ($curlError) {
                        $lastError = "cURL error: $curlError";
                    } else {
                        $lastError = "HTTP $httpCode";
                    }
                }
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                continue;
            }
        }

        // Build helpful error message
        if ($foundContent !== null) {
            $this->error_message = "File found but content doesn't match. Expected: {$this->verification_token} | Found: " . substr(trim($foundContent), 0, 50);
        } else {
            $this->error_message = "File not accessible. Please upload the file to: https://{$this->domain}/{$this->txt_filename}";
            if ($lastError) {
                $this->error_message .= " (Error: $lastError)";
            }
        }
        
        $this->save();
        return false;
    }

    /**
     * Verify via DNS TXT record
     * Simplified, more reliable DNS verification
     */
    protected function verifyDnsRecord()
    {
        try {
            // Try with subdomain prefix first (most common format)
            $recordName = $this->dns_record_name . '.' . $this->domain;
            $records = @dns_get_record($recordName, DNS_TXT);
            
            if ($records && is_array($records)) {
                foreach ($records as $record) {
                    if (isset($record['txt'])) {
                        $recordValue = trim($record['txt']);
                        // Remove quotes if present (some DNS providers add them)
                        $recordValue = trim($recordValue, '"');
                        
                        if ($recordValue === $this->verification_token) {
                            $this->markAsVerified();
                            return true;
                        }
                    }
                }
            }

            // Also try at domain root (some DNS providers require this)
            $records = @dns_get_record($this->domain, DNS_TXT);
            if ($records && is_array($records)) {
                foreach ($records as $record) {
                    if (isset($record['txt'])) {
                        $recordValue = trim($record['txt']);
                        $recordValue = trim($recordValue, '"');
                        
                        if ($recordValue === $this->verification_token) {
                            $this->markAsVerified();
                            return true;
                        }
                    }
                }
            }

            // Clear error message
            $this->error_message = "DNS TXT record not found. Please add a TXT record:\nName: {$this->dns_record_name}\nValue: {$this->verification_token}\n\nNote: DNS changes can take 5 minutes to 48 hours to propagate.";
            $this->save();
            return false;

        } catch (\Exception $e) {
            $this->error_message = 'DNS lookup failed: ' . $e->getMessage() . '. Please check your DNS settings and try again.';
            $this->save();
            return false;
        }
    }

    /**
     * Update listing status when verification is successful
     */
    protected function updateListingStatus()
    {
        if ($this->listing) {
            $this->listing->is_verified = true;
            $this->listing->verification_notes = 'Domain ownership verified via ' . $this->verification_method;
            $this->listing->save();
        }
    }

    /**
     * Check if verification is still valid/not expired
     */
    public function isValid()
    {
        return $this->status === self::STATUS_VERIFIED
            && (!$this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Check if verification has expired
     */
    public function isExpired()
    {
        return $this->status === self::STATUS_EXPIRED
            || ($this->expires_at && $this->expires_at->isPast());
    }

    /**
     * Get remaining attempts
     */
    public function getRemainingAttempts()
    {
        $maxAttempts = \App\Models\VerificationSetting::getMaxAttempts();
        return max(0, $maxAttempts - $this->attempt_count);
    }

    /**
     * Check if can attempt verification
     */
    public function canAttempt()
    {
        return !$this->isExpired()
            && $this->status !== self::STATUS_VERIFIED
            && $this->status !== self::STATUS_FAILED
            && $this->getRemainingAttempts() > 0;
    }

    /**
     * Get instructions for the user
     */
    public function getInstructions()
    {
        if ($this->verification_method === self::METHOD_FILE) {
            $filename = $this->verification_data['filename'] ?? 'verification.txt';

            return [
                'method' => 'File Upload',
                'steps' => [
                    '1. Create a text file named: <strong>' . $filename . '</strong>',
                    '2. The file should contain ONLY this text: <code>' . $this->verification_token . '</code>',
                    '3. Upload the file to: <code>https://' . $this->domain . '/.well-known/' . $filename . '</code>',
                    '4. Or upload to: <code>https://' . $this->domain . '/' . $filename . '</code>',
                    '5. Click "Verify Now" to check',
                ],
                'download_content' => $this->verification_token,
                'download_filename' => $filename,
                'expected_url' => 'https://' . $this->domain . '/.well-known/' . $filename,
            ];
        } else {
            $recordName = $this->verification_data['record_name'] ?? '_verify';

            return [
                'method' => 'DNS TXT Record',
                'steps' => [
                    '1. Go to your domain registrar\'s DNS settings',
                    '2. Add a new TXT record',
                    '3. Set the Name/Host to: <code>' . $recordName . '</code>',
                    '4. Set the Value/Content to: <code>' . $this->verification_token . '</code>',
                    '5. Wait for DNS propagation (can take up to 24-48 hours)',
                    '6. Click "Verify Now" to check',
                ],
                'record_type' => 'TXT',
                'record_name' => $recordName,
                'record_value' => $this->verification_token,
            ];
        }
    }
}

