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
        $request->validate([
            'domain' => 'required|string|max:255',
            'method' => 'required|in:txt_file,dns_record',
        ]);

        $domain = $this->normalizeDomain($request->domain);
        $method = $request->method;

        // Check if verification is enabled
        if (!MarketplaceSetting::requireDomainVerification() &&
            !MarketplaceSetting::requireWebsiteVerification()) {
            return response()->json([
                'success' => false,
                'message' => 'Domain verification is not enabled'
            ], 400);
        }

        // Check if user already has a verified domain
        $verifiedCacheKey = 'verified_domain_' . auth()->id() . '_' . $domain;
        $verifiedData = Cache::get($verifiedCacheKey);

        if ($verifiedData) {
            return response()->json([
                'success' => false,
                'message' => 'This domain is already verified'
            ], 400);
        }

        // Check if domain is accessible
        if (!$this->isDomainAccessible($domain)) {
            return response()->json([
                'success' => false,
                'message' => 'Domain is not accessible. Please ensure the domain exists and is live.'
            ], 400);
        }

        // Generate verification data
        $verificationData = $this->generateVerificationData($domain, $method);

        // Store in cache with user ID (expires in 24 hours)
        $cacheKey = 'verification_' . auth()->id() . '_' . $domain;
        Cache::put($cacheKey, $verificationData, 86400);

        return response()->json([
            'success' => true,
            'token' => $verificationData['token'],
            'filename' => $verificationData['filename'] ?? null,
            'expected_url' => $verificationData['expected_url'] ?? null,
            'content' => $verificationData['token'],
            'dns_name' => $verificationData['dns_name'] ?? null,
            'dns_value' => $verificationData['token'],
        ]);
    }

    /**
     * Verify domain ownership
     */
    public function verifyDomain(Request $request)
    {
        $request->validate([
            'domain' => 'required|string',
            'method' => 'required|in:txt_file,dns_record',
            'token' => 'required|string',
            'filename' => 'required_if:method,txt_file|string',
            'dns_name' => 'required_if:method,dns_record|string',
        ]);

        $domain = $this->normalizeDomain($request->domain);
        $method = $request->method;
        $token = trim($request->token);

        $cacheKey = 'verification_' . auth()->id() . '_' . $domain;
        $verificationData = Cache::get($cacheKey);

        if (!$verificationData || $verificationData['token'] !== $token) {
            return response()->json([
                'success' => false,
                'message' => 'Verification session expired or invalid. Please start verification again.'
            ], 400);
        }

        $success = false;
        $message = '';

        if ($method === 'txt_file') {
            $success = $this->verifyTxtFile($domain, $request->filename, $token);
            $message = $success ? 'Domain verified successfully!' : 'Verification file not found or content doesn\'t match.';
        } else {
            $success = $this->verifyDnsRecord($domain, $request->dns_name, $token);
            $message = $success ? 'Domain verified successfully!' : 'DNS record not found or doesn\'t match.';
        }

        if ($success) {
            // Store successful verification
            $verifiedCacheKey = 'verified_domain_' . auth()->id() . '_' . $domain;
            Cache::put($verifiedCacheKey, [
                'domain' => $domain,
                'verified_at' => now(),
                'method' => $method,
                'token' => $token
            ], 2592000); // 30 days
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
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

    private function isDomainAccessible($domain)
    {
        // Simple check - try to connect
        $url = 'https://' . $domain;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_NOBODY => true, // HEAD request
        ]);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 400;
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

        foreach ($urls as $url) {
            try {
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
                curl_close($ch);

                if ($httpCode >= 200 && $httpCode < 300 && $content) {
                    $normalizedContent = preg_replace('/\s+/', '', trim($content));
                    $normalizedToken = preg_replace('/\s+/', '', trim($expectedToken));

                    if ($normalizedContent === $normalizedToken) {
                        return true;
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

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

