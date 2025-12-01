<?php
// Ultra-simple test - just to verify PHP is working
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP is working!<br>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Current time: " . date('Y-m-d H:i:s') . "<br>";

// Test file includes
echo "<br>Testing file includes:<br>";

$files = [
    'core/config/timezone.php',
    'core/vendor/autoload.php',
    'core/bootstrap/app.php'
];

foreach ($files as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file NOT FOUND<br>";
    }
}

// Try to include timezone.php
echo "<br>Testing timezone.php include:<br>";
try {
    $timezoneFile = __DIR__ . '/core/config/timezone.php';
    if (file_exists($timezoneFile)) {
        require_once $timezoneFile;
        if (isset($timezone)) {
            echo "✅ timezone.php loaded, timezone = $timezone<br>";
        } else {
            echo "❌ timezone.php loaded but \$timezone variable not set<br>";
        }
    }
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
}

echo "<br>✅ Simple test complete!";

