<?php

// Web-accessible script to fix migration tracking
// Access this via browser: http://your-domain/fix_migration_tracking.php

echo "<h1>Laravel Migration Tracking Fix</h1>";
echo "<pre>";

// Bootstrap Laravel
require_once 'core/vendor/autoload.php';

// Load environment variables if .env exists
if (file_exists('.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Bootstrap Laravel
$app = require_once 'core/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "🔧 Checking database connection...\n";

try {
    DB::connection()->getPdo();
    echo "✅ Database connection successful\n\n";
} catch (\Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if migrations table exists
$migrationsTableExists = Schema::hasTable('migrations');
echo "Migrations table exists: " . ($migrationsTableExists ? "✅ Yes" : "❌ No") . "\n";

if (!$migrationsTableExists) {
    echo "\n📦 Creating migrations table...\n";
    try {
        Schema::create('migrations', function ($table) {
            $table->increments('id');
            $table->string('migration');
            $table->integer('batch');
        });
        echo "✅ Migrations table created\n";
    } catch (\Exception $e) {
        echo "❌ Failed to create migrations table: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Get all migration files
$migrationPath = 'core/database/migrations';
$migrationFiles = glob($migrationPath . '/*.php');

echo "\n📋 Found " . count($migrationFiles) . " migration files\n";

// Get current migrations in database
$currentMigrations = [];
if ($migrationsTableExists) {
    $currentMigrations = DB::table('migrations')->pluck('migration')->toArray();
}

echo "📊 Currently tracked migrations: " . count($currentMigrations) . "\n";

// Get all existing tables
$existingTables = [];
try {
    $tables = DB::select('SHOW TABLES');
    $dbName = env('DB_DATABASE', 'laravel');

    foreach ($tables as $table) {
        $tableName = $table->{'Tables_in_' . $dbName};
        $existingTables[] = $tableName;
    }
} catch (\Exception $e) {
    echo "❌ Failed to get table list: " . $e->getMessage() . "\n";
    exit(1);
}

echo "🗄️  Existing database tables: " . count($existingTables) . "\n\n";

// Process each migration file
$migrationsToMarkAsRun = [];
$batch = 1;

foreach ($migrationFiles as $file) {
    $fileName = basename($file);
    $migrationName = str_replace('.php', '', $fileName);

    // Check if this migration is already tracked
    if (in_array($migrationName, $currentMigrations)) {
        echo "✅ $migrationName - Already tracked\n";
        continue;
    }

    // Since the SQL dump creates all tables, we'll mark all migrations as run
    // if the corresponding tables exist
    $shouldMarkAsRun = false;

    // Check for common table patterns
    $tableChecks = [
        'users' => ['users'],
        'admins' => ['admins'],
        'listings' => ['listings'],
        'bids' => ['bids'],
        'offers' => ['offers'],
        'categories' => ['categories', 'listing_categories'],
        'reviews' => ['reviews'],
        'transactions' => ['transactions'],
        'deposits' => ['deposits'],
        'withdrawals' => ['withdrawals'],
        'support' => ['support_tickets', 'support_messages'],
        'notifications' => ['notifications', 'notification_logs'],
        'devices' => ['device_tokens'],
        'login' => ['user_logins'],
        'watchlist' => ['watchlist'],
        'images' => ['listing_images'],
        'metrics' => ['listing_metrics'],
        'questions' => ['listing_questions'],
        'views' => ['listing_views'],
        'settings' => ['general_settings', 'marketplace_settings'],
        'extensions' => ['extensions'],
        'pages' => ['pages'],
        'subscribers' => ['subscribers'],
        'gateways' => ['gateways', 'gateway_currencies'],
        'escrow' => ['escrows', 'escrow_charges'],
        'milestones' => ['milestones', 'milestone_templates'],
        'ndas' => ['nda_documents'],
        'domains' => ['domain_verifications'],
        'social' => ['social_media_verifications'],
        'verifications' => ['verification_settings', 'verification_attempts'],
        'tracking' => ['migration_tracking'],
    ];

    foreach ($tableChecks as $keyword => $possibleTables) {
        if (strpos(strtolower($migrationName), $keyword) !== false) {
            foreach ($possibleTables as $table) {
                if (in_array($table, $existingTables)) {
                    $shouldMarkAsRun = true;
                    break 2;
                }
            }
        }
    }

    // If we can't determine from keywords, check if this looks like a core Laravel table
    if (!$shouldMarkAsRun && in_array($migrationName, [
        '0001_01_01_000000_create_users_table',
        '0001_01_01_000001_create_cache_table',
        '0001_01_01_000002_create_jobs_table'
    ])) {
        // These are core Laravel tables that should exist
        $shouldMarkAsRun = true;
    }

    if ($shouldMarkAsRun) {
        $migrationsToMarkAsRun[] = $migrationName;
        echo "✅ $migrationName - Will mark as run\n";
    } else {
        echo "⏳ $migrationName - Keeping as pending\n";
    }
}

// Mark migrations as run
if (!empty($migrationsToMarkAsRun)) {
    echo "\n🔄 Marking " . count($migrationsToMarkAsRun) . " migrations as run...\n";

    foreach ($migrationsToMarkAsRun as $migration) {
        try {
            // Find the highest batch number and increment
            $maxBatch = DB::table('migrations')->max('batch') ?? 0;
            $batch = $maxBatch + 1;

            DB::table('migrations')->insert([
                'migration' => $migration,
                'batch' => $batch
            ]);
            echo "✅ Marked $migration as run (batch $batch)\n";
        } catch (\Exception $e) {
            echo "❌ Failed to mark $migration: " . $e->getMessage() . "\n";
        }
    }
} else {
    echo "\nℹ️  No migrations to mark as run\n";
}

// Final status check
$finalCount = DB::table('migrations')->count();
echo "\n📈 Final migration count: $finalCount\n";

echo "\n🎉 Migration tracking fix complete!\n";
echo "You can now try running migrations from the admin panel.\n";
echo "\n🔗 <a href='/admin/migrations'>Go to Admin Migrations Panel</a>\n";

echo "</pre>";
?>
