<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('LARAVEL_START', microtime(true));

require __DIR__.'/core/vendor/autoload.php';

$app = require_once __DIR__.'/core/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

try {
    // Simulate a request to the home page
    $request = Illuminate\Http\Request::create('/', 'GET');
    $response = $kernel->handle($request);
    
    echo "Status: " . $response->getStatusCode() . "<br>";
    
    if ($response->getStatusCode() >= 400) {
        echo "<h2>Error Response:</h2>";
        echo $response->getContent();
    } else {
        echo "✅ Home page loaded successfully!";
    }
} catch (Exception $e) {
    echo "<h2>❌ Exception:</h2>";
    echo "Message: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<h2>❌ Fatal Error:</h2>";
    echo "Message: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

$kernel->terminate($request, $response ?? null);

