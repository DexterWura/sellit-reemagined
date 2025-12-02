<?php
// Step 4: Installation

if (!isset($_SESSION['db_config']) || !isset($_SESSION['app_config'])) {
    header('Location: ?step=2');
    exit;
}

$dbConfig = $_SESSION['db_config'];
$appConfig = $_SESSION['app_config'];

// Generate APP_KEY
$appKey = 'base64:' . base64_encode(random_bytes(32));

// Escape values for .env file
function escapeEnvValue($value) {
    // If value contains spaces, quotes, or special chars, wrap in quotes and escape
    if (preg_match('/[\s"\'#\\\]/', $value)) {
        return '"' . str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value) . '"';
    }
    return $value;
}

// Create .env file with properly escaped values
$envContent = "APP_NAME=" . escapeEnvValue($appConfig['name']) . "\n";
$envContent .= "APP_ENV={$appConfig['env']}\n";
$envContent .= "APP_KEY={$appKey}\n";
$envContent .= "APP_DEBUG={$appConfig['debug']}\n";
$envContent .= "APP_TIMEZONE=Africa/Harare\n";
$envContent .= "APP_URL=" . escapeEnvValue($appConfig['url']) . "\n";
$envContent .= "\n";
$envContent .= "LOG_CHANNEL=stack\n";
$envContent .= "LOG_DEPRECATIONS_CHANNEL=null\n";
$envContent .= "LOG_LEVEL=error\n";
$envContent .= "\n";
$envContent .= "DB_CONNECTION=mysql\n";
$envContent .= "DB_HOST=" . escapeEnvValue($dbConfig['host']) . "\n";
$envContent .= "DB_PORT={$dbConfig['port']}\n";
$envContent .= "DB_DATABASE=" . escapeEnvValue($dbConfig['database']) . "\n";
$envContent .= "DB_USERNAME=" . escapeEnvValue($dbConfig['username']) . "\n";
$envContent .= "DB_PASSWORD=" . escapeEnvValue($dbConfig['password']) . "\n";

$envContent .= "\n";
$envContent .= "BROADCAST_DRIVER=log\n";
$envContent .= "CACHE_DRIVER=file\n";
$envContent .= "FILESYSTEM_DISK=local\n";
$envContent .= "QUEUE_CONNECTION=database\n";
$envContent .= "SESSION_DRIVER=file\n";
$envContent .= "SESSION_LIFETIME=120\n";
$envContent .= "\n";
$envContent .= "MEMCACHED_HOST=127.0.0.1\n";
$envContent .= "\n";
$envContent .= "REDIS_HOST=127.0.0.1\n";
$envContent .= "REDIS_PASSWORD=null\n";
$envContent .= "REDIS_PORT=6379\n";
$envContent .= "\n";
$envContent .= "MAIL_MAILER=smtp\n";
$envContent .= "MAIL_HOST=mailpit\n";
$envContent .= "MAIL_PORT=1025\n";
$envContent .= "MAIL_USERNAME=null\n";
$envContent .= "MAIL_PASSWORD=null\n";
$envContent .= "MAIL_ENCRYPTION=null\n";
$envContent .= "MAIL_FROM_ADDRESS=\"hello@example.com\"\n";
$envContent .= "MAIL_FROM_NAME=" . escapeEnvValue($appConfig['name']) . "\n";
$envContent .= "\n";
$envContent .= "AWS_ACCESS_KEY_ID=\n";
$envContent .= "AWS_SECRET_ACCESS_KEY=\n";
$envContent .= "AWS_DEFAULT_REGION=us-east-1\n";
$envContent .= "AWS_BUCKET=\n";
$envContent .= "AWS_USE_PATH_STYLE_ENDPOINT=false\n";
$envContent .= "\n";
$envContent .= "VITE_APP_NAME=" . escapeEnvValue($appConfig['name']) . "\n";

$envPath = __DIR__ . '/../.env';

// Backup existing .env if it exists
$envBackupPath = __DIR__ . '/../.env.backup.' . date('Y-m-d_H-i-s');
if (file_exists($envPath)) {
    copy($envPath, $envBackupPath);
    $steps[] = ['ℹ️', 'Backed up existing .env file'];
}

// Create .env file
$envCreated = file_put_contents($envPath, $envContent);
if ($envCreated === false) {
    $errors[] = 'Failed to write .env file. Please check file permissions.';
}

