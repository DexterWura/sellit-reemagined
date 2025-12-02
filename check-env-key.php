<?php
/**
 * Check and fix APP_KEY in .env file
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Check APP_KEY</h1>";
echo "<hr>";

$envPath = __DIR__ . '/.env';

if (!file_exists($envPath)) {
    die("❌ .env file not found at: " . htmlspecialchars($envPath));
}

echo "✅ .env file exists<br>";

// Read .env file
$envContent = file_get_contents($envPath);
echo "✅ .env file read (" . strlen($envContent) . " bytes)<br>";

// Check for APP_KEY
if (strpos($envContent, 'APP_KEY=') === false) {
    echo "<p style='color: red;'>❌ APP_KEY not found in .env file!</p>";
    
    // Generate APP_KEY
    $appKey = 'base64:' . base64_encode(random_bytes(32));
    
    // Add APP_KEY to .env
    if (strpos($envContent, 'APP_NAME=') !== false) {
        // Insert after APP_NAME
        $envContent = preg_replace(
            '/(APP_NAME=.*\n)/',
            '$1APP_KEY=' . $appKey . "\n",
            $envContent
        );
    } else {
        // Add at the beginning
        $envContent = "APP_KEY=" . $appKey . "\n" . $envContent;
    }
    
    // Write back
    if (file_put_contents($envPath, $envContent)) {
        echo "<p style='color: green;'>✅ APP_KEY added to .env file</p>";
        echo "<p>Generated key: <code>" . htmlspecialchars($appKey) . "</code></p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to write APP_KEY to .env file</p>";
    }
} else {
    // Check if APP_KEY has a value
    if (preg_match('/APP_KEY=(.*)/', $envContent, $matches)) {
        $appKeyValue = trim($matches[1]);
        if (empty($appKeyValue) || $appKeyValue === 'null' || $appKeyValue === '') {
            echo "<p style='color: orange;'>⚠️ APP_KEY is empty!</p>";
            
            // Generate APP_KEY
            $appKey = 'base64:' . base64_encode(random_bytes(32));
            
            // Replace empty APP_KEY
            $envContent = preg_replace(
                '/APP_KEY=.*/',
                'APP_KEY=' . $appKey,
                $envContent
            );
            
            // Write back
            if (file_put_contents($envPath, $envContent)) {
                echo "<p style='color: green;'>✅ APP_KEY set in .env file</p>";
                echo "<p>Generated key: <code>" . htmlspecialchars($appKey) . "</code></p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to write APP_KEY to .env file</p>";
            }
        } else {
            echo "<p style='color: green;'>✅ APP_KEY exists: <code>" . htmlspecialchars(substr($appKeyValue, 0, 20)) . "...</code></p>";
            
            // Test if Laravel can read it
            try {
                define('LARAVEL_START', microtime(true));
                require __DIR__ . '/core/vendor/autoload.php';
                $app = require __DIR__ . '/core/bootstrap/app.php';
                
                $key = config('app.key');
                if ($key) {
                    echo "<p style='color: green;'>✅ Laravel can read APP_KEY successfully</p>";
                } else {
                    echo "<p style='color: red;'>❌ Laravel cannot read APP_KEY (config cache issue?)</p>";
                    echo "<p>Try running: <code>php artisan config:clear</code></p>";
                }
            } catch (Exception $e) {
                echo "<p style='color: orange;'>⚠️ Could not test Laravel (this is okay if .env was just created)</p>";
            }
        }
    }
}

echo "<hr>";
echo "<p><a href='/'>Try accessing your site</a></p>";

