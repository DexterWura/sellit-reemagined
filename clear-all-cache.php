<?php
/**
 * Clear all Laravel caches manually
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Clear All Caches</h1>";
echo "<hr>";

$cacheDir = __DIR__ . '/core/bootstrap/cache';
$storageCacheDir = __DIR__ . '/core/storage/framework/cache';

$deleted = 0;
$errors = 0;

// Delete config cache
$configCache = $cacheDir . '/config.php';
if (file_exists($configCache)) {
    if (unlink($configCache)) {
        echo "✅ Deleted: bootstrap/cache/config.php<br>";
        $deleted++;
    } else {
        echo "❌ Failed to delete: bootstrap/cache/config.php<br>";
        $errors++;
    }
} else {
    echo "ℹ️ Not found: bootstrap/cache/config.php<br>";
}

// Delete services cache
$servicesCache = $cacheDir . '/services.php';
if (file_exists($servicesCache)) {
    if (unlink($servicesCache)) {
        echo "✅ Deleted: bootstrap/cache/services.php<br>";
        $deleted++;
    } else {
        echo "❌ Failed to delete: bootstrap/cache/services.php<br>";
        $errors++;
    }
} else {
    echo "ℹ️ Not found: bootstrap/cache/services.php<br>";
}

// Delete packages cache
$packagesCache = $cacheDir . '/packages.php';
if (file_exists($packagesCache)) {
    if (unlink($packagesCache)) {
        echo "✅ Deleted: bootstrap/cache/packages.php<br>";
        $deleted++;
    } else {
        echo "❌ Failed to delete: bootstrap/cache/packages.php<br>";
        $errors++;
    }
} else {
    echo "ℹ️ Not found: bootstrap/cache/packages.php<br>";
}

// Clear storage cache
if (is_dir($storageCacheDir)) {
    $files = glob($storageCacheDir . '/data/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            if (unlink($file)) {
                $deleted++;
            } else {
                $errors++;
            }
        }
    }
    echo "✅ Cleared storage/framework/cache/data/ directory<br>";
}

echo "<hr>";
echo "<p><strong>Deleted:</strong> $deleted files</p>";
if ($errors > 0) {
    echo "<p style='color: orange;'><strong>Errors:</strong> $errors files could not be deleted (check permissions)</p>";
}

echo "<hr>";
echo "<p><a href='/'>Try accessing your site now</a></p>";

