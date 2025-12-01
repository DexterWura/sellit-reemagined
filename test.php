<?php
// Simple diagnostic test page
// This will help identify where the error occurs

// Enable all error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set content type
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Diagnostic Test Page</h1>";
echo "<hr>";

// Test 1: Basic PHP
echo "<h2>Test 1: Basic PHP</h2>";
try {
    echo "✅ PHP Version: " . PHP_VERSION . "<br>";
    echo "✅ Current Directory: " . __DIR__ . "<br>";
    echo "✅ Script Path: " . __FILE__ . "<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// Test 2: File existence checks
echo "<h2>Test 2: File Existence</h2>";
$files = [
    'index.php',
    'core/vendor/autoload.php',
    'core/bootstrap/app.php',
    'core/config/app.php',
    'core/config/timezone.php',
    '.env'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    $exists = file_exists($path);
    $readable = $exists ? is_readable($path) : false;
    echo ($exists ? "✅" : "❌") . " $file - " . ($exists ? "Exists" : "Missing");
    if ($exists && !$readable) {
        echo " (NOT READABLE)";
    }
    echo "<br>";
}
echo "<hr>";

// Test 3: Include timezone.php directly
echo "<h2>Test 3: Include timezone.php</h2>";
try {
    $timezonePath = __DIR__ . '/core/config/timezone.php';
    if (file_exists($timezonePath)) {
        require_once($timezonePath);
        echo "✅ timezone.php included successfully<br>";
        if (isset($timezone)) {
            echo "✅ Timezone variable set: $timezone<br>";
        } else {
            echo "❌ Timezone variable NOT set<br>";
        }
    } else {
        echo "❌ timezone.php file not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
}
echo "<hr>";

// Test 4: Load Composer autoloader
echo "<h2>Test 4: Composer Autoloader</h2>";
try {
    $autoloadPath = __DIR__ . '/core/vendor/autoload.php';
    if (file_exists($autoloadPath)) {
        require_once($autoloadPath);
        echo "✅ Composer autoloader loaded<br>";
    } else {
        echo "❌ autoload.php not found at: $autoloadPath<br>";
    }
} catch (Exception $e) {
    echo "❌ Exception loading autoloader: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
} catch (Error $e) {
    echo "❌ Fatal Error loading autoloader: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
}
echo "<hr>";

// Test 5: Load config/app.php
echo "<h2>Test 5: Load config/app.php</h2>";
try {
    $configPath = __DIR__ . '/core/config/app.php';
    if (file_exists($configPath)) {
        // Temporarily set a timezone variable in case it's not set
        if (!isset($timezone)) {
            $timezone = 'UTC';
        }
        $config = require($configPath);
        echo "✅ config/app.php loaded<br>";
        echo "✅ App Name: " . ($config['name'] ?? 'NOT SET') . "<br>";
        echo "✅ App Env: " . ($config['env'] ?? 'NOT SET') . "<br>";
    } else {
        echo "❌ config/app.php not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Exception loading config: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "❌ Fatal Error loading config: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
echo "<hr>";

// Test 6: Bootstrap Laravel
echo "<h2>Test 6: Bootstrap Laravel</h2>";
try {
    if (class_exists('Illuminate\Foundation\Application')) {
        define('LARAVEL_START', microtime(true));
        $app = require_once __DIR__ . '/core/bootstrap/app.php';
        echo "✅ Laravel bootstrap successful<br>";
        echo "✅ Application instance created<br>";
    } else {
        echo "❌ Laravel classes not available (autoloader issue?)<br>";
    }
} catch (Exception $e) {
    echo "❌ Exception bootstrapping Laravel: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "❌ Fatal Error bootstrapping Laravel: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
echo "<hr>";

// Test 7: Check .env file
echo "<h2>Test 7: Environment File</h2>";
try {
    $envPath = __DIR__ . '/.env';
    if (file_exists($envPath)) {
        echo "✅ .env file exists<br>";
        $envContent = file_get_contents($envPath);
        if (empty($envContent)) {
            echo "❌ .env file is EMPTY<br>";
        } else {
            echo "✅ .env file has content (" . strlen($envContent) . " bytes)<br>";
            // Check for critical settings
            if (strpos($envContent, 'APP_KEY=') !== false) {
                echo "✅ APP_KEY is set<br>";
            } else {
                echo "❌ APP_KEY is NOT set<br>";
            }
        }
    } else {
        echo "❌ .env file NOT found<br>";
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// Test 8: PHP Configuration
echo "<h2>Test 8: PHP Configuration</h2>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";
echo "Post Max Size: " . ini_get('post_max_size') . "<br>";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Error Reporting: " . error_reporting() . "<br>";
echo "Display Errors: " . ini_get('display_errors') . "<br>";
echo "<hr>";

echo "<h2>✅ Diagnostic Complete</h2>";
echo "<p>If you see this message, at least basic PHP is working.</p>";

