<?php
// Step 1: Requirements Check

$checks = [];
$allPassed = true;

// PHP Version
$phpVersion = PHP_VERSION;
$phpRequired = '8.3';
$checks['php'] = version_compare($phpVersion, $phpRequired, '>=');

// Required Extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'curl', 'zip', 'gd', 'fileinfo'];
foreach ($requiredExtensions as $ext) {
    $checks['ext_' . $ext] = extension_loaded($ext);
}

// File Permissions
$writableDirs = [
    'core/storage',
    'core/bootstrap/cache',
    'core/storage/framework',
    'core/storage/logs',
];
foreach ($writableDirs as $dir) {
    $fullPath = __DIR__ . '/../' . $dir;
    // Check if directory exists and is writable, or if parent is writable so we can create it
    if (is_dir($fullPath)) {
        $checks['writable_' . str_replace('/', '_', $dir)] = is_writable($fullPath);
    } else {
        // Directory doesn't exist, check if parent is writable
        $parentDir = dirname($fullPath);
        $checks['writable_' . str_replace('/', '_', $dir)] = is_dir($parentDir) && is_writable($parentDir);
    }
}

// Vendor Directory
$vendorPath = __DIR__ . '/../core/vendor/autoload.php';
$checks['vendor'] = file_exists($vendorPath);

// .env file (optional at this stage)
$envPath = __DIR__ . '/../.env';
$checks['env'] = file_exists($envPath);

$allPassed = !in_array(false, $checks);
?>

<h2>Step 1: System Requirements</h2>

<?php if ($allPassed): ?>
    <div class="alert alert-success">
        <strong>✅ All requirements met!</strong> You can proceed to the next step.
    </div>
<?php else: ?>
    <div class="alert alert-danger">
        <strong>❌ Some requirements are not met.</strong> Please fix the issues below before proceeding.
    </div>
<?php endif; ?>

<h3>PHP Requirements</h3>
<div class="check-item <?= $checks['php'] ? 'success' : 'error' ?>">
    <?= $checks['php'] ? '✅' : '❌' ?> PHP Version: <?= $phpVersion ?> (Required: <?= $phpRequired ?>+)
</div>

<h3>Required PHP Extensions</h3>
<?php foreach ($requiredExtensions as $ext): ?>
    <div class="check-item <?= $checks['ext_' . $ext] ? 'success' : 'error' ?>">
        <?= $checks['ext_' . $ext] ? '✅' : '❌' ?> <?= $ext ?>
    </div>
<?php endforeach; ?>

<h3>File Permissions</h3>
<?php foreach ($writableDirs as $dir): ?>
    <div class="check-item <?= $checks['writable_' . str_replace('/', '_', $dir)] ? 'success' : 'error' ?>">
        <?= $checks['writable_' . str_replace('/', '_', $dir)] ? '✅' : '❌' ?> <?= $dir ?> (writable)
    </div>
<?php endforeach; ?>

<h3>Application Files</h3>
<div class="check-item <?= $checks['vendor'] ? 'success' : 'error' ?>">
    <?= $checks['vendor'] ? '✅' : '❌' ?> Vendor directory (core/vendor)
</div>
<div class="check-item <?= $checks['env'] ? 'success' : 'info' ?>">
    <?= $checks['env'] ? '✅' : 'ℹ️' ?> .env file (<?= $checks['env'] ? 'exists' : 'will be created' ?>)
</div>

<div style="margin-top: 30px;">
    <?php if ($allPassed): ?>
        <a href="?step=2" class="btn btn-primary">Next: Database Configuration →</a>
    <?php else: ?>
        <button class="btn btn-primary" disabled>Please fix the issues above</button>
        <a href="?step=1" class="btn btn-success">Refresh Check</a>
    <?php endif; ?>
</div>

