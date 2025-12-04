<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FileVerificationService
{
    /**
     * Verify domain ownership by checking for uploaded file
     */
    public function verifyDomain(string $domain, string $token): bool
    {
        $startTime = microtime(true);

        // Try multiple possible locations as per technical plan
        $urls = [
            "https://{$domain}/.well-known/verification-{$token}.txt",
            "http://{$domain}/.well-known/verification-{$token}.txt",
            "https://{$domain}/verification-{$token}.txt",
            "http://{$domain}/verification-{$token}.txt",
        ];

        foreach ($urls as $url) {
            try {
                $response = Http::timeout(30)
                    ->withHeaders([
                        'User-Agent' => 'Flippa-Verification-Bot/1.0',
                        'Accept' => 'text/plain, */*',
                    ])
                    ->get($url);

                if ($response->successful()) {
                    $content = trim($response->body());

                    // Normalize content for comparison
                    $normalizedContent = $this->normalizeContent($content);
                    $normalizedToken = $this->normalizeContent($token);

                    if ($normalizedContent === $normalizedToken) {
                        Log::info('Domain verification successful', [
                            'domain' => $domain,
                            'method' => 'file',
                            'url' => $url,
                            'duration' => microtime(true) - $startTime,
                        ]);
                        return true;
                    }
                }
            } catch (\Exception $e) {
                Log::debug('File verification attempt failed', [
                    'domain' => $domain,
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        Log::info('Domain verification failed', [
            'domain' => $domain,
            'method' => 'file',
            'duration' => microtime(true) - $startTime,
        ]);

        return false;
    }

    /**
     * Generate verification file content
     */
    public function generateFileContent(string $token, string $domain): string
    {
        return "Domain verification for {$domain}\nToken: {$token}\nTimestamp: " . now()->toISOString();
    }

    /**
     * Normalize content for comparison (remove whitespace, normalize line endings)
     */
    private function normalizeContent(string $content): string
    {
        // Remove all whitespace and normalize
        return preg_replace('/\s+/', '', trim($content));
    }

    /**
     * Check if domain is accessible
     */
    public function checkDomainAccessibility(string $domain): array
    {
        $url = "https://{$domain}";

        try {
            $response = Http::timeout(10)->head($url);

            return [
                'accessible' => $response->successful(),
                'status_code' => $response->status(),
                'error' => null,
            ];
        } catch (\Exception $e) {
            return [
                'accessible' => false,
                'status_code' => null,
                'error' => $e->getMessage(),
            ];
        }
    }
}
