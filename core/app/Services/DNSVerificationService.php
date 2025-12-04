<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class DNSVerificationService
{
    /**
     * Verify domain ownership by checking DNS TXT record
     */
    public function verifyDomain(string $domain, string $expectedToken): bool
    {
        $startTime = microtime(true);

        try {
            // Try different record name formats
            $recordNames = [
                "_verify.{$domain}",
                $domain,
            ];

            foreach ($recordNames as $recordName) {
                $records = @dns_get_record($recordName, DNS_TXT);

                if ($records && is_array($records)) {
                    foreach ($records as $record) {
                        if (isset($record['txt'])) {
                            $txtValue = trim($record['txt']);

                            // Remove quotes if present (some DNS providers add them)
                            $txtValue = trim($txtValue, '"');

                            if ($txtValue === $expectedToken) {
                                Log::info('DNS verification successful', [
                                    'domain' => $domain,
                                    'method' => 'dns',
                                    'record_name' => $recordName,
                                    'duration' => microtime(true) - $startTime,
                                ]);
                                return true;
                            }
                        }
                    }
                }
            }

            Log::info('DNS verification failed', [
                'domain' => $domain,
                'method' => 'dns',
                'duration' => microtime(true) - $startTime,
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('DNS verification error', [
                'domain' => $domain,
                'error' => $e->getMessage(),
                'duration' => microtime(true) - $startTime,
            ]);

            return false;
        }
    }

    /**
     * Generate DNS record value
     */
    public function generateRecordValue(string $token): string
    {
        return "flippa-verification-{$token}";
    }

    /**
     * Generate DNS record name
     */
    public function generateRecordName(): string
    {
        return '_verify' . now()->format('His') . rand(100, 999);
    }

    /**
     * Check if domain has valid DNS
     */
    public function checkDomainDNS(string $domain): array
    {
        try {
            $records = @dns_get_record($domain, DNS_A);

            if ($records && count($records) > 0) {
                return [
                    'has_dns' => true,
                    'error' => null,
                ];
            }

            return [
                'has_dns' => false,
                'error' => 'No A records found for domain',
            ];
        } catch (\Exception $e) {
            return [
                'has_dns' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get DNS propagation estimate
     */
    public function getPropagationEstimate(): string
    {
        return 'DNS changes can take 5 minutes to 48 hours to propagate globally.';
    }
}
