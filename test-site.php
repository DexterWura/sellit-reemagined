<?php
/**
 * Test site access with detailed error reporting
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enable Laravel error reporting
putenv('APP_DEBUG=true');

define('LARAVEL_START', microtime(true));

// Check if .env file exists
$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    die('❌ .env file not found');
}

// Register the Composer autoloader...
require __DIR__.'/core/vendor/autoload.php';

// Bootstrap Laravel
try {
    $app = require_once __DIR__.'/core/bootstrap/app.php';
    
    // Create a request
    $request = \Illuminate\Http\Request::create('/', 'GET');
    
    // Handle the request
    $response = $app->handleRequest($request);
    
    // Get the status code
    $statusCode = $response->getStatusCode();
    
    echo "<h1>Request Test</h1>";
    echo "<p>Status Code: " . $statusCode . "</p>";
    
    if ($statusCode >= 400) {
        echo "<h2>Error Response:</h2>";
        echo "<pre>" . htmlspecialchars($response->getContent()) . "</pre>";
    } else {
        echo "<h2>✅ Success!</h2>";
        echo "<p>Response length: " . strlen($response->getContent()) . " bytes</p>";
    }
    
    // Send the response
    $response->send();
    
    $app->terminate($request, $response);
    
} catch (\Throwable $e) {
    echo "<h1>Fatal Error</h1>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<h2>Stack Trace:</h2>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

