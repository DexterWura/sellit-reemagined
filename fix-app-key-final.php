<?php
/**
 * Final fix for APP_KEY issue
 * This ensures .env is loaded and config cache is cleared
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Fix APP_KEY - Final Solution</h1>";
echo "<hr>";

$basePath = __DIR__;
$envPath = $basePath . '/.env';
$configCachePath = $basePath . '/core/bootstrap/cache/config.php';

// Step 1: Verify .env exists
if (!file_exists($envPath)) {
    die("❌ .env file not found at: $envPath");
}
echo "✅ .env file exists<br>";

// Step 2: Read and verify APP_KEY
$envContent = file_get_contents($envPath);
$appKeyPattern = '/^APP_KEY=(.+)$/m';

if (!preg_match($appKeyPattern, $envContent, $matches)) {
    // Generate and add APP_KEY
    $newKey = 'base64:' . base64_encode(random_bytes(32));
    if (preg_match('/^APP_NAME=/m', $envContent)) {
        $envContent = preg_replace('/^(APP_NAME=.*)$/m', '$1' . "\nAPP_KEY=" . $newKey, $envContent);
    } else {
        $envContent = "APP_KEY=" . $newKey . "\n" . $envContent;
    }
    file_put_contents($envPath, $envContent);
    echo "✅ Generated and added APP_KEY<br>";
} else {
    $appKey = trim($matches[1]);
    if (empty($appKey) || $appKey === 'null' || $appKey === '') {
        // Replace empty key
        $newKey = 'base64:' . base64_encode(random_bytes(32));
        $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $newKey, $envContent);
        file_put_contents($envPath, $envContent);
        echo "✅ Replaced empty APP_KEY with new one<br>";
    } else {
        echo "✅ APP_KEY exists: " . htmlspecialchars(substr($appKey, 0, 30)) . "...<br>";
    }
}

// Step 3: Delete config cache
if (file_exists($configCachePath)) {
    if (unlink($configCachePath)) {
        echo "✅ Deleted config cache<br>";
    } else {
        echo "⚠️ Could not delete config cache (permissions?)<br>";
    }
} else {
    echo "✅ No config cache found<br>";
}

// Step 4: Delete other cache files
$cacheFiles = [
    $basePath . '/core/bootstrap/cache/services.php',
    $basePath . '/core/bootstrap/cache/packages.php',
];

foreach ($cacheFiles as $cacheFile) {
    if (file_exists($cacheFile)) {
        @unlink($cacheFile);
    }
}

// Step 5: Load .env manually and bootstrap Laravel to verify
try {
    if (!defined('LARAVEL_START')) {
        define('LARAVEL_START', microtime(true));
    }
    
    // Load autoloader
    require $basePath . '/core/vendor/autoload.php';
    
    // IMPORTANT: Load .env BEFORE bootstrapping Laravel
    // This ensures APP_KEY is available when EncryptionServiceProvider runs
    $dotenv = Dotenv\Dotenv::createImmutable($basePath);
    $dotenv->load();
    
    // Verify APP_KEY is in environment
    $envKey = $_ENV['APP_KEY'] ?? getenv('APP_KEY') ?: null;
    if ($envKey && !empty($envKey)) {
        echo "✅ APP_KEY loaded into environment<br>";
    } else {
        echo "❌ APP_KEY not in environment (this is the problem!)<br>";
        exit;
    }
    
    // Now bootstrap Laravel
    $app = require $basePath . '/core/bootstrap/app.php';
    
    // Verify Laravel can read it
    $configKey = $app->make('config')->get('app.key');
    if ($configKey && !empty($configKey)) {
        echo "✅ Laravel can read APP_KEY from config<br>";
        echo "<p style='color: green; font-weight: bold;'>✅ SUCCESS! APP_KEY is working.</p>";
    } else {
        echo "❌ Laravel cannot read APP_KEY from config<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p><strong>Next step:</strong> <a href='/'>Try accessing your site</a></p>";

