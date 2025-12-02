<?php
// Step 3: Application Configuration

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appName = trim($_POST['app_name'] ?? 'Escrow System');
    $appUrl = trim($_POST['app_url'] ?? 'https://escrow.dextersoft.com');
    $appEnv = $_POST['app_env'] ?? 'production';
    $appDebug = isset($_POST['app_debug']) ? 'true' : 'false';
    
    // Validate
    if (empty($appName)) {
        $errors[] = 'Application name is required';
    }
    if (empty($appUrl)) {
        $errors[] = 'Application URL is required';
    } elseif (!filter_var($appUrl, FILTER_VALIDATE_URL)) {
        $errors[] = 'Application URL must be a valid URL';
    }
    if (!in_array($appEnv, ['production', 'local', 'staging'])) {
        $errors[] = 'Invalid environment selected';
    }
    
    if (empty($errors)) {
        // Ensure URL doesn't have trailing slash
        $appUrl = rtrim($appUrl, '/');
        
        // Save to session
        $_SESSION['app_config'] = [
            'name' => $appName,
            'url' => $appUrl,
            'env' => $appEnv,
            'debug' => $appDebug,
        ];
        
        $success = true;
    }
}

$appConfig = $_SESSION['app_config'] ?? [
    'name' => 'Escrow System',
    'url' => 'https://escrow.dextersoft.com',
    'env' => 'production',
    'debug' => 'false',
];
?>

<h2>Step 3: Application Configuration</h2>

<?php if ($success): ?>
    <div class="alert alert-success">
        <strong>✅ Configuration saved!</strong>
    </div>
    <div style="margin-top: 30px;">
        <a href="?step=4" class="btn btn-primary">Next: Install Application →</a>
    </div>
<?php else: ?>
    <form method="POST">
        <div class="form-group">
            <label>Application Name</label>
            <input type="text" name="app_name" class="form-control" value="<?= htmlspecialchars($appConfig['name']) ?>" required>
        </div>

        <div class="form-group">
            <label>Application URL</label>
            <input type="url" name="app_url" class="form-control" value="<?= htmlspecialchars($appConfig['url']) ?>" required>
            <small class="text-muted">Your full website URL</small>
        </div>

        <div class="form-group">
            <label>Environment</label>
            <select name="app_env" class="form-control" required>
                <option value="production" <?= $appConfig['env'] === 'production' ? 'selected' : '' ?>>Production</option>
                <option value="local" <?= $appConfig['env'] === 'local' ? 'selected' : '' ?>>Local</option>
                <option value="staging" <?= $appConfig['env'] === 'staging' ? 'selected' : '' ?>>Staging</option>
            </select>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="app_debug" <?= $appConfig['debug'] === 'true' ? 'checked' : '' ?>>
                Enable Debug Mode (Not recommended for production)
            </label>
        </div>

        <div style="margin-top: 30px;">
            <button type="submit" class="btn btn-primary">Save Configuration</button>
            <a href="?step=2" class="btn">← Back</a>
        </div>
    </form>
<?php endif; ?>

