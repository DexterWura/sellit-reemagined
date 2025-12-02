<?php
/**
 * Force Laravel to load .env file even if config is cached
 * This script deletes config cache and ensures .env is loaded
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Force .env Load</h1>";
echo "<hr>";

// Step 1: Delete config cache
$configCache = __DIR__ . '/core/bootstrap/cache/config.php';
if (file_exists($configCache)) {
    if (unlink($configCache)) {
        echo "✅ Deleted config cache<br>";
    } else {
        echo "❌ Failed to delete config cache (check permissions)<br>";
    }
} else {
    echo "ℹ️ No config cache found<br>";
}

// Step 2: Verify .env file
$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    echo "❌ .env file not found!<br>";
    exit;
}

echo "✅ .env file exists<br>";

// Step 3: Read and verify APP_KEY
$envContent = file_get_contents($envPath);
if (preg_match('/^APP_KEY=(.+)$/m', $envContent, $matches)) {
    $appKey = trim($matches[1]);
    if (!empty($appKey) && $appKey !== 'null' && $appKey !== '') {
        echo "✅ APP_KEY found in .env: " . htmlspecialchars(substr($appKey, 0, 30)) . "...<br>";
    } else {
        echo "❌ APP_KEY is empty in .env<br>";
        // Generate new key
        $newKey = 'base64:' . base64_encode(random_bytes(32));
        $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $newKey, $envContent);
        file_put_contents($envPath, $envContent);
        echo "✅ Generated new APP_KEY<br>";
    }
} else {
    echo "❌ APP_KEY not found in .env<br>";
    // Add APP_KEY
    $newKey = 'base64:' . base64_encode(random_bytes(32));
    $envContent .= "\nAPP_KEY=" . $newKey . "\n";
    file_put_contents($envPath, $envContent);
    echo "✅ Added APP_KEY to .env<br>";
}

// Step 4: Manually load .env and verify Laravel can read it
try {
    if (!defined('LARAVEL_START')) {
        define('LARAVEL_START', microtime(true));
    }
    
    require __DIR__ . '/core/vendor/autoload.php';
    
    // Load .env manually before Laravel bootstraps
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    
    $appKey = $_ENV['APP_KEY'] ?? getenv('APP_KEY') ?: null;
    if ($appKey && !empty($appKey)) {
        echo "✅ APP_KEY loaded into environment: " . htmlspecialchars(substr($appKey, 0, 30)) . "...<br>";
    } else {
        echo "❌ APP_KEY not in environment variables<br>";
    }
    
    // Now bootstrap Laravel
    $app = require __DIR__ . '/core/bootstrap/app.php';
    
    // Check if Laravel can read it
    $configKey = config('app.key');
    if ($configKey && !empty($configKey)) {
        echo "✅ Laravel can read APP_KEY from config: " . htmlspecialchars(substr($configKey, 0, 30)) . "...<br>";
    } else {
        echo "❌ Laravel cannot read APP_KEY from config<br>";
        echo "⚠️ This might be because config is still cached or .env is not being loaded<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
}

echo "<hr>";
echo "<p><strong>Next step:</strong> <a href='/'>Try accessing your site</a></p>";