// Bootstrap Laravel
$errors = [];
$steps = [];
$app = null;

try {
    define('LARAVEL_START', microtime(true));
    require __DIR__ . '/../core/vendor/autoload.php';
    $app = require_once __DIR__ . '/../core/bootstrap/app.php';
    $steps[] = ['✅', 'Laravel bootstrapped successfully'];
} catch (Exception $e) {
    $errors[] = 'Failed to bootstrap Laravel: ' . $e->getMessage();
    $steps[] = ['❌', 'Laravel bootstrap failed: ' . $e->getMessage()];
}

if ($app) {
    // Step 1: Clear all caches first
    try {
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        $steps[] = ['✅', 'All caches cleared'];
    } catch (Exception $e) {
        // Non-critical, continue
        $steps[] = ['⚠️', 'Cache clear skipped (non-critical)'];
    }
    
    // Step 2: Update timezone.php file
    try {
        $timezonePath = __DIR__ . '/../core/config/timezone.php';
        $timezoneContent = '<?php $timezone = "Africa/Harare" ?>';
        file_put_contents($timezonePath, $timezoneContent);
        $steps[] = ['✅', 'Timezone configuration updated'];
    } catch (Exception $e) {
        $steps[] = ['⚠️', 'Timezone update skipped (non-critical)'];
    }
    
    // Step 3: Import SQL file
    $sqlFile = __DIR__ . '/dexterso_escrow.sql';
    $sqlImported = false;
    
    if (file_exists($sqlFile)) {
        try {
            // Connect to database directly using PDO
            $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 300, // 5 minutes for large SQL files
            ]);
            
            // Read SQL file
            $sqlContent = file_get_contents($sqlFile);
            
            if (!empty($sqlContent)) {
                // Remove BOM if present
                $sqlContent = preg_replace('/^\xEF\xBB\xBF/', '', $sqlContent);
                
                // Remove comments and empty lines
                $sqlContent = preg_replace('/^--.*$/m', '', $sqlContent);
                $sqlContent = preg_replace('/^\/\*.*?\*\//ms', '', $sqlContent);
                
                // Split by semicolon, but preserve semicolons inside quotes
                $statements = [];
                $currentStatement = '';
                $inQuotes = false;
                $quoteChar = null;
                
                for ($i = 0; $i < strlen($sqlContent); $i++) {
                    $char = $sqlContent[$i];
                    $nextChar = isset($sqlContent[$i + 1]) ? $sqlContent[$i + 1] : '';
                    
                    // Handle escaped quotes
                    if ($char === '\\' && $inQuotes) {
                        $currentStatement .= $char . $nextChar;
                        $i++;
                        continue;
                    }
                    
                    // Toggle quote state
                    if (($char === '"' || $char === "'" || $char === '`') && !$inQuotes) {
                        $inQuotes = true;
                        $quoteChar = $char;
                    } elseif ($char === $quoteChar && $inQuotes) {
                        $inQuotes = false;
                        $quoteChar = null;
                    }
                    
                    $currentStatement .= $char;
                    
                    // If semicolon and not in quotes, end of statement
                    if ($char === ';' && !$inQuotes) {
                        $statement = trim($currentStatement);
                        if (!empty($statement)) {
                            $statements[] = $statement;
                        }
                        $currentStatement = '';
                    }
                }
                
                // Add any remaining statement
                $remaining = trim($currentStatement);
                if (!empty($remaining)) {
                    $statements[] = $remaining;
                }
                
                // Execute each statement
                $executedCount = 0;
                $errorCount = 0;
                $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
                
                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    if (empty($statement) || preg_match('/^(SET|USE|DELIMITER)/i', $statement)) {
                        continue; // Skip empty statements and MySQL-specific commands
                    }
                    
                    try {
                        $pdo->exec($statement);
                        $executedCount++;
                    } catch (PDOException $e) {
                        // Some errors are acceptable (like table already exists)
                        if (strpos($e->getMessage(), 'already exists') === false && 
                            strpos($e->getMessage(), 'Duplicate') === false) {
                            $errorCount++;
                            // Log first few errors for debugging
                            if ($errorCount <= 3) {
                                $steps[] = ['⚠️', 'SQL warning: ' . substr($e->getMessage(), 0, 100)];
                            }
                        }
                    }
                }
                
                if ($executedCount > 0) {
                    $steps[] = ['✅', "SQL file imported successfully ({$executedCount} statements executed)"];
                    $sqlImported = true;
                } else {
                    $steps[] = ['⚠️', 'SQL file processed but no statements were executed'];
                }
            } else {
                $steps[] = ['⚠️', 'SQL file is empty'];
            }
        } catch (Exception $e) {
            $errors[] = 'SQL import failed: ' . $e->getMessage();
            $steps[] = ['❌', 'SQL file import failed: ' . substr($e->getMessage(), 0, 150)];
        }
    } else {
        $steps[] = ['ℹ️', 'SQL file not found, will use migrations instead'];
    }
    
    // Step 4: Run migrations (only if SQL file wasn't successfully imported)
    if (!$sqlImported) {
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $steps[] = ['✅', 'Database migrations completed'];
        } catch (Exception $e) {
            $errors[] = 'Migration failed: ' . $e->getMessage();
            $steps[] = ['❌', 'Database migrations failed: ' . $e->getMessage()];
        }
    } else {
        $steps[] = ['ℹ️', 'Skipping migrations (SQL file was imported)'];
    }
    
    // Step 5: Set SystemInstalled cache
    try {
        $cache = $app->make('cache');
        $cache->put('SystemInstalled', true, now()->addYears(10));
        $steps[] = ['✅', 'Installation flag set'];
    } catch (Exception $e) {
        // Try alternative method if cache fails
        try {
            // Use file-based cache directly
            $cacheFile = __DIR__ . '/../core/storage/framework/cache/data/SystemInstalled';
            $cacheDir = dirname($cacheFile);
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            file_put_contents($cacheFile, serialize(['installed' => true, 'timestamp' => time()]));
            $steps[] = ['✅', 'Installation flag set (alternative method)'];
        } catch (Exception $e2) {
            $errors[] = 'Failed to set installation flag: ' . $e->getMessage();
            $steps[] = ['❌', 'Installation flag failed'];
        }
    }
    
    // Step 6: Optimize (optional, non-critical)
    try {
        \Illuminate\Support\Facades\Artisan::call('config:cache');
        $steps[] = ['✅', 'Configuration cached'];
    } catch (Exception $e) {
        $steps[] = ['⚠️', 'Config cache skipped (non-critical)'];
    }
}

