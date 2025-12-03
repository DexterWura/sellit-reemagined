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
        'last_attempt_at' => 'datetime',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    const STATUS_PENDING = 0;
    const STATUS_VERIFIED = 1;
    const STATUS_FAILED = 2;

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
        return $query->where('expires_at', '<', now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    // Helper Methods
    /**
     * Generate a simple, reliable verification token
     * Format: Simple alphanumeric string (no special chars that could cause issues)
     */
    public static function generateToken()
    {
        // Generate a simple token: 40 characters, alphanumeric only
        // This avoids issues with special characters, encoding, etc.
        return Str::random(40);
    }

    /**
     * Generate a simple filename for verification file
     */
    public static function generateTxtFilename()
    {
        // Simple filename: verification-{random}.txt
        return 'verification-' . Str::random(16) . '.txt';
    }

    /**
     * Generate DNS record name
     */
    public static function generateDnsRecordName()
    {
        // Simple DNS record name: _verify{random}
        // Using underscore prefix is standard for TXT records
        return '_verify' . Str::random(8);
    }

    /**
     * Create a new verification for a listing
     */
    public static function createForListing(Listing $listing, $method = self::METHOD_TXT_FILE)
    {
        $domain = self::extractDomain($listing);
        
        if (!$domain) {
            return null;
        }

        $token = self::generateToken();
        
        $verification = self::updateOrCreate(
            ['listing_id' => $listing->id],
            [
                'user_id' => $listing->user_id,
                'domain' => $domain,
                'verification_method' => $method,
                'verification_token' => $token,
                'txt_filename' => $method === self::METHOD_TXT_FILE ? self::generateTxtFilename() : null,
                'dns_record_name' => $method === self::METHOD_DNS_RECORD ? self::generateDnsRecordName() : null,
                'dns_record_value' => $method === self::METHOD_DNS_RECORD ? $token : null,
                'status' => self::STATUS_PENDING,
                'attempts' => 0,
                'error_message' => null,
                'expires_at' => now()->addDays(7),
            ]
        );

        return $verification;
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
        $this->increment('attempts');
        $this->last_attempt_at = now();
        $this->save();

        try {
            if ($this->verification_method === self::METHOD_TXT_FILE) {
                return $this->verifyTxtFile();
            } else {
                return $this->verifyDnsRecord();
            }
        } catch (\Exception $e) {
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
     * Mark as verified
     */
    public function markAsVerified()
    {
        $this->status = self::STATUS_VERIFIED;
        $this->verified_at = now();
        $this->error_message = null;
        $this->save();

        // Update listing
        if ($this->listing) {
            $this->listing->is_verified = true;
            $this->listing->verification_notes = 'Domain ownership verified via ' . ($this->verification_method === self::METHOD_TXT_FILE ? 'file upload' : 'DNS record');
            $this->listing->save();
        }
    }

    /**
     * Mark as failed
     */
    public function markAsFailed($reason = null)
    {
        $this->status = self::STATUS_FAILED;
        $this->error_message = $reason;
        $this->save();
    }

    /**
     * Check if verification is still valid/not expired
     */
    public function isValid()
    {
        return $this->status !== self::STATUS_FAILED 
            && (!$this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Get instructions for the user
     */
    public function getInstructions()
    {
        if ($this->verification_method === self::METHOD_TXT_FILE) {
            return [
                'method' => 'File Upload',
                'steps' => [
                    '1. Download or create a text file named: <strong>' . $this->txt_filename . '</strong>',
                    '2. The file should contain ONLY this text: <code>' . $this->verification_token . '</code>',
                    '3. Upload the file to your domain root: <code>https://' . $this->domain . '/' . $this->txt_filename . '</code>',
                    '4. Or upload to: <code>https://' . $this->domain . '/.well-known/' . $this->txt_filename . '</code>',
                    '5. Click "Verify Now" to check',
                ],
                'download_content' => $this->verification_token,
                'download_filename' => $this->txt_filename,
            ];
        } else {
            return [
                'method' => 'DNS TXT Record',
                'steps' => [
                    '1. Go to your domain registrar\'s DNS settings',
                    '2. Add a new TXT record',
                    '3. Set the Name/Host to: <code>' . $this->dns_record_name . '</code>',
                    '4. Set the Value/Content to: <code>' . $this->verification_token . '</code>',
                    '5. Wait for DNS propagation (can take up to 24-48 hours)',
                    '6. Click "Verify Now" to check',
                ],
                'record_type' => 'TXT',
                'record_name' => $this->dns_record_name,
                'record_value' => $this->verification_token,
            ];
        }
    }
}

