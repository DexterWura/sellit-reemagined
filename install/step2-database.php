<?php
// Step 2: Database Configuration

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host'] ?? '127.0.0.1');
    $dbPort = trim($_POST['db_port'] ?? '3306');
    $dbName = trim($_POST['db_name'] ?? '');
    $dbUser = trim($_POST['db_user'] ?? '');
    $dbPass = $_POST['db_pass'] ?? ''; // Don't trim password, might have leading/trailing spaces
    
    // Validate
    if (empty($dbName)) $errors[] = 'Database name is required';
    if (empty($dbUser)) $errors[] = 'Database username is required';
    if (empty($dbHost)) $errors[] = 'Database host is required';
    if (empty($dbPort) || !is_numeric($dbPort)) $errors[] = 'Database port must be a valid number';
    
    if (empty($errors)) {
        // Test connection
        try {
            // Escape database name for use in SQL (basic sanitization)
            $dbNameEscaped = str_replace('`', '``', $dbName);
            
            $dsn = "mysql:host=" . addslashes($dbHost) . ";port=" . intval($dbPort) . ";charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);
            
            // Check if database exists, create if not
            try {
                $pdo->exec("USE `{$dbNameEscaped}`");
            } catch (PDOException $e) {
                // Database doesn't exist, try to create it
                try {
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbNameEscaped}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $pdo->exec("USE `{$dbNameEscaped}`");
                } catch (PDOException $createError) {
                    // User might not have CREATE DATABASE permission
                    $errors[] = 'Database does not exist and could not be created. Please create it manually or ensure your user has CREATE DATABASE permission. Error: ' . $createError->getMessage();
                    throw $createError;
                }
            }
            
            // Test a simple query to ensure connection works
            $pdo->query("SELECT 1");
            
            // Save to session
            $_SESSION['db_config'] = [
                'host' => $dbHost,
                'port' => $dbPort,
                'database' => $dbName,
                'username' => $dbUser,
                'password' => $dbPass,
            ];
            
            $success = true;
        } catch (PDOException $e) {
            $errors[] = 'Database connection failed: ' . htmlspecialchars($e->getMessage());
        }
    }
}

$dbConfig = $_SESSION['db_config'] ?? [
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => '',
    'username' => '',
    'password' => '',
];
?>

<h2>Step 2: Database Configuration</h2>

<?php if ($success): ?>
    <div class="alert alert-success">
        <strong>✅ Database connection successful!</strong> Configuration saved.
    </div>
    <div style="margin-top: 30px;">
        <a href="?step=3" class="btn btn-primary">Next: Application Configuration →</a>
    </div>
<?php else: ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Database Host</label>
            <input type="text" name="db_host" class="form-control" value="<?= htmlspecialchars($dbConfig['host']) ?>" required>
            <small class="text-muted">Usually 127.0.0.1 or localhost</small>
        </div>

        <div class="form-group">
            <label>Database Port</label>
            <input type="text" name="db_port" class="form-control" value="<?= htmlspecialchars($dbConfig['port']) ?>" required>
            <small class="text-muted">Usually 3306 for MySQL</small>
        </div>

        <div class="form-group">
            <label>Database Name</label>
            <input type="text" name="db_name" class="form-control" value="<?= htmlspecialchars($dbConfig['database']) ?>" required>
            <small class="text-muted">The database will be created if it doesn't exist</small>
        </div>

        <div class="form-group">
            <label>Database Username</label>
            <input type="text" name="db_user" class="form-control" value="<?= htmlspecialchars($dbConfig['username']) ?>" required>
        </div>

        <div class="form-group">
            <label>Database Password</label>
            <input type="password" name="db_pass" class="form-control" value="<?= htmlspecialchars($dbConfig['password']) ?>">
        </div>

        <div style="margin-top: 30px;">
            <button type="submit" class="btn btn-primary">Test Connection & Save</button>
            <a href="?step=1" class="btn">← Back</a>
        </div>
    </form>
<?php endif; ?>

