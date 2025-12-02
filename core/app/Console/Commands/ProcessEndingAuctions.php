<?php

namespace App\Console\Commands;

use App\Constants\Status;
use App\Jobs\ProcessAuctionEnd;
use App\Models\Listing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessEndingAuctions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auctions:process-ending 
                            {--check-only : Only check for ending auctions without processing}
                            {--minutes=5 : Check auctions ending within this many minutes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process auctions that are ending or have ended';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutes = (int) $this->option('minutes');
        $checkOnly = $this->option('check-only');

        // Find auctions that have ended or are ending soon
        $endingAuctions = Listing::where('sale_type', 'auction')
            ->where('status', Status::LISTING_ACTIVE)
            ->whereNotNull('auction_end')
            ->where('auction_end', '<=', now()->addMinutes($minutes))
            ->get();

        if ($endingAuctions->isEmpty()) {
            $this->info('No auctions ending in the next ' . $minutes . ' minutes.');
            return 0;
        }

        $this->info('Found ' . $endingAuctions->count() . ' auction(s) ending soon.');

        if ($checkOnly) {
            foreach ($endingAuctions as $listing) {
                $this->line("  - Listing #{$listing->listing_number}: {$listing->title} (Ends: {$listing->auction_end})");
            }
            return 0;
        }

        $processed = 0;
        $scheduled = 0;

        foreach ($endingAuctions as $listing) {
            // If auction has already ended, process immediately
            if ($listing->auction_end->isPast()) {
                try {
                    ProcessAuctionEnd::dispatch($listing->id);
                    $processed++;
                    $this->info("  ✓ Dispatched job for ended auction: {$listing->listing_number}");
                } catch (\Exception $e) {
                    $this->error("  ✗ Failed to dispatch job for {$listing->listing_number}: " . $e->getMessage());
                    Log::error('Failed to dispatch auction processing job', [
                        'listing_id' => $listing->id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                // Schedule job to run at auction end time
                try {
                    ProcessAuctionEnd::dispatch($listing->id)
                        ->delay($listing->auction_end);
                    $scheduled++;
                    $this->info("  ✓ Scheduled job for auction ending at {$listing->auction_end}: {$listing->listing_number}");
                } catch (\Exception $e) {
                    $this->error("  ✗ Failed to schedule job for {$listing->listing_number}: " . $e->getMessage());
                    Log::error('Failed to schedule auction processing job', [
                        'listing_id' => $listing->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        $this->info("Processed: {$processed}, Scheduled: {$scheduled}");
        return 0;
    }
}

