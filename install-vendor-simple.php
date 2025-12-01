<?php
/**
 * Simplified script to install vendor dependencies
 * This version runs composer and shows output more reliably
 */

// Disable output buffering
if (ob_get_level()) {
    ob_end_clean();
}

// Set headers
header('Content-Type: text/html; charset=utf-8');

// Increase time limits
set_time_limit(600); // 10 minutes
ini_set('max_execution_time', 600);

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Installing Vendor Dependencies</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        .output { background: #000; color: #0f0; padding: 15px; font-family: monospace; 
                  font-size: 12px; max-height: 500px; overflow-y: auto; white-space: pre-wrap; }
        .error { color: #f00; }
        .success { color: #0f0; font-weight: bold; }
        .info { color: #ff0; }
    </style>
</head>
<body>
<div class="container">
    <h1>Installing Vendor Dependencies</h1>
    <p>This may take 5-10 minutes. Please wait...</p>
    <div class="output" id="output">Starting installation...\n</div>
</div>

<?php
// Flush output immediately
flush();

$corePath = __DIR__ . '/core';
$vendorPath = $corePath . '/vendor';
$composerPhar = __DIR__ . '/composer.phar';

// Check if composer.phar exists
if (!file_exists($composerPhar)) {
    echo "<script>document.getElementById('output').innerHTML += '<span class=\"error\">❌ composer.phar not found! Please run install-vendor.php first to download it.</span>\\n';</script>";
    flush();
    exit;
}

// Function to output to both browser and log
function output($message, $type = 'info') {
    $color = $type === 'error' ? '#f00' : ($type === 'success' ? '#0f0' : '#0ff');
    $escaped = htmlspecialchars($message);
    echo "<script>document.getElementById('output').innerHTML += '<span style=\"color: " . $color . "\">" . addslashes($escaped) . "</span>\\n';</script>";
    flush();
    @ob_flush();
}

output("✅ Found composer.phar");
output("📁 Working directory: " . $corePath);
output("🚀 Starting composer install...\n");

// Change to core directory
chdir($corePath);

// Build command
$command = "php " . escapeshellarg($composerPhar) . " install --no-dev --optimize-autoloader 2>&1";

output("Command: " . $command . "\n");

// Execute command
$output = [];
$returnVar = 0;

// Use popen for better streaming
$handle = popen($command, 'r');

if ($handle) {
    while (!feof($handle)) {
        $line = fgets($handle);
        if ($line !== false) {
            $line = rtrim($line);
            if (!empty($line)) {
                // Check for errors
                if (stripos($line, 'error') !== false || stripos($line, 'failed') !== false) {
                    output($line, 'error');
                } elseif (stripos($line, 'success') !== false || stripos($line, 'complete') !== false) {
                    output($line, 'success');
                } else {
                    output($line);
                }
                $output[] = $line;
            }
        }
        // Small delay to prevent overwhelming the browser
        usleep(50000); // 0.05 seconds
    }
    $returnVar = pclose($handle);
} else {
    output("❌ Failed to execute command. popen() may be disabled.", 'error');
    output("Try running via SSH: cd core && php ../composer.phar install --no-dev", 'error');
    exit;
}

output("\n" . str_repeat("=", 60) . "\n");

// Check result
if ($returnVar === 0) {
    if (file_exists($vendorPath . '/autoload.php')) {
        output("✅ SUCCESS! Vendor directory installed successfully!", 'success');
        output("✅ autoload.php found at: " . $vendorPath . '/autoload.php', 'success');
        output("\n🎉 Installation complete! You can now test your site.", 'success');
        echo "<script>setTimeout(function(){ window.location.href='test.php'; }, 3000);</script>";
    } else {
        output("⚠️ Installation completed but autoload.php not found.", 'error');
        output("Please check the output above for errors.", 'error');
    }
} else {
    output("❌ Installation failed with exit code: " . $returnVar, 'error');
    output("Please check the output above for error messages.", 'error');
    output("\nIf this continues to fail, please upload the vendor directory manually.", 'error');
}

output("\n" . str_repeat("=", 60));
output("Installation process finished.");
?>

<script>
// Auto-scroll output
var outputDiv = document.getElementById('output');
function scrollToBottom() {
    outputDiv.scrollTop = outputDiv.scrollHeight;
}
setInterval(scrollToBottom, 500);
</script>

</body>
</html>

