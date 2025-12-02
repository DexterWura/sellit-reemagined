<?php

use Illuminate\Http\Request;

// Suppress deprecation warnings from vendor packages (PHP 8.4 compatibility)
error_reporting(E_ALL & ~E_DEPRECATED);

define('LARAVEL_START', microtime(true));

// Check if .env file exists before proceeding
$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    // Redirect to installer
    header('Location: /install');
    exit;
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/core/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/core/vendor/autoload.php';

// CRITICAL: Load .env file BEFORE bootstrapping Laravel
// This ensures APP_KEY is available when EncryptionServiceProvider runs
// Delete config cache if it exists to force fresh .env load
$configCachePath = __DIR__ . '/core/bootstrap/cache/config.php';
if (file_exists($configCachePath)) {
    @unlink($configCachePath);
}

// Load .env manually to ensure it's available
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Exception $e) {
    // If .env loading fails, Laravel will try again, but at least we tried
}

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/core/bootstrap/app.php')
    ->handleRequest(Request::capture());
