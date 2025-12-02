<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Laravel Application Entry Point
|--------------------------------------------------------------------------
|
| This file serves as the entry point for all HTTP requests to the
| application. It handles environment setup, maintenance mode, and
| bootstrapping Laravel.
|
*/

// Suppress deprecation warnings from vendor packages (PHP 8.4 compatibility)
error_reporting(E_ALL & ~E_DEPRECATED);

define('LARAVEL_START', microtime(true));

// Check if .env file exists before proceeding
$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    // Redirect to installer if .env is missing
    header('Location: /install');
    exit;
}

// Determine if the application is in maintenance mode...
$maintenance = __DIR__.'/core/storage/framework/maintenance.php';
if (file_exists($maintenance)) {
    require $maintenance;
}

// Register the Composer autoloader...
$autoloader = __DIR__.'/core/vendor/autoload.php';
if (!file_exists($autoloader)) {
    http_response_code(500);
    die('Composer autoloader not found. Please run "composer install" in the core directory.');
}

require $autoloader;

// CRITICAL: Load .env file BEFORE bootstrapping Laravel
// This ensures APP_KEY is available when EncryptionServiceProvider runs
// Delete config cache if it exists to force fresh .env load
$configCachePath = __DIR__ . '/core/bootstrap/cache/config.php';
if (file_exists($configCachePath)) {
    @unlink($configCachePath);
}

// Load .env manually to ensure it's available before Laravel bootstraps
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Dotenv\Exception\InvalidFileException $e) {
    // Invalid .env file - redirect to installer
    header('Location: /install');
    exit;
} catch (Exception $e) {
    // If .env loading fails, Laravel will try again during bootstrap
    // Log error in production but don't die
    if (function_exists('error_log')) {
        error_log('Warning: Failed to preload .env file: ' . $e->getMessage());
    }
}

// Bootstrap Laravel and handle the request...
try {
    $app = require_once __DIR__.'/core/bootstrap/app.php';
    $app->handleRequest(Request::capture());
} catch (Throwable $e) {
    // Handle fatal errors gracefully
    http_response_code(500);
    
    // In production, show generic error; in development, show details
    $isProduction = (getenv('APP_ENV') === 'production' || getenv('APP_DEBUG') === 'false');
    
    if (!$isProduction) {
        echo '<h1>Application Error</h1>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
        echo '<p><strong>Line:</strong> ' . $e->getLine() . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        echo '<h1>Internal Server Error</h1>';
        echo '<p>Something went wrong. Please contact the administrator.</p>';
    }
    
    // Log the error
    if (function_exists('error_log')) {
        error_log('Fatal Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    }
    
    exit(1);
}
