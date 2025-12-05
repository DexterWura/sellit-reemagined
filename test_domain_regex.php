<?php

echo "Testing domain regex pattern...\n";
echo "Pattern: /^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/\n\n";

$testDomains = [
    'google.com',
    'example.com',
    'sub.example.com',
    'test-domain.com',
    '123test.com',
    'google.co.uk',
    'my-site.io',
    'https://google.com', // This should fail regex but pass after normalization
    'google.com/path',    // This should fail regex
    'not-a-domain',
    '',
    'google',
];

foreach ($testDomains as $domain) {
    $matches = preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $domain);
    echo "Domain: '$domain' -> " . ($matches ? 'VALID' : 'INVALID') . "\n";
}

echo "\nTesting normalizeDomain function...\n";

function normalizeDomain($domain) {
    // Remove protocol and www
    $domain = preg_replace('#^https?://#', '', $domain);
    $domain = preg_replace('#^www\.#', '', $domain);
    return trim($domain);
}

$testUrls = [
    'https://google.com',
    'http://google.com',
    'https://www.google.com',
    'google.com',
    'https://google.com/path',
];

foreach ($testUrls as $url) {
    $normalized = normalizeDomain($url);
    $matches = preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $normalized);
    echo "URL: '$url' -> Normalized: '$normalized' -> " . ($matches ? 'VALID' : 'INVALID') . "\n";
}
