<?php

// Simple test script to verify migration files syntax
echo "Testing migration files...\n\n";

$migrationFiles = [
    'core/database/migrations/2025_12_05_000001_create_verification_settings_table.php',
    'core/database/migrations/2025_12_05_000002_create_verification_attempts_table.php',
    'core/database/migrations/2025_12_05_000003_update_domain_verifications_table.php',
];

foreach ($migrationFiles as $file) {
    echo "Testing: " . basename($file) . "\n";

    if (!file_exists($file)) {
        echo "❌ File not found: $file\n\n";
        continue;
    }

    // Basic syntax check
    $content = file_get_contents($file);
    if (strpos($content, '<?php') === false) {
        echo "❌ Invalid PHP file\n\n";
        continue;
    }

    // Check for required migration structure
    if (strpos($content, 'extends Migration') === false) {
        echo "❌ Not a valid migration class\n\n";
        continue;
    }

    if (strpos($content, 'public function up()') === false) {
        echo "❌ Missing up() method\n\n";
        continue;
    }

    if (strpos($content, 'public function down()') === false) {
        echo "❌ Missing down() method\n\n";
        continue;
    }

    echo "✅ Migration file looks valid\n\n";
}

echo "Migration testing complete!\n";

// Test seeder
$seederFile = 'core/database/seeders/VerificationSettingsSeeder.php';
echo "\nTesting seeder: " . basename($seederFile) . "\n";

if (!file_exists($seederFile)) {
    echo "❌ Seeder file not found\n";
} else {
    $content = file_get_contents($seederFile);
    if (strpos($content, 'VerificationSetting::firstOrCreate') !== false) {
        echo "✅ Seeder looks valid\n";
    } else {
        echo "❌ Seeder structure issue\n";
    }
}

echo "\nReady for testing! The auto-migration system should pick up these files.\n";
