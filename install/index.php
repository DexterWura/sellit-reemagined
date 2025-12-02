<?php
/**
 * Application Installer
 * This will guide you through the installation process
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300);

// Prevent direct access if already installed (unless force parameter)
$force = isset($_GET['force']) && $_GET['force'] === '1';

session_start();

// Step tracking
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$maxStep = 5;

// Check if already installed
if (!$force) {
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath) && file_exists(__DIR__ . '/../core/vendor/autoload.php')) {
        try {
            if (!defined('LARAVEL_START')) {
                define('LARAVEL_START', microtime(true));
            }
            require __DIR__ . '/../core/vendor/autoload.php';
            $app = require __DIR__ . '/../core/bootstrap/app.php';
            // Handle case where require_once was used elsewhere
            if (is_object($app) && method_exists($app, 'make')) {
                $cache = $app->make('cache');
                if ($cache->get('SystemInstalled')) {
                    header('Location: /');
                    exit;
                }
            }
        } catch (Exception $e) {
            // If Laravel can't bootstrap, allow installation to proceed
            // This handles cases where .env exists but is invalid
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Escrow System</title>
    <link rel="stylesheet" href="/assets/global/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/global/css/installer.css">
    <style>
        body { background: #f5f5f5; padding: 20px; }
        .install-container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .step-indicator { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .step { flex: 1; text-align: center; padding: 10px; position: relative; }
        .step.active { color: #007bff; font-weight: bold; }
        .step.completed { color: #28a745; }
        .step::after { content: ''; position: absolute; top: 50%; right: -50%; width: 100%; height: 2px; background: #ddd; z-index: -1; }
        .step:last-child::after { display: none; }
        .form-group { margin-bottom: 20px; }
        .form-group label { font-weight: 600; margin-bottom: 5px; display: block; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .check-item { padding: 10px; margin: 5px 0; border-left: 3px solid #ddd; }
        .check-item.success { border-color: #28a745; background: #d4edda; }
        .check-item.error { border-color: #dc3545; background: #f8d7da; }
    </style>
</head>
<body>
    <div class="install-container">
        <h1>Escrow System Installation</h1>
        <p class="text-muted">Welcome to the installation wizard. This will guide you through setting up your application.</p>
        
        <div class="step-indicator">
            <div class="step <?= $step >= 1 ? 'active' : '' ?> <?= $step > 1 ? 'completed' : '' ?>">1. Requirements</div>
            <div class="step <?= $step >= 2 ? 'active' : '' ?> <?= $step > 2 ? 'completed' : '' ?>">2. Database</div>
            <div class="step <?= $step >= 3 ? 'active' : '' ?> <?= $step > 3 ? 'completed' : '' ?>">3. Configuration</div>
            <div class="step <?= $step >= 4 ? 'active' : '' ?> <?= $step > 4 ? 'completed' : '' ?>">4. Install</div>
            <div class="step <?= $step >= 5 ? 'active' : '' ?>">5. Complete</div>
        </div>

        <hr>

        <?php
        // Step 1: Requirements Check
        if ($step == 1) {
            include __DIR__ . '/step1-requirements.php';
        }
        // Step 2: Database Configuration
        elseif ($step == 2) {
            include __DIR__ . '/step2-database.php';
        }
        // Step 3: Application Configuration
        elseif ($step == 3) {
            include __DIR__ . '/step3-config.php';
        }
        // Step 4: Installation
        elseif ($step == 4) {
            include __DIR__ . '/step4-install.php';
        }
        // Step 5: Complete
        elseif ($step == 5) {
            include __DIR__ . '/step5-complete.php';
        }
        ?>
    </div>
</body>
</html>

