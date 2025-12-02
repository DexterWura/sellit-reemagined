<?php
/**
 * Quick fix to set the SystemInstalled flag
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Fix Installation Flag</h1>";

// Method 1: Try Laravel cache
try {
    if (!defined('LARAVEL_START')) {
        define('LARAVEL_START', microtime(true));
    }
    require __DIR__ . '/core/vendor/autoload.php';
    $app = require __DIR__ . '/core/bootstrap/app.php';
    
    if (is_object($app) && method_exists($app, 'make')) {
        $cache = $app->make('cache');
        $cache->put('SystemInstalled', true, now()->addYears(10));
        echo "<p style='color: green;'>✅ Set in Laravel cache</p>";
        
        // Verify
        $value = $cache->get('SystemInstalled');
        if ($value) {
            echo "<p style='color: green;'>✅ Verified: Cache value is " . ($value ? 'true' : 'false') . "</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠️ Laravel cache method failed: " . $e->getMessage() . "</p>";
}

// Method 2: File-based cache
$cacheFile1 = __DIR__ . '/core/storage/framework/cache/data/SystemInstalled';
$cacheFile2 = __DIR__ . '/core/storage/framework/cache/data';

if (!is_dir($cacheFile2)) {
    mkdir($cacheFile2, 0755, true);
}

if (file_put_contents($cacheFile1, serialize(['installed' => true, 'timestamp' => time()]))) {
    echo "<p style='color: green;'>✅ Set in file-based cache: " . htmlspecialchars($cacheFile1) . "</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to write file-based cache</p>";
}

// Method 3: Also try using storage_path
try {
    if (isset($app) && is_object($app)) {
        $storagePath = storage_path('framework/cache/data/SystemInstalled');
        $storageDir = dirname($storagePath);
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
        file_put_contents($storagePath, serialize(['installed' => true, 'timestamp' => time()]));
        echo "<p style='color: green;'>✅ Set in storage_path cache: " . htmlspecialchars($storagePath) . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠️ storage_path method failed: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>✅ Installation flag has been set using multiple methods.</strong></p>";
echo "<p>Try accessing your site now: <a href='/'>https://escrow.dextersoft.com/</a></p>";
echo "<p>If you still get redirected, clear your browser cache or try in incognito mode.</p>";

