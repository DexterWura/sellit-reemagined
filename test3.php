<?php
// This test checks if the issue is with output before headers (deprecation warnings)
ini_set('display_errors', 0);
error_reporting(0);

// Start output buffering to catch any output before Laravel
ob_start();

define('LARAVEL_START', microtime(true));

try {
    require __DIR__.'/core/vendor/autoload.php';
    $app = require_once __DIR__.'/core/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    $request = Illuminate\Http\Request::capture();
    $response = $kernel->handle($request);
    
    // Clear any buffered deprecation warnings
    ob_end_clean();
    
    // Now send the actual response
    $response->send();
    
    $kernel->terminate($request, $response);
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    ob_end_clean();
    http_response_code(500);
    echo "<h1>Fatal Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

