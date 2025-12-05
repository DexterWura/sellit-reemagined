<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OwnershipValidationService
{
    /**
     * Validate ownership for domain/website using DNS TXT record
     */
    public function validateDnsTxt($domain, $token)
    {
        try {
            // Remove protocol and www
            $domain = $this->normalizeDomain($domain);
            
            // Check DNS TXT records
            $txtRecords = dns_get_record($domain, DNS_TXT);
            
            if (!$txtRecords) {
                return [
                    'success' => false,
                    'message' => 'No TXT records found for this domain'
                ];
            }
            
            // Look for our verification token in TXT records
            $verificationString = "marketplace-verification={$token}";
            
            foreach ($txtRecords as $record) {
                if (isset($record['txt']) && strpos($record['txt'], $verificationString) !== false) {
                    return [
                        'success' => true,
                        'message' => 'Ownership verified via DNS TXT record'
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Verification token not found in DNS TXT records'
            ];
            
        } catch (\Exception $e) {
            Log::error('DNS TXT validation error', [
                'domain' => $domain,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error checking DNS records: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate ownership for domain/website using HTML meta tag
     */
    public function validateHtmlMeta($url, $token)
    {
        try {
            // Ensure URL has protocol
            if (!preg_match('/^https?:\/\//', $url)) {
                $url = 'https://' . $url;
            }
            
            // Fetch the page
            $response = Http::timeout(10)->get($url);
            
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Could not fetch the website. Please ensure the URL is accessible.'
                ];
            }
            
            $html = $response->body();
            
            // Look for meta tag
            $verificationString = "marketplace-verification={$token}";
            $pattern = '/<meta\s+name=["\']marketplace-verification["\']\s+content=["\']' . preg_quote($token, '/') . '["\']/i';
            
            if (preg_match($pattern, $html)) {
                return [
                    'success' => true,
                    'message' => 'Ownership verified via HTML meta tag'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Verification meta tag not found in the HTML. Please add: <meta name="marketplace-verification" content="' . $token . '">'
            ];
            
        } catch (\Exception $e) {
            Log::error('HTML Meta validation error', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error fetching website: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate ownership for domain/website using file upload
     */
    public function validateFileUpload($url, $filename, $token)
    {
        try {
            // Ensure URL has protocol
            if (!preg_match('/^https?:\/\//', $url)) {
                $url = 'https://' . $url;
            }
            
            // Construct file URL
            $fileUrl = rtrim($url, '/') . '/' . ltrim($filename, '/');
            
            // Fetch the file
            $response = Http::timeout(10)->get($fileUrl);
            
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Could not access the verification file. Please ensure the file is accessible at: ' . $fileUrl
                ];
            }
            
            $fileContent = $response->body();
            
            // Check if token is in file content
            if (strpos($fileContent, $token) !== false) {
                return [
                    'success' => true,
                    'message' => 'Ownership verified via file upload'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Verification token not found in the file. Please ensure the file contains: ' . $token
            ];
            
        } catch (\Exception $e) {
            Log::error('File upload validation error', [
                'url' => $url,
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error accessing verification file: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate ownership for social media using OAuth login
     * If user can successfully login with their social account, they own it
     */
    public function validateSocialMediaOAuth($platform, $oauthUser, $expectedHandle = null)
    {
        try {
            // Map platform names to socialite providers
            $platformMap = [
                'instagram' => 'instagram',
                'facebook' => 'facebook',
                'twitter' => 'twitter',
                'youtube' => 'google', // YouTube uses Google OAuth
                'tiktok' => 'tiktok',
                'linkedin' => 'linkedin',
                'google' => 'google',
            ];
            
            $provider = $platformMap[strtolower($platform)] ?? strtolower($platform);
            
            // Verify the OAuth user data
            if (!$oauthUser || !isset($oauthUser->id)) {
                return [
                    'success' => false,
                    'message' => 'Invalid OAuth response. Please try again.'
                ];
            }
            
            // If expected handle is provided, verify it matches
            if ($expectedHandle) {
                $normalizedHandle = str_replace('@', '', strtolower(trim($expectedHandle)));
                $oauthUsername = strtolower(trim($oauthUser->nickname ?? $oauthUser->name ?? ''));
                $oauthEmail = strtolower(trim($oauthUser->email ?? ''));
                
                // Check if handle matches username or email
                $handleMatches = (
                    $oauthUsername === $normalizedHandle ||
                    strpos($oauthUsername, $normalizedHandle) !== false ||
                    $oauthEmail === $normalizedHandle ||
                    (isset($oauthUser->user) && strtolower($oauthUser->user['username'] ?? '') === $normalizedHandle)
                );
                
                if (!$handleMatches) {
                    return [
                        'success' => false,
                        'message' => 'The logged-in account does not match the provided handle. Please login with the correct account.'
                    ];
                }
            }
            
            // OAuth login successful = ownership verified
            Log::info('Social media ownership verified via OAuth', [
                'platform' => $platform,
                'provider' => $provider,
                'oauth_id' => $oauthUser->id,
                'username' => $oauthUser->nickname ?? $oauthUser->name ?? 'N/A',
                'email' => $oauthUser->email ?? 'N/A'
            ]);
            
            return [
                'success' => true,
                'message' => 'Ownership verified successfully via ' . ucfirst($platform) . ' login',
                'oauth_data' => [
                    'id' => $oauthUser->id,
                    'username' => $oauthUser->nickname ?? $oauthUser->name ?? null,
                    'email' => $oauthUser->email ?? null,
                    'name' => $oauthUser->name ?? null,
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Social media OAuth validation error', [
                'platform' => $platform,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error validating social media ownership: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate a unique verification token
     */
    public function generateToken($userId, $assetUrl)
    {
        $uniqueString = $userId . '_' . $assetUrl . '_' . time();
        return hash('sha256', $uniqueString);
    }
    
    /**
     * Normalize domain (remove protocol, www, etc.)
     */
    public function normalizeDomain($domain)
    {
        // Remove protocol
        $domain = preg_replace('#^https?://#', '', $domain);
        // Remove www
        $domain = preg_replace('#^www\.#', '', $domain);
        // Remove path
        $domain = preg_replace('#/.*$#', '', $domain);
        // Remove query string
        $domain = preg_replace('#\?.*$#', '', $domain);
        // Remove fragment
        $domain = preg_replace('#\#.*$#', '', $domain);
        
        return trim($domain);
    }
    
    /**
     * Get available validation methods for a business type
     */
    public function getAvailableMethods($businessType)
    {
        switch ($businessType) {
            case 'domain':
            case 'website':
                return [
                    'dns_txt' => [
                        'name' => 'DNS TXT Record',
                        'description' => 'Add a TXT record to your domain\'s DNS settings',
                        'instructions' => 'Add a TXT record with name: @ and value: marketplace-verification={TOKEN}'
                    ],
                    'html_meta' => [
                        'name' => 'HTML Meta Tag',
                        'description' => 'Add a meta tag to your website\'s HTML',
                        'instructions' => 'Add this tag to your website\'s <head> section: <meta name="marketplace-verification" content="{TOKEN}">'
                    ],
                    'file_upload' => [
                        'name' => 'File Upload',
                        'description' => 'Upload a verification file to your website',
                        'instructions' => 'Create a file named marketplace-verification.txt in your website root containing: {TOKEN}'
                    ]
                ];
                
            case 'social_media_account':
                return [
                    'oauth_login' => [
                        'name' => 'Login with Social Account',
                        'description' => 'Verify ownership by logging in with your social media account',
                        'instructions' => 'Click the button below to login with your account. If login is successful, ownership will be verified automatically.'
                    ]
                ];
                
            default:
                return [];
        }
    }
}

