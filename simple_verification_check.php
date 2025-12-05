<?php

echo "=== SIMPLE VERIFICATION SYSTEM CHECK ===\n\n";

// Check if Laravel can load
echo "1. CHECKING LARAVEL ENVIRONMENT...\n";
try {
    require_once 'core/vendor/autoload.php';
    $app = require_once 'core/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    echo "✅ Laravel loaded successfully\n\n";
} catch (Exception $e) {
    echo "❌ Laravel failed to load: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Check Marketplace Settings
echo "2. CHECKING VERIFICATION SETTINGS...\n";
try {
    $domainRequired = \App\Models\MarketplaceSetting::requireDomainVerification();
    $websiteRequired = \App\Models\MarketplaceSetting::requireWebsiteVerification();
    $socialRequired = \App\Models\MarketplaceSetting::requireSocialMediaVerification();
    $methods = \App\Models\MarketplaceSetting::getDomainVerificationMethods();

    echo "Domain verification enabled: " . ($domainRequired ? "✅ YES" : "❌ NO") . "\n";
    echo "Website verification enabled: " . ($websiteRequired ? "✅ YES" : "❌ NO") . "\n";
    echo "Social media verification enabled: " . ($socialRequired ? "✅ YES" : "❌ NO") . "\n";
    echo "Verification methods: " . json_encode($methods) . "\n\n";

    if (!$domainRequired && !$websiteRequired) {
        echo "🚨 PROBLEM: No verification types are enabled!\n";
        echo "Go to Admin Panel → Verification Settings and enable them.\n\n";
    } else {
        echo "✅ Settings loaded successfully (with or without database)\n\n";
    }

} catch (Exception $e) {
    echo "❌ Error checking settings: " . $e->getMessage() . "\n\n";
    echo "⚠️  FALLBACK: Using default verification settings (enabled)\n\n";
}

// Check routes
echo "3. CHECKING ROUTES...\n";
$routes = [
    'user.verification.generate',
    'user.verification.verify',
    'user.verification.download'
];

foreach ($routes as $route) {
    try {
        $url = route($route);
        echo "✅ Route '$route' exists\n";
    } catch (Exception $e) {
        echo "❌ Route '$route' missing\n";
    }
}
echo "\n";

// Check files
echo "4. CHECKING REQUIRED FILES...\n";
$files = [
    'core/app/Http/Controllers/User/DomainVerificationController.php',
    'core/resources/views/templates/basic/user/listing/create.blade.php',
    'core/routes/user.php'
];

foreach ($files as $file) {
    $exists = file_exists($file);
    $size = $exists ? filesize($file) : 0;
    echo ($exists ? "✅" : "❌") . " $file (" . number_format($size) . " bytes)\n";
}
echo "\n";

// Check cache
echo "5. CHECKING CACHE SYSTEM...\n";
try {
    \Illuminate\Support\Facades\Cache::put('test_key', 'test_value', 60);
    $retrieved = \Illuminate\Support\Facades\Cache::get('test_key');
    $cacheWorks = ($retrieved === 'test_value');
    echo ($cacheWorks ? "✅" : "❌") . " Cache system " . ($cacheWorks ? "working" : "broken") . "\n\n";
} catch (Exception $e) {
    echo "❌ Cache error: " . $e->getMessage() . "\n\n";
}

// Summary
echo "=== SUMMARY ===\n";
echo "If you see any ❌ above, fix those issues first.\n\n";

echo "MOST LIKELY ISSUE: Admin verification settings are disabled.\n";
echo "SOLUTION: Enable them in Admin Panel → Verification Settings\n\n";

echo "To test the real system:\n";
echo "1. Enable admin settings\n";
echo "2. Go to listing creation\n";
echo "3. Select Domain/Website business type\n";
echo "4. Enter a domain\n";
echo "5. Verification UI should appear\n\n";

echo "=== CHECK COMPLETE ===\n";
