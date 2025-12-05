<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DomainVerificationController extends Controller
{
    /**
     * Generate verification data for a domain
     */
    public function generateVerification(Request $request)
    {
        try {
            // Log the incoming request
            \Log::info('Domain verification generate request', [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
                'headers' => $request->headers->all()
            ]);

            $request->validate([
                'domain' => 'required|string|max:255',
                'method' => 'required|in:txt_file,dns_record',
            ]);

            $originalDomain = $request->domain;
            $domain = $this->normalizeDomain($originalDomain);
            $method = $request->method;

            \Log::info('Domain normalization', [
                'original' => $originalDomain,
                'normalized' => $domain
            ]);

            // Check if verification is enabled
            $domainVerificationRequired = MarketplaceSetting::requireDomainVerification();
            $websiteVerificationRequired = MarketplaceSetting::requireWebsiteVerification();

            \Log::info('Verification settings check', [
                'domain_required' => $domainVerificationRequired,
                'website_required' => $websiteVerificationRequired
            ]);

            if (!$domainVerificationRequired && !$websiteVerificationRequired) {
                \Log::warning('Domain verification not enabled');
                return response()->json([
                    'success' => false,
                    'message' => 'Domain verification is not enabled'
                ], 400);
            }

            // Basic domain validation - just check it's not empty and looks like a domain
            if (empty($domain)) {
                \Log::warning('Domain is empty', ['original' => $originalDomain]);
                return response()->json([
                    'success' => false,
                    'message' => 'Domain name is required'
                ], 400);
            }

            if (!preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $domain)) {
                \Log::warning('Domain regex validation failed', ['domain' => $domain]);
                return response()->json([
                    'success' => false,
                    'message' => 'Please enter a valid domain name (e.g., example.com)'
                ], 400);
            }

            if (strlen($domain) > 253) {
                \Log::warning('Domain too long', ['domain' => $domain, 'length' => strlen($domain)]);
                return response()->json([
                    'success' => false,
                    'message' => 'Domain name is too long'
                ], 400);
            }

            // Additional check - domain shouldn't contain spaces or special chars at start/end
            if (preg_match('/^\s|\s$|[^\w.-]/', $domain)) {
                \Log::warning('Domain contains invalid characters', ['domain' => $domain]);
                return response()->json([
                    'success' => false,
                    'message' => 'Domain name contains invalid characters'
                ], 400);
            }

            // Check if user already has a verified domain
            $verifiedCacheKey = 'verified_domain_' . auth()->id() . '_' . $domain;
            $verifiedData = Cache::get($verifiedCacheKey);

            if ($verifiedData) {
                \Log::info('Domain already verified', ['domain' => $domain]);
                return response()->json([
                    'success' => false,
                    'message' => 'This domain is already verified'
                ], 400);
            }

            // Generate verification data
            $verificationData = $this->generateVerificationData($domain, $method);

            // Store in cache with user ID (expires in 24 hours)
            $cacheKey = 'verification_' . auth()->id() . '_' . $domain;
            $cacheResult = Cache::put($cacheKey, $verificationData, 86400);

            \Log::info('Verification data stored in cache', [
                'cache_key' => $cacheKey,
                'cache_result' => $cacheResult,
                'data_keys' => array_keys($verificationData)
            ]);

            $response = [
                'success' => true,
                'token' => $verificationData['token'],
                'filename' => $verificationData['filename'] ?? null,
                'expected_url' => $verificationData['expected_url'] ?? null,
                'content' => $verificationData['token'],
                'dns_name' => $verificationData['dns_name'] ?? null,
                'dns_value' => $verificationData['token'],
            ];

            \Log::info('Verification generation successful', [
                'domain' => $domain,
                'method' => $method,
                'response_keys' => array_keys($response)
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('Domain verification generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all() ?? []
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate verification data. Please try again.'
            ], 500);
        }
    }

    /**
     * Verify domain ownership
     */
    public function verifyDomain(Request $request)
    {
        try {
            \Log::info('Domain verification check request', [
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            $request->validate([
                'domain' => 'required|string',
                'method' => 'required|in:txt_file,dns_record',
                'token' => 'required|string',
                'filename' => 'required_if:method,txt_file|string',
                'dns_name' => 'required_if:method,dns_record|string',
            ]);

            $originalDomain = $request->domain;
            $domain = $this->normalizeDomain($originalDomain);
            $method = $request->method;
            $token = trim($request->token);

            \Log::info('Domain verification check', [
                'original_domain' => $originalDomain,
                'normalized_domain' => $domain,
                'method' => $method,
                'token_length' => strlen($token)
            ]);

            $cacheKey = 'verification_' . auth()->id() . '_' . $domain;
            $verificationData = Cache::get($cacheKey);

            \Log::info('Cache check', [
                'cache_key' => $cacheKey,
                'cache_data_exists' => !empty($verificationData),
                'cached_token_matches' => $verificationData ? ($verificationData['token'] === $token) : false
            ]);

            if (!$verificationData) {
                \Log::warning('No verification data found in cache', ['cache_key' => $cacheKey]);
                return response()->json([
                    'success' => false,
                    'message' => 'Verification session expired. Please start verification again.'
                ], 400);
            }

            if ($verificationData['token'] !== $token) {
                \Log::warning('Token mismatch', [
                    'expected' => substr($verificationData['token'], 0, 10) . '...',
                    'received' => substr($token, 0, 10) . '...'
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Verification token is invalid. Please start verification again.'
                ], 400);
            }

            $success = false;
            $message = '';

            if ($method === 'txt_file') {
                \Log::info('Verifying TXT file', [
                    'domain' => $domain,
                    'filename' => $request->filename
                ]);
                $success = $this->verifyTxtFile($domain, $request->filename, $token);
                $message = $success ? 'Domain verified successfully!' : 'Verification file not found or content doesn\'t match.';
            } elseif ($method === 'dns_record') {
                \Log::info('Verifying DNS record', [
                    'domain' => $domain,
                    'dns_name' => $request->dns_name
                ]);
                $success = $this->verifyDnsRecord($domain, $request->dns_name, $token);
                $message = $success ? 'Domain verified successfully!' : 'DNS record not found or doesn\'t match.';
            }

            \Log::info('Verification result', [
                'success' => $success,
                'method' => $method,
                'domain' => $domain
            ]);

            if ($success) {
                // Store successful verification
                $verifiedCacheKey = 'verified_domain_' . auth()->id() . '_' . $domain;
                $verifiedData = [
                    'domain' => $domain,
                    'verified_at' => now(),
                    'method' => $method,
                    'token' => $token
                ];

                $cacheResult = Cache::put($verifiedCacheKey, $verifiedData, 2592000); // 30 days

                \Log::info('Verification stored successfully', [
                    'cache_key' => $verifiedCacheKey,
                    'cache_result' => $cacheResult
                ]);
            }

            return response()->json([
                'success' => $success,
                'message' => $message,
            ]);

        } catch (\Exception $e) {
            \Log::error('Domain verification check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all() ?? []
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Verification failed due to an error. Please try again.'
            ], 500);
        }
    }

    /**
     * Download verification file
     */
    public function downloadFile(Request $request)
    {
        $domain = $this->normalizeDomain($request->get('domain'));
        $cacheKey = 'verification_' . auth()->id() . '_' . $domain;
        $verificationData = Cache::get($cacheKey);

        if (!$verificationData) {
            abort(404, 'Verification data not found');
        }

        $filename = $verificationData['filename'] ?? 'verification.txt';
        $content = $verificationData['token'];

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    // Private helper methods

    private function normalizeDomain($domain)
    {
        // Remove protocol and www
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = preg_replace('#^www\.#', '', $domain);
        return trim($domain);
    }


    private function generateVerificationData($domain, $method)
    {
        $token = 'verify_' . bin2hex(random_bytes(16));

        $data = [
            'domain' => $domain,
            'method' => $method,
            'token' => $token,
            'created_at' => now(),
        ];

        if ($method === 'txt_file') {
            $filename = 'flippa-verify-' . substr($token, 0, 8) . '.txt';
            $data['filename'] = $filename;
            $data['expected_url'] = 'https://' . $domain . '/' . $filename;
        } elseif ($method === 'dns_record') {
            $dnsName = '_flippa-verify-' . substr($token, 0, 8);
            $data['dns_name'] = $dnsName;
        }

        return $data;
    }

    private function verifyTxtFile($domain, $filename, $expectedToken)
    {
        $urls = [
            "https://{$domain}/{$filename}",
            "http://{$domain}/{$filename}",
        ];

        \Log::info('TXT file verification starting', [
            'domain' => $domain,
            'filename' => $filename,
            'expected_token_length' => strlen($expectedToken),
            'urls_to_check' => $urls
        ]);

        foreach ($urls as $url) {
            try {
                \Log::info('Checking URL', ['url' => $url]);

                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_USERAGENT => 'DomainVerification/1.0',
                ]);

                $content = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);

                \Log::info('URL check result', [
                    'url' => $url,
                    'http_code' => $httpCode,
                    'content_length' => strlen($content),
                    'error' => $error,
                    'content_preview' => $content ? substr($content, 0, 50) : 'empty'
                ]);

                if ($httpCode >= 200 && $httpCode < 300 && $content) {
                    $normalizedContent = preg_replace('/\s+/', '', trim($content));
                    $normalizedToken = preg_replace('/\s+/', '', trim($expectedToken));

                    \Log::info('Content comparison', [
                        'normalized_content' => substr($normalizedContent, 0, 50) . '...',
                        'normalized_token' => substr($normalizedToken, 0, 50) . '...',
                        'match' => $normalizedContent === $normalizedToken
                    ]);

                    if ($normalizedContent === $normalizedToken) {
                        \Log::info('TXT file verification successful');
                        return true;
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('URL check failed', [
                    'url' => $url,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }

        \Log::warning('TXT file verification failed for all URLs');
        return false;
    }

    private function verifyDnsRecord($domain, $recordName, $expectedToken)
    {
        try {
            $records = @dns_get_record($recordName . '.' . $domain, DNS_TXT);
            if ($records) {
                foreach ($records as $record) {
                    if (isset($record['txt'])) {
                        $value = trim($record['txt'], '"');
                        if ($value === $expectedToken) {
                            return true;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }
}

