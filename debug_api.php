<?php

echo "<h1>Direct API Test</h1>";

// Test the verification generation API directly
echo "<h2>Testing Verification API</h2>";

// Simulate the request data
$testData = [
    'domain' => 'google.com',
    'method' => 'txt_file'
];

echo "<p>Testing with data: " . json_encode($testData) . "</p>";

// Try to include the controller and call the method directly
try {
    require_once 'vendor/autoload.php';

    // Bootstrap Laravel (limited)
    $app = require_once 'bootstrap/app.php';

    // Create a mock request
    $request = new Illuminate\Http\Request();
    $request->merge([
        '_token' => 'test',
        'domain' => 'google.com',
        'method' => 'txt_file'
    ]);

    // Try to resolve the controller
    $controller = app(\App\Http\Controllers\User\DomainVerificationController::class);

    // Call the method
    $response = $controller->generateVerification($request);

    echo "<h3>Response:</h3>";
    echo "<pre>" . $response->getContent() . "</pre>";

} catch (Exception $e) {
    echo "<h3>Error:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

?>
