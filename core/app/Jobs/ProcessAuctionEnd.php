<?php

namespace App\Jobs;

use App\Constants\Status;
use App\Models\Bid;
use App\Models\Escrow;
use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessAuctionEnd implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $listingId;
    public $tries = 3;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct($listingId)
    {
        $this->listingId = $listingId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Lock listing to prevent concurrent processing
            $listing = Listing::lockForUpdate()
                ->where('id', $this->listingId)
                ->where('sale_type', 'auction')
                ->where('status', Status::LISTING_ACTIVE)
                ->first();

            if (!$listing) {
                Log::info('Auction already processed or not found', ['listing_id' => $this->listingId]);
                return;
            }

            // Double-check auction has actually ended
            if ($listing->auction_end && $listing->auction_end->isFuture()) {
                Log::info('Auction has not ended yet', [
                    'listing_id' => $this->listingId,
                    'auction_end' => $listing->auction_end
                ]);
                return;
            }

            // Check if already processed
            if (in_array($listing->status, [Status::LISTING_SOLD, Status::LISTING_EXPIRED])) {
                Log::info('Auction already processed', ['listing_id' => $this->listingId, 'status' => $listing->status]);
                return;
            }

            DB::beginTransaction();
            
            try {
                $winningBid = Bid::where('listing_id', $listing->id)
                    ->where('status', Status::BID_WINNING)
                    ->with('user')
                    ->lockForUpdate()
                    ->first();

                if (!$winningBid) {
                    // No bids - mark as expired
                    $listing->status = Status::LISTING_EXPIRED;
                    $listing->save();

                    DB::commit();

                    notify($listing->seller, 'AUCTION_ENDED_NO_BIDS', [
                        'listing_title' => $listing->title,
                        'listing_number' => $listing->listing_number,
                    ]);

                    Log::info('Auction ended with no bids', ['listing_id' => $this->listingId]);
                    return;
                }

                // Check if reserve was met
                if ($listing->reserve_price > 0 && $listing->current_bid < $listing->reserve_price) {
                    $listing->status = Status::LISTING_EXPIRED;
                    $listing->save();

                    // Mark all bids as lost
                    Bid::where('listing_id', $listing->id)
                        ->whereIn('status', [Status::BID_ACTIVE, Status::BID_WINNING, Status::BID_OUTBID])
                        ->update(['status' => Status::BID_LOST]);

                    DB::commit();

                    notify($listing->seller, 'AUCTION_ENDED_RESERVE_NOT_MET', [
                        'listing_title' => $listing->title,
                        'listing_number' => $listing->listing_number,
                        'highest_bid' => showAmount($listing->current_bid),
                        'reserve_price' => showAmount($listing->reserve_price),
                    ]);

                    // Notify bidders
                    $bidders = Bid::where('listing_id', $listing->id)
                        ->where('status', Status::BID_LOST)
                        ->with('user')
                        ->get();
                    
                    foreach ($bidders as $bid) {
                        notify($bid->user, 'AUCTION_ENDED_RESERVE_NOT_MET_BIDDER', [
                            'listing_title' => $listing->title,
                            'your_bid' => showAmount($bid->amount),
                            'reserve_price' => showAmount($listing->reserve_price),
                        ]);
                    }

                    Log::info('Auction ended - reserve not met', [
                        'listing_id' => $this->listingId,
                        'highest_bid' => $listing->current_bid,
                        'reserve_price' => $listing->reserve_price
                    ]);
                    return;
                }

                // Winner found - process sale
                $winningBid->status = Status::BID_WON;
                $winningBid->save();

                // Mark other bids as lost
                Bid::where('listing_id', $listing->id)
                    ->where('id', '!=', $winningBid->id)
                    ->whereIn('status', [Status::BID_ACTIVE, Status::BID_OUTBID])
                    ->update(['status' => Status::BID_LOST]);

                // Update listing
                $listing->status = Status::LISTING_SOLD;
                $listing->winner_id = $winningBid->user_id;
                $listing->final_price = $winningBid->amount;
                $listing->sold_at = now();

                // Create escrow
                $escrow = $this->createEscrow($listing, $winningBid->user, $winningBid->amount);
                $listing->escrow_id = $escrow->id;
                $listing->save();

                // Auto-generate milestones from template if available
                $this->generateMilestonesFromTemplate($escrow, $listing);

                // Update user stats
                $listing->seller->increment('total_sales');
                $listing->seller->increment('total_sales_value', $winningBid->amount);
                $winningBid->user->increment('total_purchases');

                DB::commit();

                // Notify winner (outside transaction)
                notify($winningBid->user, 'AUCTION_WON', [
                    'listing_title' => $listing->title,
                    'listing_number' => $listing->listing_number,
                    'winning_bid' => showAmount($winningBid->amount),
                    'escrow_number' => $escrow->escrow_number,
                ]);

                // Notify seller
                notify($listing->seller, 'AUCTION_ENDED_SOLD', [
                    'listing_title' => $listing->title,
                    'listing_number' => $listing->listing_number,
                    'final_price' => showAmount($winningBid->amount),
                    'winner' => $winningBid->user->username ?? $winningBid->user->name,
                ]);

                // Notify outbid bidders
                $outbidBidders = Bid::where('listing_id', $listing->id)
                    ->where('status', Status::BID_LOST)
                    ->where('id', '!=', $winningBid->id)
                    ->with('user')
                    ->get();
                
                foreach ($outbidBidders as $bid) {
                    notify($bid->user, 'AUCTION_ENDED_OUTBID', [
                        'listing_title' => $listing->title,
                        'winning_bid' => showAmount($winningBid->amount),
                        'your_bid' => showAmount($bid->amount),
                    ]);
                }

                Log::info('Auction processed successfully', [
                    'listing_id' => $this->listingId,
                    'winner_id' => $winningBid->user_id,
                    'final_price' => $winningBid->amount,
                    'escrow_id' => $escrow->id
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Auction processing failed: ' . $e->getMessage(), [
                    'listing_id' => $this->listingId,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Auction processing error: ' . $e->getMessage(), [
                'listing_id' => $this->listingId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Create escrow for auction sale
     */
    private function createEscrow($listing, $buyer, $amount)
    {
        $seller = $listing->seller;
        $general = gs();

        $percentCharge = $general->percent_charge ?? 0;
        $fixedCharge = $general->fixed_charge ?? 0;
        $charge = ($amount * $percentCharge / 100) + $fixedCharge;

        if ($charge > ($general->charge_cap ?? 0) && $general->charge_cap > 0) {
            $charge = $general->charge_cap;
        }

        $escrow = new Escrow();
        $escrow->escrow_number = getTrx();
        $escrow->seller_id = $seller->id;
        $escrow->buyer_id = $buyer->id;
        $escrow->creator_id = $buyer->id;
        $escrow->amount = $amount;
        $escrow->charge = $charge;
        $escrow->buyer_charge = $charge;
        $escrow->seller_charge = 0;
        $escrow->charge_payer = Status::CHARGE_PAYER_BUYER;
        $escrow->title = 'Auction Won: ' . $listing->title;
        $escrow->details = "Escrow for auction: {$listing->title}\nListing #: {$listing->listing_number}";
        $escrow->status = Status::ESCROW_ACCEPTED;
        $escrow->save();

        // Create conversation
        $conversation = new \App\Models\Conversation();
        $conversation->escrow_id = $escrow->id;
        $conversation->buyer_id = $buyer->id;
        $conversation->seller_id = $seller->id;
        $conversation->save();

        return $escrow;
    }

    /**
     * Generate milestones from template for auction escrow
     */
    private function generateMilestonesFromTemplate($escrow, $listing)
    {
        try {
            $template = \App\Models\MilestoneTemplate::getDefaultTemplate($listing->business_type);
            
            if (!$template) {
                // Try generic template
                $template = \App\Models\MilestoneTemplate::getDefaultTemplate('all');
            }
            
            if ($template) {
                $totalAmount = $escrow->amount + $escrow->buyer_charge;
                $milestones = $template->generateMilestones($escrow, $totalAmount);
                
                \App\Models\Milestone::insert($milestones);
                
                Log::info('Milestones auto-generated from template', [
                    'escrow_id' => $escrow->id,
                    'template_id' => $template->id,
                    'milestone_count' => count($milestones)
                ]);
                
                // Notify buyer to review milestones
                notify($escrow->buyer, 'MILESTONES_GENERATED', [
                    'escrow_number' => $escrow->escrow_number,
                    'listing_title' => $listing->title,
                    'template_name' => $template->name,
                    'milestone_count' => count($milestones),
                    'action_required' => 'Please review and approve the proposed milestones',
                ]);
            }
        } catch (\Exception $e) {
            // Don't fail escrow creation if milestone generation fails
            Log::warning('Failed to auto-generate milestones: ' . $e->getMessage(), [
                'escrow_id' => $escrow->id,
                'listing_id' => $listing->id
            ]);
        }
    }
}

