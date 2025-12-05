<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceSetting;
use Illuminate\Http\Request;

class MarketplaceConfigController extends Controller
{
    public function index()
    {
        $pageTitle = 'Marketplace Configuration';
        $settings = MarketplaceSetting::getAllSettings();
        
        return view('admin.marketplace.config', compact('pageTitle', 'settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'max_auction_days' => 'required|integer|min:1|max:365',
            'min_auction_days' => 'required|integer|min:1|max:30',
            'max_images_per_listing' => 'required|integer|min:1|max:50',
            'min_listing_description' => 'required|integer|min:10|max:1000',
            'listing_fee_percentage' => 'required|numeric|min:0|max:100',
            'escrow_fee_percentage' => 'required|numeric|min:0|max:50',
            'auto_extend_auction_minutes' => 'required|integer|min:0|max:60',
            'bid_extension_threshold_minutes' => 'required|integer|min:0|max:60',
        ]);

        // Boolean settings
        $booleanSettings = [
            'allow_auctions',
            'allow_fixed_price',
            'allow_domain_listings',
            'allow_website_listings',
            'allow_social_media_listings',
            'allow_mobile_app_listings',
            'allow_desktop_app_listings',
            'listing_approval_required',
        ];

        foreach ($booleanSettings as $key) {
            MarketplaceSetting::setValue($key, $request->has($key) ? '1' : '0');
        }

        // Numeric/text settings
        $otherSettings = [
            'max_auction_days',
            'min_auction_days',
            'max_images_per_listing',
            'min_listing_description',
            'featured_listing_fee',
            'listing_fee_percentage',
            'escrow_fee_percentage',
            'auto_extend_auction_minutes',
            'bid_extension_threshold_minutes',
        ];

        foreach ($otherSettings as $key) {
            if ($request->has($key)) {
                MarketplaceSetting::setValue($key, $request->input($key));
            }
        }


        MarketplaceSetting::clearCache();

        $notify[] = ['success', 'Marketplace configuration updated successfully'];
        return back()->withNotify($notify);
    }

    public function toggleSetting(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
        ]);

        $key = $request->key;
        $currentValue = MarketplaceSetting::getValue($key, '0');
        $newValue = $currentValue === '1' ? '0' : '1';
        
        MarketplaceSetting::setValue($key, $newValue);

        return response()->json([
            'success' => true,
            'value' => $newValue,
            'message' => 'Setting updated successfully',
        ]);
    }
}

