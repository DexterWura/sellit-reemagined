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
    '/usr/local/bin/composer',
    '/usr/bin/composer',
    '/opt/cpanel/composer/bin/composer', // cPanel composer
    '/usr/local/cpanel/3rdparty/bin/composer', // Another cPanel location
    __DIR__ . '/composer.phar', // Local composer.phar
];

$composerCmd = null;

// First, try to test if 'composer' command actually works
$testOutput = @shell_exec("composer --version 2>&1");
if ($testOutput && strpos($testOutput, 'Composer version') !== false) {
    $composerCmd = 'composer';
    echo "✅ Found working composer command<br>";
} else {
    // Try specific paths
    foreach ($composerPaths as $path) {
        if (file_exists($path) && is_executable($path)) {
            // Test if it actually works
            $testOutput = @shell_exec(escapeshellarg($path) . " --version 2>&1");
            if ($testOutput && strpos($testOutput, 'Composer version') !== false) {
                $composerCmd = $path;
                echo "✅ Found composer at: " . htmlspecialchars($path) . "<br>";
                break;
            }
        }
    }
}

if (!$composerCmd) {
    echo "<p style='color: red;'>❌ Composer not found or not executable!</p>";
    
    // Try to download composer.phar
    echo "<h3>Attempting to download composer.phar...</h3>";
    $composerPharPath = __DIR__ . '/composer.phar';
    
    if (!file_exists($composerPharPath)) {
        echo "<p>Downloading composer.phar...</p>";
        $composerPhar = @file_get_contents('https://getcomposer.org/composer-stable.phar');
        
        if ($composerPhar && file_put_contents($composerPharPath, $composerPhar)) {
            chmod($composerPharPath, 0755);
            $composerCmd = 'php ' . escapeshellarg($composerPharPath);
            echo "<p style='color: green;'>✅ Successfully downloaded composer.phar!</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to download composer.phar</p>";
        }
    } else {
        $composerCmd = 'php ' . escapeshellarg($composerPharPath);
        echo "<p style='color: green;'>✅ Found existing composer.phar!</p>";
    }
    
    if (!$composerCmd) {
        echo "<h3>Manual Options:</h3>";
        echo "<ol>";
        echo "<li><strong>Upload vendor directory manually:</strong> Upload the entire <code>core/vendor</code> folder from your local machine (MOST RELIABLE)</li>";
        echo "<li><strong>Download composer.phar manually:</strong> Download from <a href='https://getcomposer.org/download/' target='_blank'>getcomposer.org</a> and upload to root directory</li>";
        echo "<li><strong>Use SSH/Terminal:</strong> If you have SSH access, run: <code>cd core && composer install --no-dev</code></li>";
        echo "</ol>";
        exit;
    }
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
// Use full path and ensure we're in the right directory
$command = "cd " . escapeshellarg($corePath) . " && " . $composerCmd . " install --no-dev --optimize-autoloader 2>&1";

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
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);
    
    $startTime = time();
    $timeout = 300; // 5 minutes
    
    while (true) {
        // Check timeout
        if (time() - $startTime > $timeout) {
            echo "<br><span style='color: red;'>⏱️ Timeout reached. Process may still be running...</span><br>";
            break;
        }
        
        // Check if process is still running
        $status = proc_get_status($process);
        if (!$status['running']) {
            // Read remaining output
            while (!feof($pipes[1])) {
                $line = fgets($pipes[1]);
                if ($line !== false) {
                    echo htmlspecialchars($line) . "<br>";
                    $output .= $line;
                }
            }
            while (!feof($pipes[2])) {
                $line = fgets($pipes[2]);
                if ($line !== false) {
                    echo "<span style='color: #f00;'>" . htmlspecialchars($line) . "</span><br>";
                    $error .= $line;
                }
            }
            break;
        }
        
        // Read available output
        $read = [$pipes[1], $pipes[2]];
        $write = null;
        $except = null;
        
        if (stream_select($read, $write, $except, 1) > 0) {
            foreach ($read as $pipe) {
                $line = fgets($pipe);
                if ($line !== false) {
                    if ($pipe === $pipes[1]) {
                        echo htmlspecialchars($line) . "<br>";
                        $output .= $line;
                    } else {
                        echo "<span style='color: #f00;'>" . htmlspecialchars($line) . "</span><br>";
                        $error .= $line;
                    }
                    @flush();
                }
            }
        }
        
        usleep(100000); // 0.1 second
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

