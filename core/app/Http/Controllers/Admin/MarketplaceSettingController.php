<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Frontend;
use Illuminate\Http\Request;

class MarketplaceSettingController extends Controller
{
    public function sections()
    {
        $pageTitle = 'Marketplace Homepage Sections';
        
        // Get all marketplace sections
        $sectionKeys = [
            'marketplace_hero.content' => [
                'name' => 'Hero/Search Section',
                'description' => 'Main hero banner with search functionality',
                'icon' => 'las la-search',
            ],
            'marketplace_stats.content' => [
                'name' => 'Statistics Section',
                'description' => 'Display marketplace stats (total listings, sales, etc.)',
                'icon' => 'las la-chart-bar',
            ],
            'marketplace_featured.content' => [
                'name' => 'Featured Listings',
                'description' => 'Display featured/premium listings',
                'icon' => 'las la-star',
            ],
            'marketplace_ending.content' => [
                'name' => 'Auctions Ending Soon',
                'description' => 'Display auctions that are about to end',
                'icon' => 'las la-clock',
            ],
            'marketplace_popular.content' => [
                'name' => 'Most Popular Listings',
                'description' => 'Display trending listings with high engagement',
                'icon' => 'las la-fire',
            ],
            'marketplace_new.content' => [
                'name' => 'Just Listed / New Listings',
                'description' => 'Display recently added listings',
                'icon' => 'las la-plus-circle',
            ],
            'marketplace_domains.content' => [
                'name' => 'Domain Names Section',
                'description' => 'Display domain name listings',
                'icon' => 'las la-globe',
            ],
            'marketplace_websites.content' => [
                'name' => 'Websites Section',
                'description' => 'Display website listings',
                'icon' => 'las la-laptop',
            ],
            'marketplace_apps.content' => [
                'name' => 'Mobile & Desktop Apps Section',
                'description' => 'Display app listings',
                'icon' => 'las la-mobile-alt',
            ],
            'marketplace_social.content' => [
                'name' => 'Social Media Accounts Section',
                'description' => 'Display social media account listings',
                'icon' => 'las la-share-alt',
            ],
            'marketplace_cta.content' => [
                'name' => 'Call to Action Section',
                'description' => 'Seller CTA banner at the bottom',
                'icon' => 'las la-bullhorn',
            ],
        ];

        $sections = [];
        foreach ($sectionKeys as $key => $info) {
            $content = Frontend::where('data_keys', $key)->first();
            if ($content) {
                // Handle both object (from model cast) and string formats
                $dataValues = is_string($content->data_values) 
                    ? json_decode($content->data_values, true) 
                    : (array) $content->data_values;
                $dataValues = $dataValues ?? [];
                
                $sections[$key] = [
                    'id' => $content->id,
                    'name' => $info['name'],
                    'description' => $info['description'],
                    'icon' => $info['icon'],
                    'status' => $dataValues['status'] ?? '1',
                    'heading' => $dataValues['heading'] ?? '',
                    'subheading' => $dataValues['subheading'] ?? '',
                    'limit' => $dataValues['limit'] ?? 6,
                ];
            }
        }

        return view('admin.marketplace.sections', compact('pageTitle', 'sections'));
    }

    public function sectionsUpdate(Request $request)
    {
        $request->validate([
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:frontends,id',
        ]);

        foreach ($request->sections as $key => $data) {
            $content = Frontend::find($data['id']);
            if (!$content) continue;
            
            // Handle both object (from model cast) and string formats
            $dataValues = is_string($content->data_values) 
                ? json_decode($content->data_values, true) 
                : (array) $content->data_values;
            $dataValues = $dataValues ?? [];
            
            $dataValues['status'] = isset($data['status']) && $data['status'] ? '1' : '0';
            
            if (isset($data['heading'])) {
                $dataValues['heading'] = $data['heading'];
            }
            
            if (isset($data['subheading'])) {
                $dataValues['subheading'] = $data['subheading'];
            }
            
            if (isset($data['limit'])) {
                $dataValues['limit'] = (int) $data['limit'];
            }
            
            $content->data_values = json_encode($dataValues);
            $content->save();
        }

        $notify[] = ['success', 'Sections updated successfully'];
        return back()->withNotify($notify);
    }

    public function updateSection(Request $request, $id)
    {
        $content = Frontend::findOrFail($id);
        
        // Handle both object (from model cast) and string formats
        $dataValues = is_string($content->data_values) 
            ? json_decode($content->data_values, true) 
            : (array) $content->data_values;
        $dataValues = $dataValues ?? [];
        
        // Update specific fields
        if ($request->has('status')) {
            $dataValues['status'] = $request->status ? '1' : '0';
        }
        
        if ($request->has('heading')) {
            $dataValues['heading'] = $request->heading;
        }
        
        if ($request->has('subheading')) {
            $dataValues['subheading'] = $request->subheading;
        }
        
        if ($request->has('limit')) {
            $dataValues['limit'] = (int) $request->limit;
        }
        
        $content->data_values = json_encode($dataValues);
        $content->save();

        $notify[] = ['success', 'Section updated successfully'];
        return back()->withNotify($notify);
    }

    public function toggleSection(Request $request, $id)
    {
        $content = Frontend::findOrFail($id);
        
        // Handle both object (from model cast) and string formats
        $dataValues = is_string($content->data_values) 
            ? json_decode($content->data_values, true) 
            : (array) $content->data_values;
        $dataValues = $dataValues ?? [];
        $dataValues['status'] = ($dataValues['status'] ?? '1') == '1' ? '0' : '1';
        
        $content->data_values = json_encode($dataValues);
        $content->save();

        return response()->json([
            'success' => true,
            'status' => $dataValues['status'],
            'message' => $dataValues['status'] == '1' ? 'Section enabled' : 'Section disabled'
        ]);
    }
}

