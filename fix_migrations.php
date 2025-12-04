<?php

// Script to fix migration tracking issues
// This script will ensure that existing database tables are properly tracked in Laravel's migrations table

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

echo "🔧 Laravel Migration Fix Script\n";
echo "================================\n\n";

// Check database connection
try {
    DB::connection()->getPdo();
    echo "✅ Database connection successful\n";
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
        // Create migrations table manually
        DB::statement("
            CREATE TABLE `migrations` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `migration` varchar(255) NOT NULL,
                `batch` int(11) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
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

// Check which tables exist in database
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

echo "🗄️  Existing database tables: " . count($existingTables) . "\n";

// Analyze each migration file
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

    // Include the migration file to check what it creates
    try {
        $migration = require $file;

        if (!method_exists($migration, 'up')) {
            echo "⚠️  $migrationName - No up() method found, skipping\n";
            continue;
        }

        // Try to determine what tables this migration creates
        // This is a simplified check - we'll mark migrations as run if their tables exist
        $createsUsers = strpos(file_get_contents($file), "Schema::create('users'") !== false;
        $createsListings = strpos(file_get_contents($file), "Schema::create('listings'") !== false;
        $createsBids = strpos(file_get_contents($file), "Schema::create('bids'") !== false;
        $createsOffers = strpos(file_get_contents($file), "Schema::create('offers'") !== false;

        $shouldMarkAsRun = false;

        if ($createsUsers && in_array('users', $existingTables)) {
            $shouldMarkAsRun = true;
            echo "✅ $migrationName - Users table exists, marking as run\n";
        } elseif ($createsListings && in_array('listings', $existingTables)) {
            $shouldMarkAsRun = true;
            echo "✅ $migrationName - Listings table exists, marking as run\n";
        } elseif ($createsBids && in_array('bids', $existingTables)) {
            $shouldMarkAsRun = true;
            echo "✅ $migrationName - Bids table exists, marking as run\n";
        } elseif ($createsOffers && in_array('offers', $existingTables)) {
            $shouldMarkAsRun = true;
            echo "✅ $migrationName - Offers table exists, marking as run\n";
        } elseif (in_array(str_replace(['create_', '_table'], '', $migrationName), $existingTables)) {
            // Generic check: if a table with similar name exists
            $shouldMarkAsRun = true;
            echo "✅ $migrationName - Matching table exists, marking as run\n";
        } else {
            echo "⏳ $migrationName - No matching table found, keeping as pending\n";
        }

        if ($shouldMarkAsRun) {
            $migrationsToMarkAsRun[] = $migrationName;
        }

    } catch (\Exception $e) {
        echo "❌ $migrationName - Error analyzing: " . $e->getMessage() . "\n";
    }
}

// Mark migrations as run
if (!empty($migrationsToMarkAsRun)) {
    echo "\n🔄 Marking " . count($migrationsToMarkAsRun) . " migrations as run...\n";

    foreach ($migrationsToMarkAsRun as $migration) {
        try {
            DB::table('migrations')->insert([
                'migration' => $migration,
                'batch' => $batch
            ]);
            echo "✅ Marked $migration as run\n";
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

echo "\n🎉 Migration fix complete!\n";
echo "You can now try running migrations from the admin panel.\n";

?>
