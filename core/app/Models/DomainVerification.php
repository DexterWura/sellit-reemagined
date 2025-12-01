<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;
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
    public static function generateToken()
    {
        return 'escrow-verify-' . Str::random(32);
    }

    public static function generateTxtFilename()
    {
        return 'escrow-verification-' . Str::random(16) . '.txt';
    }

    public static function generateDnsRecordName()
    {
        return '_escrow-verify';
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
            return $listing->domain_name;
        }

        if ($listing->business_type === 'website' && $listing->url) {
            $parsed = parse_url($listing->url);
            return $parsed['host'] ?? null;
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
     */
    protected function verifyTxtFile()
    {
        $urls = [
            'https://' . $this->domain . '/' . $this->txt_filename,
            'https://' . $this->domain . '/.well-known/' . $this->txt_filename,
            'http://' . $this->domain . '/' . $this->txt_filename,
            'http://' . $this->domain . '/.well-known/' . $this->txt_filename,
        ];

        foreach ($urls as $url) {
            try {
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 10,
                        'follow_location' => true,
                    ],
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ]);

                $content = @file_get_contents($url, false, $context);
                
                if ($content !== false && trim($content) === $this->verification_token) {
                    $this->markAsVerified();
                    return true;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        $this->error_message = 'Verification file not found or token mismatch. Please ensure the file is accessible at: https://' . $this->domain . '/' . $this->txt_filename;
        $this->save();
        return false;
    }

    /**
     * Verify via DNS TXT record
     */
    protected function verifyDnsRecord()
    {
        $recordName = $this->dns_record_name . '.' . $this->domain;
        
        try {
            $records = dns_get_record($recordName, DNS_TXT);
            
            if ($records) {
                foreach ($records as $record) {
                    if (isset($record['txt']) && trim($record['txt']) === $this->verification_token) {
                        $this->markAsVerified();
                        return true;
                    }
                }
            }

            // Also try without subdomain prefix
            $records = dns_get_record($this->domain, DNS_TXT);
            if ($records) {
                foreach ($records as $record) {
                    if (isset($record['txt']) && trim($record['txt']) === $this->verification_token) {
                        $this->markAsVerified();
                        return true;
                    }
                }
            }

            $this->error_message = 'DNS TXT record not found. Please add a TXT record with name "' . $this->dns_record_name . '" and value "' . $this->verification_token . '"';
            $this->save();
            return false;

        } catch (\Exception $e) {
            $this->error_message = 'DNS lookup failed: ' . $e->getMessage();
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