// Installation is successful if .env was created and either SQL was imported or migrations ran
$hasMigrations = false;
$hasSqlImport = false;
foreach ($steps as $step) {
    if (is_array($step) && isset($step[1])) {
        if (strpos($step[1], 'Database migrations completed') !== false) {
            $hasMigrations = true;
        }
        if (strpos($step[1], 'SQL file imported successfully') !== false) {
            $hasSqlImport = true;
        }
    }
}

$installationSuccess = $envCreated !== false && !empty($steps) && 
    ($hasMigrations || $hasSqlImport ||
     in_array(['✅', 'Installation flag set'], $steps) ||
     in_array(['✅', 'Installation flag set (alternative method)'], $steps));
?>

<h2>Step 4: Installing Application</h2>

<?php if ($envCreated): ?>
    <div class="check-item success">✅ .env file created</div>
<?php else: ?>
    <div class="check-item error">❌ Failed to create .env file</div>
<?php endif; ?>

<h3>Installation Steps</h3>
<?php foreach ($steps as $step): ?>
    <div class="check-item <?= strpos($step[0], '✅') !== false ? 'success' : (strpos($step[0], '❌') !== false ? 'error' : '') ?>">
        <?= $step[0] ?> <?= $step[1] ?>
    </div>
<?php endforeach; ?>

<?php if ($installationSuccess): ?>
    <div class="alert alert-success" style="margin-top: 20px;">
        <strong>✅ Installation completed successfully!</strong>
    </div>
    <div style="margin-top: 30px;">
        <a href="?step=5" class="btn btn-success">Complete Installation →</a>
    </div>
<?php else: ?>
    <div class="alert alert-danger" style="margin-top: 20px;">
        <strong>❌ Installation encountered errors.</strong>
        <ul style="margin-top: 10px; padding-left: 20px;">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div style="margin-top: 30px;">
        <a href="?step=4" class="btn btn-primary">Retry Installation</a>
        <a href="?step=3" class="btn">← Back</a>
    </div>
<?php endif; ?>

