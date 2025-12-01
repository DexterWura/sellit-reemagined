<?php
/**
 * Script to diagnose and help fix missing vendor directory
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Vendor Directory Fix</h1>";
echo "<hr>";

$vendorPath = __DIR__ . '/core/vendor';
$autoloadPath = $vendorPath . '/autoload.php';
$composerJsonPath = __DIR__ . '/core/composer.json';

echo "<h2>Diagnosis</h2>";

// Check if vendor directory exists
if (is_dir($vendorPath)) {
    echo "✅ Vendor directory exists<br>";
    
    // Check if autoload.php exists
    if (file_exists($autoloadPath)) {
        echo "✅ autoload.php exists<br>";
        echo "<p style='color: green;'><strong>Vendor directory appears to be intact!</strong></p>";
    } else {
        echo "❌ autoload.php is missing<br>";
        echo "<p style='color: red;'><strong>Vendor directory exists but autoload.php is missing. This is unusual.</strong></p>";
    }
} else {
    echo "❌ Vendor directory does NOT exist<br>";
    echo "<p style='color: red;'><strong>This is the problem! The vendor directory needs to be restored.</strong></p>";
}

echo "<hr>";

// Check if composer.json exists
if (file_exists($composerJsonPath)) {
    echo "✅ composer.json exists<br>";
} else {
    echo "❌ composer.json is missing<br>";
}

echo "<hr>";

echo "<h2>Solutions</h2>";

echo "<h3>Option 1: Upload Vendor Directory (Recommended if you have it locally)</h3>";
echo "<p>If you have the vendor directory on your local machine:</p>";
echo "<ol>";
echo "<li>Upload the entire <code>core/vendor</code> directory to your server</li>";
echo "<li>Make sure <code>core/vendor/autoload.php</code> exists</li>";
echo "<li>Refresh this page to verify</li>";
echo "</ol>";

echo "<h3>Option 2: Install via Composer (If you have SSH access)</h3>";
echo "<p>If you have SSH access to your server, run:</p>";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
echo "cd /home/dexterso/public_html/escrow.dextersoft.com/core\n";
echo "composer install --no-dev --optimize-autoloader";
echo "</pre>";

echo "<h3>Option 3: Install via cPanel Terminal (If available)</h3>";
echo "<p>If your hosting provider has cPanel with Terminal access:</p>";
echo "<ol>";
echo "<li>Open cPanel Terminal</li>";
echo "<li>Navigate to: <code>cd public_html/escrow.dextersoft.com/core</code></li>";
echo "<li>Run: <code>composer install --no-dev --optimize-autoloader</code></li>";
echo "</ol>";

echo "<h3>Option 4: Use Composer via Web (Advanced)</h3>";
echo "<p>If Composer is installed on your server but you don't have SSH:</p>";
echo "<p>You can try running composer via PHP exec, but this requires proper permissions.</p>";

echo "<hr>";

// Try to check if composer is available
echo "<h2>Check Composer Availability</h2>";
$composerPhar = __DIR__ . '/composer.phar';
$composerGlobal = shell_exec('which composer 2>&1');

if (file_exists($composerPhar)) {
    echo "✅ composer.phar found in root directory<br>";
    echo "<p>You can try running: <code>php composer.phar install</code> in the core directory</p>";
} else {
    echo "❌ composer.phar not found<br>";
}

if ($composerGlobal && strpos($composerGlobal, 'composer') !== false) {
    echo "✅ Composer appears to be installed globally<br>";
    echo "<p>Composer path: " . htmlspecialchars(trim($composerGlobal)) . "</p>";
} else {
    echo "❌ Composer not found in PATH<br>";
}

echo "<hr>";

echo "<h2>Current Status</h2>";
echo "<p><strong>Vendor Path:</strong> " . htmlspecialchars($vendorPath) . "</p>";
echo "<p><strong>Autoload Path:</strong> " . htmlspecialchars($autoloadPath) . "</p>";
echo "<p><strong>Vendor Exists:</strong> " . (is_dir($vendorPath) ? "Yes" : "No") . "</p>";
echo "<p><strong>Autoload Exists:</strong> " . (file_exists($autoloadPath) ? "Yes" : "No") . "</p>";

echo "<hr>";
echo "<p><a href='test.php'>← Back to Diagnostic Test</a></p>";

