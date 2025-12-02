<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Escrow;
use App\Models\Milestone;
use App\Models\MilestoneTemplate;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MilestoneController extends Controller
{

    public function milestones($id)
    {
        $pageTitle   = "Payment Milestones";
        $escrow      = Escrow::checkUser()->findOrFail($id);
        
        // Get milestones ordered by id (sort_order may not exist in all databases)
        $milestones  = Milestone::where('escrow_id', $id)
            ->orderBy('id')
            ->with('deposit:milestone_id,status')
            ->get();
            
        $totalAmount = $milestones->sum('amount');
        $restAmount  = $escrow->amount + $escrow->buyer_charge - $totalAmount;
        
        // Get available templates if no milestones exist yet
        $templates = null;
        if ($milestones->isEmpty() && $escrow->status == Status::ESCROW_ACCEPTED) {
            // Try to get business type from associated listing
            $listing = \App\Models\Listing::where('escrow_id', $escrow->id)->first();
            if ($listing) {
                $templates = MilestoneTemplate::getTemplatesForBusinessType($listing->business_type);
            }
        }
        
        return view('Template::user.escrow.milestones', compact('pageTitle', 'escrow', 'milestones', 'restAmount', 'templates'));
    }

    /**
     * Create milestone - Seller can create, Buyer can also create but needs seller approval
     */
    public function createMilestone(Request $request, $id)
    {
        $escrow = Escrow::checkUser()->accepted()->findOrFail($id);
        $user = auth()->user();
        
        $isSeller = $escrow->seller_id == $user->id;
        $isBuyer = $escrow->buyer_id == $user->id;
        
        if (!$isSeller && !$isBuyer) {
            abort(403, 'Unauthorized');
        }

        $totalAmount = $escrow->milestones()->where('approval_status', '!=', 'rejected')->sum('amount');
        $restAmount  = $escrow->amount + $escrow->buyer_charge - $totalAmount;

        $request->validate([
            'amount' => 'required|numeric|gt:0|lte:' . $restAmount,
            'note'   => 'required|max:500',
            'milestone_type' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        
        try {
            // Get current max sort_order (if column exists, otherwise use count)
            $maxSortOrder = 0;
            try {
                $maxSortOrder = Milestone::where('escrow_id', $escrow->id)
                    ->where('approval_status', '!=', 'rejected')
                    ->max('sort_order') ?? 0;
            } catch (\Exception $e) {
                // sort_order column doesn't exist, use count instead
                $maxSortOrder = Milestone::where('escrow_id', $escrow->id)
                    ->where('approval_status', '!=', 'rejected')
                    ->count();
            }

            $milestone = new Milestone();
            $milestone->escrow_id = $escrow->id;
            $milestone->user_id = $user->id;
            $milestone->requested_by = $isSeller ? 'seller' : 'buyer';
            $milestone->amount = $request->amount;
            $milestone->note = $request->note;
            $milestone->milestone_type = $request->milestone_type;
            
            // Set sort_order only if column exists (check using Schema)
            if (\Illuminate\Support\Facades\Schema::hasColumn('milestones', 'sort_order')) {
                $milestone->sort_order = $maxSortOrder + 1;
            }
            
            // Auto-approve by the creator
            if ($isSeller) {
                $milestone->approved_by_seller = true;
                $milestone->approved_by_buyer = false;
            } else {
                $milestone->approved_by_seller = false;
                $milestone->approved_by_buyer = true;
            }
            
            // Status is approved only if both parties have approved
            $milestone->approval_status = ($milestone->approved_by_seller && $milestone->approved_by_buyer) 
                ? 'approved' 
                : 'pending';
            
            $milestone->payment_status = Status::MILESTONE_UNFUNDED;
            $milestone->status = 1;
            $milestone->save();

            DB::commit();

            // Notify the other party
            $otherParty = $isSeller ? $escrow->buyer : $escrow->seller;
            notify($otherParty, 'MILESTONE_CREATED', [
                'escrow_number' => $escrow->escrow_number,
                'milestone_note' => $milestone->note,
                'milestone_amount' => showAmount($milestone->amount),
                'action_required' => $milestone->approval_status === 'pending' ? 'Please review and approve this milestone' : null,
            ]);

            $notify[] = ['success', 'Milestone created successfully. ' . ($milestone->approval_status === 'pending' ? 'Waiting for ' . ($isSeller ? 'buyer' : 'seller') . ' approval.' : 'Milestone is approved and ready.')];
            return back()->withNotify($notify);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Milestone creation failed: ' . $e->getMessage(), [
                'escrow_id' => $id,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            $notify[] = ['error', 'Failed to create milestone. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    /**
     * Generate milestones from template
     */
    public function generateFromTemplate(Request $request, $id)
    {
        $escrow = Escrow::checkUser()->accepted()->findOrFail($id);
        $user = auth()->user();
        
        // Only seller can generate from template
        if ($escrow->seller_id != $user->id) {
            abort(403, 'Only seller can generate milestones from template');
        }

        $request->validate([
            'template_id' => 'required|exists:milestone_templates,id',
        ]);

        $template = MilestoneTemplate::findOrFail($request->template_id);
        
        // Check if milestones already exist
        if ($escrow->milestones()->where('approval_status', '!=', 'rejected')->exists()) {
            $notify[] = ['error', 'Milestones already exist. Please delete existing milestones first.'];
            return back()->withNotify($notify);
        }

        DB::beginTransaction();
        
        try {
            $totalAmount = $escrow->amount + $escrow->buyer_charge;
            $milestones = $template->generateMilestones($escrow, $totalAmount);
            
            Milestone::insert($milestones);

            DB::commit();

            // Notify buyer
            notify($escrow->buyer, 'MILESTONES_GENERATED', [
                'escrow_number' => $escrow->escrow_number,
                'template_name' => $template->name,
                'milestone_count' => count($milestones),
                'action_required' => 'Please review and approve the proposed milestones',
            ]);

            $notify[] = ['success', 'Milestones generated from template. Buyer has been notified to review.'];
            return back()->withNotify($notify);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Milestone generation failed: ' . $e->getMessage(), [
                'escrow_id' => $id,
                'template_id' => $request->template_id,
                'trace' => $e->getTraceAsString()
            ]);
            $notify[] = ['error', 'Failed to generate milestones. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    /**
     * Approve milestone
     */
    public function approveMilestone($id)
    {
        $milestone = Milestone::whereHas('escrow', function ($query) {
            $query->checkUser();
        })->findOrFail($id);
        
        $escrow = $milestone->escrow;
        $user = auth()->user();
        
        $isSeller = $escrow->seller_id == $user->id;
        $isBuyer = $escrow->buyer_id == $user->id;
        
        if (!$isSeller && !$isBuyer) {
            abort(403, 'Unauthorized');
        }

        // Can't approve own request if you're the only one who needs to approve
        if (($isSeller && $milestone->requested_by === 'seller' && $milestone->approved_by_seller) ||
            ($isBuyer && $milestone->requested_by === 'buyer' && $milestone->approved_by_buyer)) {
            $notify[] = ['error', 'You have already approved this milestone'];
            return back()->withNotify($notify);
        }

        DB::beginTransaction();
        
        try {
            if ($isSeller) {
                $milestone->approved_by_seller = true;
            } else {
                $milestone->approved_by_buyer = true;
            }
            
            // Check if both parties have approved
            if ($milestone->approved_by_seller && $milestone->approved_by_buyer) {
                $milestone->approval_status = 'approved';
                $milestone->approved_at = now();
            }
            
            $milestone->save();

            DB::commit();

            // Notify the other party
            $otherParty = $isSeller ? $escrow->buyer : $escrow->seller;
            notify($otherParty, 'MILESTONE_APPROVED', [
                'escrow_number' => $escrow->escrow_number,
                'milestone_note' => $milestone->note,
                'milestone_amount' => showAmount($milestone->amount),
                'approved_by' => $user->username ?? $user->name,
            ]);

            $message = $milestone->approval_status === 'approved' 
                ? 'Milestone approved by both parties and is now active.'
                : 'Your approval has been recorded. Waiting for ' . ($isSeller ? 'buyer' : 'seller') . ' approval.';
            
            $notify[] = ['success', $message];
            return back()->withNotify($notify);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Milestone approval failed: ' . $e->getMessage());
            $notify[] = ['error', 'Failed to approve milestone. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    /**
     * Reject milestone
     */
    public function rejectMilestone(Request $request, $id)
    {
        $milestone = Milestone::whereHas('escrow', function ($query) {
            $query->checkUser();
        })->findOrFail($id);
        
        $escrow = $milestone->escrow;
        $user = auth()->user();
        
        $isSeller = $escrow->seller_id == $user->id;
        $isBuyer = $escrow->buyer_id == $user->id;
        
        if (!$isSeller && !$isBuyer) {
            abort(403, 'Unauthorized');
        }

        // Can't reject if already approved by both parties
        if ($milestone->approval_status === 'approved') {
            $notify[] = ['error', 'Cannot reject an approved milestone'];
            return back()->withNotify($notify);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        
        try {
            $milestone->approval_status = 'rejected';
            $milestone->rejection_reason = $request->rejection_reason;
            $milestone->rejected_by = $user->id;
            $milestone->save();

            DB::commit();

            // Notify the other party
            $otherParty = $isSeller ? $escrow->buyer : $escrow->seller;
            notify($otherParty, 'MILESTONE_REJECTED', [
                'escrow_number' => $escrow->escrow_number,
                'milestone_note' => $milestone->note,
                'rejection_reason' => $request->rejection_reason,
                'rejected_by' => $user->username ?? $user->name,
            ]);

            $notify[] = ['success', 'Milestone rejected. The other party has been notified.'];
            return back()->withNotify($notify);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Milestone rejection failed: ' . $e->getMessage());
            $notify[] = ['error', 'Failed to reject milestone. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    /**
     * Delete milestone (only if not approved or if creator)
     */
    public function deleteMilestone($id)
    {
        $milestone = Milestone::whereHas('escrow', function ($query) {
            $query->checkUser();
        })->findOrFail($id);
        
        $escrow = $milestone->escrow;
        $user = auth()->user();
        
        // Can only delete if:
        // 1. Not approved by both parties, OR
        // 2. You created it and it's not funded
        if ($milestone->approval_status === 'approved' && $milestone->payment_status === Status::MILESTONE_FUNDED) {
            $notify[] = ['error', 'Cannot delete a funded milestone'];
            return back()->withNotify($notify);
        }
        
        if ($milestone->user_id != $user->id && $milestone->approval_status === 'approved') {
            $notify[] = ['error', 'Cannot delete an approved milestone created by someone else'];
            return back()->withNotify($notify);
        }

        DB::beginTransaction();
        
        try {
            $milestone->delete();

            DB::commit();

            $notify[] = ['success', 'Milestone deleted successfully'];
            return back()->withNotify($notify);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Milestone deletion failed: ' . $e->getMessage());
            $notify[] = ['error', 'Failed to delete milestone. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    public function payMilestone(Request $request, $id)
    {
        try {
            $request->validate([
                'pay_via' => 'required|in:1,2',
            ]);

            $milestone = Milestone::unFunded()->whereHas('escrow', function ($query) {
                $query->where('buyer_id', auth()->user()->id);
            })->with('deposit:milestone_id,status')->find($id);

            if (!$milestone) {
                $notify[] = ['error', 'Milestone not found'];
                return back()->withNotify($notify);
            }

            // Check if milestone is approved by both parties
            if ($milestone->approval_status !== 'approved') {
                $notify[] = ['error', 'You can only pay for milestones that are approved by both parties'];
                return back()->withNotify($notify);
            }

            if ($milestone->deposit && $milestone->deposit->status == Status::PAYMENT_PENDING) {
                $notify[] = ['error', 'Payment for this milestone is pending now. Please wait for admin approval.'];
                return back()->withNotify($notify);
            }

            if ($milestone->escrow->status != Status::ESCROW_ACCEPTED) {
                $notify[] = ['error', 'You can only pay for a milestone when its escrow status is accepted'];
                return back()->withNotify($notify);
            }

            $user = auth()->user();

            if ($request->pay_via == 2) {
                session()->put('checkout', encrypt([
                    'amount'       => $milestone->amount,
                    'milestone_id' => $milestone->id,
                ]));

                return redirect()->route('user.deposit.index', 'checkout');
            }

            if ($user->balance < $milestone->amount) {
                $notify[] = ['error', 'You have no sufficient balance'];
                return back()->withNotify($notify);
            }

            // Lock user and milestone to prevent concurrent payments
            DB::beginTransaction();
            
            try {
                // Reload user with lock
                $user = \App\Models\User::lockForUpdate()->find($user->id);
                
                // Re-check balance after lock
                if ($user->balance < $milestone->amount) {
                    DB::rollBack();
                    $notify[] = ['error', 'Insufficient balance'];
                    return back()->withNotify($notify);
                }

                $user->balance -= $milestone->amount;
                $user->save();

                $transaction               = new Transaction();
                $transaction->user_id      = $user->id;
                $transaction->amount       = $milestone->amount;
                $transaction->post_balance = $user->balance;
                $transaction->charge       = 0;
                $transaction->trx_type     = '-';
                $transaction->remark       = "milestone_paid";
                $transaction->details      = 'Milestone amount paid';
                $transaction->trx          = getTrx();
                $transaction->save();

                $milestone->payment_status = Status::MILESTONE_FUNDED;
                $milestone->save();

                $escrow               = $milestone->escrow;
                $escrow->paid_amount += $milestone->amount;
                $escrow->save();

                DB::commit();

                $notify[] = ['success', 'Milestone amount paid successfully'];
                return back()->withNotify($notify);
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Milestone payment failed: ' . $e->getMessage(), [
                    'milestone_id' => $id,
                    'user_id' => $user->id,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Milestone payment error: ' . $e->getMessage());
            $notify[] = ['error', 'An error occurred while processing the payment. Please try again.'];
            return back()->withNotify($notify);
        }
    }
}
