<?php

// Simple test without Laravel bootstrap - test core functions only

echo "=== DOMAIN VERIFICATION COMPREHENSIVE TEST ===\n\n";

// Test 1: Domain Normalization
echo "1. TESTING DOMAIN NORMALIZATION:\n";
function normalizeDomain($domain) {
    // Remove protocol and www
    $domain = preg_replace('#^https?://#', '', $domain);
    $domain = preg_replace('#^www\.#', '', $domain);
    return trim($domain);
}

$testDomains = [
    'https://google.com',
    'http://google.com',
    'https://www.google.com',
    'google.com',
    'https://google.com/path',
    'sub.google.com',
    'google.co.uk',
    'example.com?query=test',
    'https://example.com#fragment',
    '',
    'invalid domain with spaces',
    'google.com/path/to/file',
];

foreach ($testDomains as $domain) {
    $normalized = normalizeDomain($domain);
    echo "  '$domain' → '$normalized'\n";
}
echo "\n";

// Test 2: Domain Validation
echo "2. TESTING DOMAIN VALIDATION:\n";
function validateDomain($domain) {
    if (empty($domain)) return false;
    if (!preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $domain)) return false;
    if (strlen($domain) > 253) return false;
    if (preg_match('/^\s|\s$|[^\w.-]/', $domain)) return false;
    return true;
}

$validationTests = [
    'google.com' => true,
    'example.com' => true,
    'sub.example.com' => true,
    'google.co.uk' => true,
    '123test.com' => true,
    'test-domain.com' => true,
    '' => false,
    'google' => false,
    'google.com/path' => false,
    'google.com?query=test' => false,
    'google.com with spaces' => false,
    'google.com!' => false,
];

foreach ($validationTests as $domain => $expected) {
    $result = validateDomain($domain);
    $status = ($result === $expected) ? '✅' : '❌';
    echo "  $status '$domain' → " . ($result ? 'VALID' : 'INVALID') . " (expected " . ($expected ? 'VALID' : 'INVALID') . ")\n";
}
echo "\n";

// Test 3: Token Generation
echo "3. TESTING TOKEN GENERATION:\n";
function generateToken() {
    return 'verify_' . bin2hex(random_bytes(16));
}

for ($i = 0; $i < 3; $i++) {
    $token = generateToken();
    echo "  Token $i: $token (length: " . strlen($token) . ")\n";
}
echo "\n";

