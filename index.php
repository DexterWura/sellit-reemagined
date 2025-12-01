<?php

use Illuminate\Http\Request;

// Suppress deprecation warnings from vendor packages (PHP 8.4 compatibility)
error_reporting(E_ALL & ~E_DEPRECATED);

define('LARAVEL_START', microtime(true));

// Check if .env file exists before proceeding
$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    // Try to redirect to installer or show helpful message
    if (file_exists(__DIR__ . '/install')) {
        header('Location: /install');
        exit;
    }
    http_response_code(500);
    die('Error: .env file is missing. Please create it using create-env.php or run the installer.');
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/core/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/core/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/core/bootstrap/app.php')
    ->handleRequest(Request::capture());
