<?php
/**
 * Script to install vendor dependencies via Composer
 * WARNING: This requires Composer to be installed and proper permissions
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // 5 minutes timeout

echo "<h1>Install Vendor Dependencies</h1>";
echo "<hr>";

$corePath = __DIR__ . '/core';
$vendorPath = $corePath . '/vendor';
$composerJsonPath = $corePath . '/composer.json';

// Check prerequisites
echo "<h2>Prerequisites Check</h2>";

if (!file_exists($composerJsonPath)) {
    die("<p style='color: red;'>❌ composer.json not found at: " . htmlspecialchars($composerJsonPath) . "</p>");
}
echo "✅ composer.json exists<br>";

if (!is_dir($corePath)) {
    die("<p style='color: red;'>❌ core directory not found</p>");
}
echo "✅ core directory exists<br>";

// Check if vendor already exists
if (is_dir($vendorPath) && file_exists($vendorPath . '/autoload.php')) {
    echo "<p style='color: green;'>✅ Vendor directory already exists and appears complete!</p>";
    echo "<p>No installation needed. <a href='test.php'>Go to diagnostic test</a></p>";
    exit;
}

echo "<hr>";

// Try to find Composer
echo "<h2>Finding Composer</h2>";

$composerPaths = [
    'composer', // Global composer
    '/usr/local/bin/composer',
    '/usr/bin/composer',
    '/opt/cpanel/composer/bin/composer', // cPanel composer
    __DIR__ . '/composer.phar', // Local composer.phar
];

$composerCmd = null;

foreach ($composerPaths as $path) {
    if ($path === 'composer') {
        // Try to execute composer directly
        $test = @shell_exec("which composer 2>&1");
        if ($test && strpos($test, 'composer') !== false && strpos($test, 'not found') === false) {
            $composerCmd = 'composer';
            echo "✅ Found composer in PATH<br>";
            break;
        }
    } elseif (file_exists($path)) {
        $composerCmd = $path;
        echo "✅ Found composer at: " . htmlspecialchars($path) . "<br>";
        break;
    }
}

if (!$composerCmd) {
    echo "<p style='color: red;'>❌ Composer not found!</p>";
    echo "<h3>Options:</h3>";
    echo "<ol>";
    echo "<li><strong>Upload vendor directory manually:</strong> Upload the entire <code>core/vendor</code> folder from your local machine</li>";
    echo "<li><strong>Install Composer:</strong> Download composer.phar to the root directory, then refresh this page</li>";
    echo "<li><strong>Use SSH/Terminal:</strong> If you have SSH access, run: <code>cd core && composer install --no-dev</code></li>";
    echo "</ol>";
    exit;
}

echo "<hr>";

// Check if we can execute commands
echo "<h2>Permission Check</h2>";

if (!is_writable($corePath)) {
    echo "<p style='color: red;'>❌ core directory is not writable!</p>";
    echo "<p>Please set proper permissions (755 for directory, 644 for files) or contact your hosting provider.</p>";
    exit;
}
echo "✅ core directory is writable<br>";

echo "<hr>";

// Attempt installation
echo "<h2>Installing Dependencies</h2>";
echo "<p>This may take several minutes. Please be patient...</p>";
echo "<p><strong>Command:</strong> <code>" . htmlspecialchars($composerCmd) . " install --no-dev --optimize-autoloader</code></p>";
echo "<p>Working directory: <code>" . htmlspecialchars($corePath) . "</code></p>";

// Change to core directory and run composer
$command = "cd " . escapeshellarg($corePath) . " && " . escapeshellarg($composerCmd) . " install --no-dev --optimize-autoloader 2>&1";

echo "<div style='background: #000; color: #0f0; padding: 10px; font-family: monospace; max-height: 400px; overflow-y: auto;'>";
echo "<strong>Output:</strong><br>";

// Execute command and stream output
$descriptorspec = array(
    0 => array("pipe", "r"),  // stdin
    1 => array("pipe", "w"),  // stdout
    2 => array("pipe", "w")   // stderr
);

$process = proc_open($command, $descriptorspec, $pipes);

if (is_resource($process)) {
    // Close stdin
    fclose($pipes[0]);
    
    // Read output
    $output = '';
    $error = '';
    
    // Read stdout
    while (!feof($pipes[1])) {
        $line = fgets($pipes[1]);
        if ($line !== false) {
            echo htmlspecialchars($line) . "<br>";
            flush();
            ob_flush();
            $output .= $line;
        }
    }
    
    // Read stderr
    while (!feof($pipes[2])) {
        $line = fgets($pipes[2]);
        if ($line !== false) {
            echo "<span style='color: #f00;'>" . htmlspecialchars($line) . "</span><br>";
            flush();
            ob_flush();
            $error .= $line;
        }
    }
    
    // Close pipes
    fclose($pipes[1]);
    fclose($pipes[2]);
    
    // Get exit code
    $returnValue = proc_close($process);
    
    echo "</div>";
    
    echo "<hr>";
    
    if ($returnValue === 0) {
        // Check if autoload.php was created
        if (file_exists($vendorPath . '/autoload.php')) {
            echo "<p style='color: green; font-size: 18px;'><strong>✅ SUCCESS! Vendor directory installed successfully!</strong></p>";
            echo "<p><a href='test.php'>Go to diagnostic test to verify</a></p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Installation completed but autoload.php not found. Please check the output above for errors.</p>";
        }
    } else {
        echo "<p style='color: red;'><strong>❌ Installation failed with exit code: $returnValue</strong></p>";
        echo "<p>Please check the output above for error messages.</p>";
        if ($error) {
            echo "<h3>Errors:</h3>";
            echo "<pre style='background: #fee; padding: 10px;'>" . htmlspecialchars($error) . "</pre>";
        }
    }
} else {
    echo "</div>";
    echo "<p style='color: red;'>❌ Failed to execute command. proc_open() may be disabled.</p>";
    echo "<p>You may need to:</p>";
    echo "<ul>";
    echo "<li>Enable proc_open() in PHP configuration</li>";
    echo "<li>Use SSH/Terminal to run composer manually</li>";
    echo "<li>Upload the vendor directory manually</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='fix-vendor.php'>← Back to Vendor Fix</a> | <a href='test.php'>Diagnostic Test</a></p>";

