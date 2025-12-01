<?php
/**
 * Script to create a minimal .env file
 * Run this once to generate the .env file with required settings
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Create .env File</h1>";

$envPath = __DIR__ . '/.env';

// Check if .env already exists
if (file_exists($envPath)) {
    echo "<p style='color: orange;'>⚠️ .env file already exists!</p>";
    echo "<p>If you want to recreate it, please delete the existing file first.</p>";
    exit;
}

// Generate APP_KEY
echo "<p>Generating APP_KEY...</p>";
$appKey = 'base64:' . base64_encode(random_bytes(32));

// Create minimal .env content
$envContent = <<<ENV
APP_NAME="Escrow"
APP_ENV=production
APP_KEY={$appKey}
APP_DEBUG=false
APP_TIMEZONE=Africa/Harare
APP_URL=https://escrow.dextersoft.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="Escrow"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="Escrow"
ENV;

// Write .env file
if (file_put_contents($envPath, $envContent)) {
    echo "<p style='color: green;'>✅ .env file created successfully!</p>";
    echo "<p><strong>Important:</strong> You need to fill in your database credentials:</p>";
    echo "<ul>";
    echo "<li>DB_DATABASE - Your database name</li>";
    echo "<li>DB_USERNAME - Your database username</li>";
    echo "<li>DB_PASSWORD - Your database password</li>";
    echo "</ul>";
    echo "<p>Also update APP_URL if needed.</p>";
    echo "<p style='color: red;'><strong>⚠️ Security:</strong> Make sure .env file permissions are set correctly (should not be publicly readable).</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to create .env file. Please check file permissions.</p>";
    echo "<p>Try creating it manually with the following content:</p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
    echo htmlspecialchars($envContent);
    echo "</pre>";
}

