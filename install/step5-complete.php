<?php
// Step 5: Installation Complete

// Verify installation before clearing session
$installed = false;
$verificationErrors = [];

try {
    define('LARAVEL_START', microtime(true));
    require __DIR__ . '/../core/vendor/autoload.php';
    $app = require_once __DIR__ . '/../core/bootstrap/app.php';
    
    // Try to get cache
    try {
        $cache = $app->make('cache');
        $installed = $cache->get('SystemInstalled');
    } catch (Exception $e) {
        // Try alternative verification
        $cacheFile = __DIR__ . '/../core/storage/framework/cache/data/SystemInstalled';
        $installed = file_exists($cacheFile);
    }
    
    // Also verify .env exists and has content
    $envPath = __DIR__ . '/../.env';
    if (!file_exists($envPath) || empty(file_get_contents($envPath))) {
        $verificationErrors[] = '.env file is missing or empty';
    }
} catch (Exception $e) {
    $verificationErrors[] = 'Could not verify installation: ' . $e->getMessage();
}

// Clear session only after verification
session_destroy();
?>

<h2>Step 5: Installation Complete! 🎉</h2>

<?php if ($installed && empty($verificationErrors)): ?>
    <div class="alert alert-success">
        <h3>✅ Installation Successful!</h3>
        <p>Your Escrow System has been successfully installed and configured.</p>
    </div>

    <h3>Next Steps:</h3>
    <ol>
        <li><strong>Create Admin Account:</strong> You may need to create an admin account through your database or a setup script.</li>
        <li><strong>Configure Settings:</strong> Log in to the admin panel and configure your general settings.</li>
        <li><strong>Set Up Payment Gateways:</strong> Configure your payment gateway settings in the admin panel.</li>
        <li><strong>Review Security:</strong> Make sure your .env file has proper permissions and is not publicly accessible.</li>
    </ol>

    <div class="alert alert-info" style="margin-top: 20px;">
        <strong>⚠️ Security Reminder:</strong>
        <ul style="margin-top: 10px; padding-left: 20px;">
            <li>Delete or protect the <code>/install</code> directory</li>
            <li>Ensure .env file is not publicly accessible</li>
            <li>Set proper file permissions (755 for directories, 644 for files)</li>
            <li>Keep your application updated</li>
        </ul>
    </div>

    <div style="margin-top: 30px;">
        <a href="/" class="btn btn-success btn-lg">Go to Website →</a>
        <a href="/admin" class="btn btn-primary btn-lg">Go to Admin Panel →</a>
    </div>
<?php else: ?>
    <div class="alert alert-danger">
        <h3>❌ Installation Verification Failed</h3>
        <p>The installation may not have completed successfully.</p>
        <?php if (!empty($verificationErrors)): ?>
            <ul style="margin-top: 10px; padding-left: 20px;">
                <?php foreach ($verificationErrors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <div style="margin-top: 30px;">
        <a href="?step=4&force=1" class="btn btn-primary">Retry Installation</a>
        <a href="?step=1&force=1" class="btn">Start Over</a>
    </div>
<?php endif; ?>

