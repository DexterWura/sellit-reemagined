<?php
/**
 * Debug script to find the 500 error
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>500 Error Debug</h1>";
echo "<hr>";

// Test 1: Basic PHP
echo "<h2>Test 1: Basic PHP</h2>";
echo "✅ PHP is working<br>";
echo "<hr>";

// Test 2: Load Laravel
echo "<h2>Test 2: Load Laravel</h2>";
try {
    define('LARAVEL_START', microtime(true));
    require __DIR__ . '/core/vendor/autoload.php';
    echo "✅ Autoloader loaded<br>";
    
    $app = require __DIR__ . '/core/bootstrap/app.php';
    echo "✅ Laravel bootstrapped<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

echo "<hr>";

// Test 3: Check AppServiceProvider
echo "<h2>Test 3: AppServiceProvider</h2>";
try {
    $provider = new \App\Providers\AppServiceProvider($app);
    echo "✅ AppServiceProvider instantiated<br>";
    
    // Try to boot it
    $provider->boot();
    echo "✅ AppServiceProvider booted successfully<br>";
} catch (Exception $e) {
    echo "❌ Error in AppServiceProvider: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";

// Test 4: Database Connection
echo "<h2>Test 4: Database Connection</h2>";
try {
    \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "✅ Database connected<br>";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Test 5: Helper Functions
echo "<h2>Test 5: Helper Functions</h2>";
try {
    $template = activeTemplate();
    echo "✅ activeTemplate() works: " . $template . "<br>";
} catch (Exception $e) {
    echo "❌ activeTemplate() error: " . $e->getMessage() . "<br>";
}

try {
    $gs = gs();
    echo "✅ gs() works<br>";
} catch (Exception $e) {
    echo "❌ gs() error: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Test 6: Try to handle a request
echo "<h2>Test 6: Handle Request</h2>";
try {
    $request = \Illuminate\Http\Request::create('/', 'GET');
    $response = $app->handleRequest($request);
    echo "✅ Request handled successfully<br>";
    echo "Status: " . $response->getStatusCode() . "<br>";
} catch (Exception $e) {
    echo "❌ Request handling error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>Debug Complete</h2>";

