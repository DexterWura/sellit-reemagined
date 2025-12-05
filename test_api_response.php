<?php

// Test what the API actually returns
echo "<h1>API Response Test</h1>";

// Simulate the generateVerificationData function
function generateVerificationData($domain, $method) {
    $token = 'verify_' . bin2hex(random_bytes(16));

    $data = [
        'domain' => $domain,
        'method' => $method,
        'token' => $token,
        'created_at' => date('Y-m-d H:i:s'),
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

// Test TXT file method
echo "<h2>TXT File Method Test</h2>";
$txtData = generateVerificationData('google.com', 'txt_file');
echo "<pre>" . json_encode($txtData, JSON_PRETTY_PRINT) . "</pre>";

// Test DNS method
echo "<h2>DNS Record Method Test</h2>";
$dnsData = generateVerificationData('google.com', 'dns_record');
echo "<pre>" . json_encode($dnsData, JSON_PRETTY_PRINT) . "</pre>";

// Simulate API response
echo "<h2>API Response Simulation</h2>";
$response = [
    'success' => true,
    'token' => $txtData['token'],
    'filename' => $txtData['filename'] ?? null,
    'expected_url' => $txtData['expected_url'] ?? null,
    'content' => $txtData['token'],
    'dns_name' => $txtData['dns_name'] ?? null,
    'dns_value' => $txtData['token'],
    'domain' => 'google.com',
    'method' => 'txt_file'
];

echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";

// Test what JavaScript would receive
echo "<h2>JavaScript Processing Test</h2>";
echo "<p>Token: <code>" . $response['token'] . "</code></p>";
echo "<p>Filename: <code>" . ($response['filename'] ?: 'NULL') . "</code></p>";
echo "<p>Expected URL: <code>" . ($response['expected_url'] ?: 'NULL') . "</code></p>";
echo "<p>Content: <code>" . $response['content'] . "</code></p>";

if ($response['filename']) {
    echo "<p>✅ Filename is set - download should work</p>";
    $downloadUrl = "user/verification/download?token=" . urlencode($response['token']) . "&filename=" . urlencode($response['filename']) . "&domain=" . urlencode($response['domain']);
    echo "<p>Download URL: <code>$downloadUrl</code></p>";
} else {
    echo "<p>❌ Filename is NULL - download will not work</p>";
}

?>
