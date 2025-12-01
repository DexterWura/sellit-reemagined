<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Escrow;
use App\Models\Milestone;
use App\Models\Transaction;
use Illuminate\Http\Request;

class MilestoneController extends Controller
{

    public function milestones($id)
    {
        $pageTitle   = "Escrow Milestones";
        $escrow      = Escrow::checkUser()->findOrFail($id);
        $milestones  = Milestone::where('escrow_id', $id)->orderBy('id', 'desc')->with('deposit:milestone_id,status')->paginate(getPaginate());
        $totalAmount = $escrow->milestones()->sum('amount');
        $restAmount  = $escrow->amount + $escrow->buyer_charge - $totalAmount;
        return view('Template::user.escrow.milestones', compact('pageTitle', 'escrow', 'milestones', 'restAmount'));
    }

    public function createMilestone(Request $request, $id)
    {

        $escrow      = Escrow::where('buyer_id', auth()->user()->id)->accepted()->findOrFail($id);
        $totalAmount = $escrow->milestones()->sum('amount');
        $restAmount  = $escrow->amount + $escrow->buyer_charge - $totalAmount;

        $request->validate([
            'amount' => 'required|numeric|gt:0|lte:' . $restAmount,
            'note'   => 'required|max:255',
        ]);

        $milestone            = new Milestone();
        $milestone->escrow_id = $escrow->id;
        $milestone->user_id   = auth()->id();
        $milestone->amount    = $request->amount;
        $milestone->note      = $request->note;
        $milestone->save();

        $notify[] = ['success', 'Milestone created successfully'];
        return back()->withNotify($notify);
    }

    public function payMilestone(Request $request, $id)
    {

        $request->validate([
            'pay_via' => 'required|in:1,2',
        ]);

        $milestone = Milestone::unFunded()->whereHas('escrow', function ($query) {
            $query->where('buyer_id', auth()->user()->id);
        })->with('deposit:milestone_id,status')->find($id);

        if ($milestone->deposit && $milestone->deposit->status == Status::PAYMENT_PENDING) {
            $notify[] = ['error', 'Payment for this milestone is pending now. Please wait for admin approval.'];
            return back()->withNotify($notify);
        }

        if (!$milestone) {
            $notify[] = ['error', 'Milestone not found'];
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

        $notify[] = ['success', 'Milestone amount paid successfully'];
        return back()->withNotify($notify);
    }
}
