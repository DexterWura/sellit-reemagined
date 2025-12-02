<?php
/**
 * Script to fix the install redirect issue
 * This will check .env and set the SystemInstalled cache
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Fix Install Redirect</h1>";
echo "<hr>";

// Check .env file
$envPath = __DIR__ . '/.env';
echo "<h2>Checking .env File</h2>";

if (!file_exists($envPath)) {
    echo "<p style='color: red;'>❌ .env file not found!</p>";
    echo "<p>Please create it using <a href='create-env.php'>create-env.php</a></p>";
    exit;
}

echo "✅ .env file exists<br>";

$envContent = file_get_contents($envPath);
if (empty(trim($envContent))) {
    echo "<p style='color: red;'>❌ .env file is EMPTY!</p>";
    echo "<p>Please fill in your .env file with proper configuration.</p>";
    exit;
}

echo "✅ .env file has content (" . strlen($envContent) . " bytes)<br>";

// Check for APP_KEY
if (strpos($envContent, 'APP_KEY=') === false || strpos($envContent, 'APP_KEY=base64:') === false) {
    echo "<p style='color: orange;'>⚠️ APP_KEY may not be set properly</p>";
} else {
    echo "✅ APP_KEY is set<br>";
}

echo "<hr>";

// Now try to set the cache
echo "<h2>Setting SystemInstalled Cache</h2>";

// Load Laravel to access cache
define('LARAVEL_START', microtime(true));
require __DIR__ . '/core/vendor/autoload.php';

try {
    $app = require_once __DIR__ . '/core/bootstrap/app.php';
    
    // Get cache instance
    $cache = $app->make('cache');
    
    // Check current cache value
    $currentValue = $cache->get('SystemInstalled');
    echo "Current SystemInstalled cache value: " . ($currentValue ? "true" : "false/null") . "<br>";
    
    // Set the cache
    $cache->put('SystemInstalled', true, now()->addYears(10)); // Set for 10 years
    echo "<p style='color: green;'>✅ SystemInstalled cache set to true</p>";
    
    // Verify it was set
    $newValue = $cache->get('SystemInstalled');
    if ($newValue) {
        echo "<p style='color: green;'>✅ Verified: Cache is now set correctly</p>";
    } else {
        echo "<p style='color: red;'>❌ Warning: Cache may not be working properly</p>";
    }
    
    echo "<hr>";
    echo "<h2>✅ Fix Complete!</h2>";
    echo "<p>Try accessing your site now: <a href='/'>https://escrow.dextersoft.com/</a></p>";
    echo "<p>If you still get redirected, the issue might be:</p>";
    echo "<ul>";
    echo "<li>Cache driver not working (check config/cache.php)</li>";
    echo "<li>.env file missing required values</li>";
    echo "<li>Database connection issues</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><a href='test.php'>← Back to Diagnostic Test</a></p>";

