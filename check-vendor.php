<?php
/**
 * Quick check if vendor directory is installed
 */

$vendorPath = __DIR__ . '/core/vendor';
$autoloadPath = $vendorPath . '/autoload.php';

header('Content-Type: application/json');

$result = [
    'vendor_exists' => is_dir($vendorPath),
    'autoload_exists' => file_exists($autoloadPath),
    'vendor_size' => is_dir($vendorPath) ? count(glob($vendorPath . '/*', GLOB_ONLYDIR)) : 0,
    'status' => 'unknown'
];

if ($result['autoload_exists']) {
    $result['status'] = 'complete';
    $result['message'] = 'Vendor directory is installed and ready!';
} elseif ($result['vendor_exists']) {
    $result['status'] = 'partial';
    $result['message'] = 'Vendor directory exists but autoload.php is missing. Installation may be in progress.';
} else {
    $result['status'] = 'missing';
    $result['message'] = 'Vendor directory is missing. Installation needed.';
}

echo json_encode($result, JSON_PRETTY_PRINT);