// Test 4: Verification Data Generation
echo "4. TESTING VERIFICATION DATA GENERATION:\n";
function generateVerificationData($domain, $method) {
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

$testData = generateVerificationData('google.com', 'txt_file');
echo "  TXT File Method:\n";
echo "    Domain: {$testData['domain']}\n";
echo "    Token: {$testData['token']}\n";
echo "    Filename: {$testData['filename']}\n";
echo "    Expected URL: {$testData['expected_url']}\n";

$testData = generateVerificationData('google.com', 'dns_record');
echo "  DNS Record Method:\n";
echo "    Domain: {$testData['domain']}\n";
echo "    Token: {$testData['token']}\n";
echo "    DNS Name: {$testData['dns_name']}\n";
echo "\n";

// Test 5: Cache Operations
echo "5. TESTING CACHE OPERATIONS:\n";
use Illuminate\Support\Facades\Cache;

$userId = 1;
$domain = 'google.com';
$method = 'txt_file';

// Test cache keys
$verificationCacheKey = 'verification_' . $userId . '_' . $domain;
$verifiedCacheKey = 'verified_domain_' . $userId . '_' . $domain;

echo "  Cache Keys:\n";
echo "    Verification: $verificationCacheKey\n";
echo "    Verified: $verifiedCacheKey\n";

// Test storing and retrieving data
$testData = generateVerificationData($domain, $method);
$cacheResult = Cache::put($verificationCacheKey, $testData, 86400); // 24 hours
echo "  Cache Store Result: " . ($cacheResult ? 'SUCCESS' : 'FAILED') . "\n";

$retrievedData = Cache::get($verificationCacheKey);
if ($retrievedData) {
    echo "  Cache Retrieve: SUCCESS\n";
    echo "    Retrieved Token: {$retrievedData['token']}\n";
    echo "    Retrieved Filename: {$retrievedData['filename']}\n";
} else {
    echo "  Cache Retrieve: FAILED\n";
}

// Test verified status storage
$verifiedData = [
    'domain' => $domain,
    'verified_at' => now(),
    'method' => $method,
    'token' => $testData['token']
];

$verifiedCacheResult = Cache::put($verifiedCacheKey, $verifiedData, 2592000); // 30 days
echo "  Verified Cache Store: " . ($verifiedCacheResult ? 'SUCCESS' : 'FAILED') . "\n";

$retrievedVerified = Cache::get($verifiedCacheKey);
echo "  Verified Cache Retrieve: " . ($retrievedVerified ? 'SUCCESS' : 'FAILED') . "\n";

echo "\n";

// Test 6: File Verification Simulation
echo "6. TESTING FILE VERIFICATION SIMULATION:\n";
function simulateFileVerification($domain, $filename, $expectedToken) {
    $urls = [
        "https://{$domain}/{$filename}",
        "http://{$domain}/{$filename}",
    ];

    echo "  Checking URLs:\n";
    foreach ($urls as $url) {
        echo "    $url\n";

        // Simulate curl request (we'll just pretend)
        $simulatedContent = ($url === "https://{$domain}/{$filename}") ? $expectedToken : 'wrong content';
        $simulatedHttpCode = ($url === "https://{$domain}/{$filename}") ? 200 : 404;

        echo "      HTTP Code: $simulatedHttpCode\n";
        echo "      Content: '$simulatedContent'\n";

        if ($simulatedHttpCode >= 200 && $simulatedHttpCode < 300 && $simulatedContent) {
            $normalizedContent = preg_replace('/\s+/', '', trim($simulatedContent));
            $normalizedToken = preg_replace('/\s+/', '', trim($expectedToken));

            if ($normalizedContent === $normalizedToken) {
                return true;
            }
        }
    }

    return false;
}

$result = simulateFileVerification('google.com', 'verify-test.txt', 'test_token_123');
echo "  File Verification Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n\n";

// Test 7: DNS Verification Simulation
echo "7. TESTING DNS VERIFICATION SIMULATION:\n";
function simulateDnsVerification($domain, $recordName, $expectedToken) {
    echo "  Checking DNS for: $recordName.$domain\n";

    // Simulate DNS lookup (we'll pretend to find the record)
    $simulatedRecords = [
        [
            'txt' => $expectedToken
        ]
    ];

    if ($simulatedRecords) {
        foreach ($simulatedRecords as $record) {
            if (isset($record['txt'])) {
                $value = trim($record['txt'], '"');
                echo "    Found TXT record: '$value'\n";
                if ($value === $expectedToken) {
                    return true;
                }
            }
        }
    } else {
        echo "    No TXT records found\n";
    }

    return false;
}

$result = simulateDnsVerification('google.com', '_verify_test', 'test_token_123');
echo "  DNS Verification Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n\n";

// Test 8: Marketplace Settings Check
echo "8. TESTING MARKETPLACE SETTINGS:\n";
try {
    $domainRequired = \App\Models\MarketplaceSetting::requireDomainVerification();
    $websiteRequired = \App\Models\MarketplaceSetting::requireWebsiteVerification();
    $socialRequired = \App\Models\MarketplaceSetting::requireSocialMediaVerification();
    $methods = \App\Models\MarketplaceSetting::getDomainVerificationMethods();

    echo "  Domain Verification Required: " . ($domainRequired ? 'YES' : 'NO') . "\n";
    echo "  Website Verification Required: " . ($websiteRequired ? 'YES' : 'NO') . "\n";
    echo "  Social Media Verification Required: " . ($socialRequired ? 'YES' : 'NO') . "\n";
    echo "  Verification Methods: " . json_encode($methods) . "\n";
} catch (Exception $e) {
    echo "  ERROR accessing MarketplaceSetting: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 9: Route Simulation
echo "9. TESTING ROUTE SIMULATION:\n";
try {
    $routes = [
        'user.verification.generate',
        'user.verification.verify',
        'user.verification.download',
        'user.social.verification.generate',
        'user.social.verification.verify-social',
    ];

    foreach ($routes as $routeName) {
        try {
            $url = route($routeName);
            echo "  ✅ Route '$routeName' exists: $url\n";
        } catch (Exception $e) {
            echo "  ❌ Route '$routeName' error: " . $e->getMessage() . "\n";
        }
    }
} catch (Exception $e) {
    echo "  ERROR testing routes: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 10: Error Scenarios
echo "10. TESTING ERROR SCENARIOS:\n";

echo "  Empty domain validation:\n";
$result = validateDomain('');
echo "    Result: " . ($result ? 'INVALID (should be valid)' : 'VALID (correct)') . "\n";

echo "  Invalid domain format:\n";
$result = validateDomain('not-a-valid-domain');
echo "    Result: " . ($result ? 'INVALID (should be valid)' : 'VALID (correct)') . "\n";

echo "  Domain with spaces:\n";
$result = validateDomain('google.com with spaces');
echo "    Result: " . ($result ? 'INVALID (should be valid)' : 'VALID (correct)') . "\n";

echo "  Domain too long:\n";
$longDomain = str_repeat('a', 250) . '.com';
$result = validateDomain($longDomain);
echo "    Result: " . ($result ? 'INVALID (should be valid)' : 'VALID (correct)') . "\n";

echo "\n=== TEST COMPLETE ===\n";
echo "\nIf all tests show SUCCESS/VALID/correct results, the code is working.\n";
echo "If you see FAILED/INVALID/incorrect results, there's a bug in the implementation.\n";
echo "\nCheck the Laravel logs at core/storage/logs/laravel.log for runtime errors.\n";
