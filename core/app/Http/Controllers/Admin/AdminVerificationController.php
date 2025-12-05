<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminVerificationController extends Controller
{

    public function verifications(Request $request)
    {
        $pageTitle = 'Verification Settings';

        // Since verification is now cache-based, show current settings
        $settings = [
            'domain_verification_enabled' => MarketplaceSetting::requireDomainVerification(),
            'website_verification_enabled' => MarketplaceSetting::requireWebsiteVerification(),
            'social_media_verification_enabled' => MarketplaceSetting::requireSocialMediaVerification(),
            'allowed_methods' => MarketplaceSetting::getDomainVerificationMethods(),
            'cache_driver' => config('cache.default'),
        ];

        $note = 'Note: Verification data is now stored in cache only and not in the database. Historical records are not available.';

        return view('admin.verification.index', compact('pageTitle', 'settings', 'note'));
    }

    public function settings(Request $request)
    {
        $pageTitle = 'Verification Settings';

        // Get current marketplace settings for verification
        $settings = [
            'require_domain_verification' => MarketplaceSetting::getValue('require_domain_verification', true),
            'require_website_verification' => MarketplaceSetting::getValue('require_website_verification', true),
            'require_social_media_verification' => MarketplaceSetting::getValue('require_social_media_verification', true),
            'domain_verification_methods' => MarketplaceSetting::getValue('domain_verification_methods', '["txt_file","dns_record"]'),
        ];

        return view('admin.verification.settings', compact('pageTitle', 'settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'require_domain_verification' => 'boolean',
            'require_website_verification' => 'boolean',
            'require_social_media_verification' => 'boolean',
            'domain_verification_methods' => 'string',
        ]);

        // Update marketplace settings
        MarketplaceSetting::setValue('require_domain_verification', $request->boolean('require_domain_verification'));
        MarketplaceSetting::setValue('require_website_verification', $request->boolean('require_website_verification'));
        MarketplaceSetting::setValue('require_social_media_verification', $request->boolean('require_social_media_verification'));
        MarketplaceSetting::setValue('domain_verification_methods', $request->domain_verification_methods);

        $notify[] = ['success', 'Verification settings updated successfully'];
        return back()->withNotify($notify);
    }

    public function debug()
    {
        $pageTitle = 'Verification Debug Info';

        $debug = [
            'marketplace_settings' => [
                'require_domain_verification' => MarketplaceSetting::requireDomainVerification(),
                'require_website_verification' => MarketplaceSetting::requireWebsiteVerification(),
                'require_social_media_verification' => MarketplaceSetting::requireSocialMediaVerification(),
                'domain_verification_methods' => MarketplaceSetting::getDomainVerificationMethods(),
            ],
            'cache_info' => [
                'driver' => config('cache.default'),
                'ttl_verification_sessions' => 86400, // 24 hours
                'ttl_verified_status' => 2592000, // 30 days
            ],
            'note' => 'Verification data is now stored in cache only. No database records are created for individual verifications.',
            'total_verifications_db' => 0, // No longer stored in DB
            'cache_keys_pattern' => [
                'verification_sessions' => 'verification_{user_id}_{domain/platform}_{account}',
                'verified_domains' => 'verified_domain_{user_id}_{domain}',
                'verified_social' => 'verified_social_{user_id}_{platform}_{username}',
            ],
        ];

        return view('admin.verification.debug', compact('pageTitle', 'debug'));
    }
}
