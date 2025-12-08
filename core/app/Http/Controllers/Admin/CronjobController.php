<?php

namespace App\Http\Controllers\Admin;

use App\Console\Commands\ProcessEndingAuctions;
use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CronjobController extends Controller
{
    public function index()
    {
        $pageTitle = 'Cronjob Management';
        
        // Get cronjob settings from general settings
        $general = gs();
        $cronjobSettings = [
            'auction_processing_enabled' => $general->auction_processing_enabled ?? 1,
            'auction_processing_interval' => $general->auction_processing_interval ?? 1, // minutes
            'last_auction_processing_run' => $general->last_auction_processing_run ?? null,
        ];

        // Get log file info
        $logFile = storage_path('logs/auction-processing.log');
        $logExists = file_exists($logFile);
        $logSize = $logExists ? filesize($logFile) : 0;
        $logLastModified = $logExists ? filemtime($logFile) : null;

        // Get recent log entries (last 50 lines)
        $recentLogs = [];
        if ($logExists && $logSize > 0) {
            $logContent = file_get_contents($logFile);
            $logLines = explode("\n", $logContent);
            $recentLogs = array_slice(array_filter($logLines), -50);
            $recentLogs = array_reverse($recentLogs);
        }

        // Check for pending auctions
        $pendingAuctions = \App\Models\Listing::where('sale_type', 'auction')
            ->where('status', \App\Constants\Status::LISTING_ACTIVE)
            ->whereNotNull('auction_end')
            ->where('auction_end', '<=', now())
            ->count();

        return view('admin.setting.cronjob', compact(
            'pageTitle',
            'cronjobSettings',
            'logExists',
            'logSize',
            'logLastModified',
            'recentLogs',
            'pendingAuctions'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'auction_processing_enabled' => 'required|boolean',
            'auction_processing_interval' => 'required|integer|min:1|max:60',
        ]);

        $general = gs();
        $general->auction_processing_enabled = $request->auction_processing_enabled;
        $general->auction_processing_interval = $request->auction_processing_interval;
        $general->save();

        $notify[] = ['success', 'Cronjob settings updated successfully'];
        return back()->withNotify($notify);
    }

    public function runAuctionProcessing(Request $request)
    {
        try {
            $minutes = $request->input('minutes', 60);
            
            // Run the command
            Artisan::call('auctions:process-ending', [
                '--minutes' => $minutes
            ]);

            $output = Artisan::output();

            // Update last run time
            $general = gs();
            $general->last_auction_processing_run = now();
            $general->save();

            $notify[] = ['success', 'Auction processing command executed successfully'];
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Auction processing executed successfully',
                    'output' => $output
                ]);
            }

            return back()->withNotify($notify);
        } catch (\Exception $e) {
            Log::error('Manual auction processing failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            $notify[] = ['error', 'Failed to execute auction processing: ' . $e->getMessage()];
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to execute: ' . $e->getMessage()
                ], 500);
            }

            return back()->withNotify($notify);
        }
    }

    public function clearLogs()
    {
        try {
            $logFile = storage_path('logs/auction-processing.log');
            if (file_exists($logFile)) {
                file_put_contents($logFile, '');
            }

            $notify[] = ['success', 'Log file cleared successfully'];
            return back()->withNotify($notify);
        } catch (\Exception $e) {
            $notify[] = ['error', 'Failed to clear log file: ' . $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    public function getStatus()
    {
        $pendingAuctions = \App\Models\Listing::where('sale_type', 'auction')
            ->where('status', \App\Constants\Status::LISTING_ACTIVE)
            ->whereNotNull('auction_end')
            ->where('auction_end', '<=', now())
            ->count();

        $general = gs();
        $lastRun = $general->last_auction_processing_run;

        return response()->json([
            'pending_auctions' => $pendingAuctions,
            'last_run' => $lastRun ? $lastRun->format('Y-m-d H:i:s') : null,
            'enabled' => $general->auction_processing_enabled ?? 1,
        ]);
    }
}

