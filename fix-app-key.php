<?php
/**
 * Fix APP_KEY issue
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Fix APP_KEY</h1>";
echo "<hr>";

$envPath = __DIR__ . '/.env';

if (!file_exists($envPath)) {
    die("❌ .env file not found!");
}

$envContent = file_get_contents($envPath);

// Check if APP_KEY exists and has a value
$hasAppKey = preg_match('/^APP_KEY=(.+)$/m', $envContent, $matches);

if (!$hasAppKey || empty(trim($matches[1] ?? ''))) {
    echo "<p style='color: red;'>❌ APP_KEY is missing or empty!</p>";
    
    // Generate new APP_KEY
    $appKey = 'base64:' . base64_encode(random_bytes(32));
    
    // Add or replace APP_KEY
    if (preg_match('/^APP_KEY=.*$/m', $envContent)) {
        // Replace existing empty one
        $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $appKey, $envContent);
    } else {
        // Add after APP_NAME
        if (preg_match('/^APP_NAME=.*$/m', $envContent)) {
            $envContent = preg_replace('/^(APP_NAME=.*)$/m', '$1' . "\nAPP_KEY=" . $appKey, $envContent);
        } else {
            // Add at the beginning
            $envContent = "APP_KEY=" . $appKey . "\n" . $envContent;
        }
    }
    
    // Write back
    if (file_put_contents($envPath, $envContent)) {
        echo "<p style='color: green;'>✅ APP_KEY added to .env file</p>";
        echo "<p>Key: <code>" . htmlspecialchars($appKey) . "</code></p>";
    } else {
        die("<p style='color: red;'>❌ Failed to write to .env file. Check permissions.</p>");
    }
} else {
    $existingKey = trim($matches[1]);
    echo "<p style='color: green;'>✅ APP_KEY exists: <code>" . htmlspecialchars(substr($existingKey, 0, 30)) . "...</code></p>";
    
    // Clear config cache
    try {
        define('LARAVEL_START', microtime(true));
        require __DIR__ . '/core/vendor/autoload.php';
        $app = require __DIR__ . '/core/bootstrap/app.php';
        
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        echo "<p style='color: green;'>✅ Config cache cleared</p>";
        
        // Verify key is readable
        $key = config('app.key');
        if ($key) {
            echo "<p style='color: green;'>✅ Laravel can read APP_KEY</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Laravel still can't read APP_KEY</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Could not clear cache: " . $e->getMessage() . "</p>";
    }
}

echo "<hr>";
echo "<p><strong>Next step:</strong> <a href='/'>Try accessing your site</a></p>";

