<?php

namespace App\Http\Controllers\Gateway;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Models\AdminNotification;
use App\Models\Deposit;
use App\Models\GatewayCurrency;
use App\Models\Milestone;
use App\Models\Escrow;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function deposit($type = null)
    {
        $amount = NULL;
        if ($type == 'checkout') {
            $checkOutData = session('checkout');
            if (!$checkOutData) {
                $notify[] = ['error', 'Checkout session expired. Please try again.'];
                return redirect()->route('user.home')->withNotify($notify);
            }
            
            try {
                $checkOutData = decrypt($checkOutData);
                $amount = $checkOutData['amount'] ?? null;
                
                if (!$amount || $amount <= 0) {
                    session()->forget('checkout');
                    $notify[] = ['error', 'Invalid checkout amount'];
                    return redirect()->route('user.home')->withNotify($notify);
                }
            } catch (\Exception $e) {
                session()->forget('checkout');
                $notify[] = ['error', 'Invalid checkout session. Please try again.'];
                return redirect()->route('user.home')->withNotify($notify);
            }
            
            $pageTitle = 'Checkout';
        } else {
            session()->forget('checkout');
            $pageTitle = 'Deposit Money';
        }

        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->with('method')->orderby('name')->get();

        if ($gatewayCurrency->isEmpty()) {
            $notify[] = ['error', 'No payment methods are currently available. Please contact support.'];
            return redirect()->route('user.home')->withNotify($notify);
        }

        return view('Template::user.payment.deposit', compact('gatewayCurrency', 'pageTitle', 'amount'));
    }

    public function depositInsert(Request $request)
    {
        $request->validate([
            'amount'   => 'required|numeric|gt:0',
            'gateway'  => 'required',
            'currency' => 'required',
        ]);


        $amount = $request->amount;

        if ($request->type == 'checkout') {
            $checkOutData = session('checkout');

            if (!$checkOutData) {
                $notify[] = ['error', 'Invalid session'];
                return redirect()->route('user.home')->withNotify($notify);
            }

            $checkOutData = decrypt($checkOutData);

            $amount = $checkOutData['amount'];
        }


        $user = auth()->user();
        $gate = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->where('method_code', $request->gateway)->where('currency', $request->currency)->first();

        if (!$gate) {
            $notify[] = ['error', 'Selected payment method is not available. Please choose another method.'];
            return back()->withInput()->withNotify($notify);
        }

        // Validate amount limits with helpful messages
        if ($gate->min_amount > $amount) {
            $notify[] = ['error', 'Minimum deposit amount is ' . showAmount($gate->min_amount) . '. You entered ' . showAmount($amount)];
            return back()->withInput()->withNotify($notify);
        }
        
        if ($gate->max_amount < $amount) {
            $notify[] = ['error', 'Maximum deposit amount is ' . showAmount($gate->max_amount) . '. You entered ' . showAmount($amount)];
            return back()->withInput()->withNotify($notify);
        }

        // Warn if depositing a very large amount
        if ($amount > 10000) {
            $notify[] = ['info', 'You are depositing a large amount. Please ensure all details are correct before proceeding.'];
            // Don't block, just inform
        }

        $charge      = $gate->fixed_charge + ($request->amount * $gate->percent_charge / 100);
        $payable     = $request->amount + $charge;
        $finalAmount = $payable * $gate->rate;

        $data                  = new Deposit();
        $data->user_id         = $user->id;
        $data->milestone_id    = @$checkOutData['milestone_id'] ?? 0;
        $data->method_code     = $gate->method_code;
        $data->method_currency = strtoupper($gate->currency);
        $data->amount          = $amount;
        $data->charge          = $charge;
        $data->rate            = $gate->rate;
        $data->final_amount    = $finalAmount;
        $data->btc_amount      = 0;

        // Store escrow_id in btc_wallet field temporarily if it's a full escrow payment
        // Only set btc_wallet if it's not already set for escrow payments
        if (isset($checkOutData['type']) && $checkOutData['type'] == 'escrow_full_payment') {
            $data->btc_wallet = 'escrow_' . (@$checkOutData['escrow_id'] ?? 0);
        } else {
            $data->btc_wallet = "";
        }
        $data->trx             = getTrx();
        $data->success_url     = urlPath('user.deposit.history');
        $data->failed_url      = urlPath('user.deposit.history');
        $data->save();

        // Log deposit initiation
        \Log::info('Deposit initiated', [
            'deposit_id' => $data->id,
            'user_id' => $user->id,
            'username' => $user->username,
            'amount' => $amount,
            'charge' => $charge,
            'final_amount' => $finalAmount,
            'gateway' => $gate->method_code,
            'currency' => $gate->currency,
            'trx' => $data->trx,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'milestone_id' => $data->milestone_id,
            'escrow_payment' => isset($checkOutData['type']) && $checkOutData['type'] == 'escrow_full_payment'
        ]);

        session()->put('Track', $data->trx);
        return to_route('user.deposit.confirm');
    }




    public function depositConfirm()
    {
        $track = session()->get('Track');
        
        if (!$track) {
            $notify[] = ['error', 'Payment session expired. Please start over.'];
            return redirect()->route('user.deposit')->withNotify($notify);
        }

        $deposit = Deposit::where('trx', $track)
            ->where('status', Status::PAYMENT_INITIATE)
            ->orderBy('id', 'DESC')
            ->with('gateway')
            ->firstOrFail();

        // Verify deposit belongs to current user
        if ($deposit->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to deposit');
        }

        // Check if gateway is still active
        if (!$deposit->gateway || $deposit->gateway->status != Status::ENABLE) {
            $notify[] = ['error', 'Payment method is no longer available. Please choose another method.'];
            return redirect()->route('user.deposit')->withNotify($notify);
        }

        if ($deposit->method_code >= 1000) {
            return to_route('user.deposit.manual.confirm');
        }


        $dirName = $deposit->gateway->alias;
        $new     = __NAMESPACE__ . '\\' . $dirName . '\\ProcessController';

        $data = $new::process($deposit);
        $data = json_decode($data);


        if (isset($data->error)) {
            $notify[] = ['error', $data->message];
            return back()->withNotify($notify);
        }
        if (isset($data->redirect)) {
            return redirect($data->redirect_url);
        }

        // for Stripe V3
        if (@$data->session) {
            $deposit->btc_wallet = $data->session->id;
            $deposit->save();
        }

        $pageTitle = 'Payment Confirm';
        return view("Template::$data->view", compact('data', 'pageTitle', 'deposit'));
    }


    public static function userDataUpdate($deposit, $isManual = null)
    {
        if ($deposit->status == Status::PAYMENT_INITIATE || $deposit->status == Status::PAYMENT_PENDING) {
            $deposit->status = Status::PAYMENT_SUCCESS;
            $deposit->save();

            $user           = User::find($deposit->user_id);
            $user->balance += $deposit->amount;
            $user->save();

            $methodName = $deposit->methodName();

            $transaction               = new Transaction();
            $transaction->user_id      = $deposit->user_id;
            $transaction->amount       = $deposit->amount;
            $transaction->post_balance = $user->balance;
            $transaction->charge       = $deposit->charge;
            $transaction->trx_type     = '+';
            $transaction->details      = 'Deposit Via ' . $methodName;
            $transaction->trx          = $deposit->trx;
            $transaction->remark       = 'deposit';
            $transaction->save();

            if (!$isManual) {
                $adminNotification            = new AdminNotification();
                $adminNotification->user_id   = $user->id;
                $adminNotification->title     = 'Deposit successful via ' . $methodName;
                $adminNotification->click_url = urlPath('admin.deposit.successful');
                $adminNotification->save();
            }

            notify($user, $isManual ? 'DEPOSIT_APPROVE' : 'DEPOSIT_COMPLETE', [
                'method_name'     => $methodName,
                'method_currency' => $deposit->method_currency,
                'method_amount'   => showAmount($deposit->final_amount, currencyFormat: false),
                'amount'          => showAmount($deposit->amount, currencyFormat: false),
                'charge'          => showAmount($deposit->charge, currencyFormat: false),
                'rate'            => showAmount($deposit->rate, currencyFormat: false),
                'trx'             => $deposit->trx,
                'post_balance'    => showAmount($user->balance)
            ]);

            // Handle milestone payment
            if ($deposit->milestone_id) {

                $milestone = Milestone::where('payment_status', Status::MILESTONE_UNFUNDED)->where('status', Status::NO)->whereHas('escrow', function ($query) {
                    $query->where('status', '!=', Status::ESCROW_DISPUTED)->where('status', '!=', Status::ESCROW_CANCELLED);
                })->find($deposit->milestone_id);

                if ($milestone) {
                    $user->balance -= $milestone->amount;
                    $user->save();

                    $transaction               = new Transaction();
                    $transaction->user_id      = $user->id;
                    $transaction->amount       = $milestone->amount;
                    $transaction->post_balance = $user->balance;
                    $transaction->charge       = 0;
                    $transaction->trx_type     = '-';
                    $transaction->details      = 'Milestone paid for ' . $milestone->escrow->title;
                    $transaction->trx          = getTrx();
                    $transaction->save();

                    $milestone->payment_status = Status::MILESTONE_FUNDED;
                    $milestone->status         = Status::YES;
                    $milestone->save();

                    $escrow               = $milestone->escrow;
                    $escrow->paid_amount += $milestone->amount;
                    $escrow->save();
                }
            }

            // Handle full escrow payment
            if (strpos($deposit->btc_wallet, 'escrow_') === 0) {
                $escrowId = (int) str_replace('escrow_', '', $deposit->btc_wallet);
                $escrow = Escrow::where('id', $escrowId)
                    ->where('buyer_id', $user->id)
                    ->accepted()
                    ->where('status', '!=', Status::ESCROW_DISPUTED)
                    ->where('status', '!=', Status::ESCROW_CANCELLED)
                    ->with('milestones')
                    ->first();

                if ($escrow && $escrow->milestones->count() == 0) {
                    $totalAmount = $escrow->amount + $escrow->buyer_charge;
                    $remainingAmount = $totalAmount - $escrow->paid_amount;

                    // Log escrow payment attempt
                    \Log::info('Processing escrow full payment', [
                        'escrow_id' => $escrow->id,
                        'escrow_number' => $escrow->escrow_number,
                        'user_balance' => $user->balance,
                        'remaining_amount' => $remainingAmount,
                        'total_amount' => $totalAmount,
                        'paid_amount' => $escrow->paid_amount,
                        'deposit_amount' => $deposit->amount
                    ]);

                    if ($remainingAmount > 0) {
                        // For gateway payments, the deposit amount should cover the escrow payment
                        // If user deposited more than needed, the excess stays in their balance
                        if ($user->balance >= $remainingAmount) {
                            $user->balance -= $remainingAmount;
                            $user->save();

                            $transaction               = new Transaction();
                            $transaction->user_id      = $user->id;
                            $transaction->amount       = $remainingAmount;
                            $transaction->post_balance = $user->balance;
                            $transaction->charge       = 0;
                            $transaction->trx_type     = '-';
                            $transaction->details      = 'Full payment for escrow: ' . $escrow->escrow_number;
                            $transaction->trx          = getTrx();
                            $transaction->save();

                            $escrow->paid_amount += $remainingAmount;
                            $escrow->save();

                            \Log::info('Escrow payment completed successfully', [
                                'escrow_id' => $escrow->id,
                                'amount_paid' => $remainingAmount,
                                'new_paid_amount' => $escrow->paid_amount
                            ]);

                            notify($escrow->seller, 'ESCROW_FULLY_PAID', [
                                'escrow_number' => $escrow->escrow_number,
                                'amount' => showAmount($remainingAmount, currencyFormat: false),
                                'currency' => gs()->cur_text,
                            ]);
                        } else {
                            \Log::warning('Insufficient balance for escrow payment after deposit', [
                                'escrow_id' => $escrow->id,
                                'user_balance' => $user->balance,
                                'remaining_amount' => $remainingAmount,
                                'deposit_amount' => $deposit->amount
                            ]);
                        }
                    } else {
                        \Log::info('Escrow already fully paid', [
                            'escrow_id' => $escrow->id,
                            'paid_amount' => $escrow->paid_amount,
                            'total_amount' => $totalAmount
                        ]);
                    }
                } else {
                    if (!$escrow) {
                        \Log::warning('Escrow not found for payment processing', [
                            'escrow_id' => $escrowId,
                            'user_id' => $user->id,
                            'btc_wallet' => $deposit->btc_wallet
                        ]);
                    } elseif ($escrow->milestones->count() > 0) {
                        \Log::info('Escrow has milestones, skipping full payment processing', [
                            'escrow_id' => $escrow->id,
                            'milestone_count' => $escrow->milestones->count()
                        ]);
                    }
                }
            }
        }
    }

    public function manualDepositConfirm()
    {
        $track = session()->get('Track');
        $data  = Deposit::with('gateway')->where('status', Status::PAYMENT_INITIATE)->where('trx', $track)->first();
        abort_if(!$data, 404);
        if ($data->method_code > 999) {
            $pageTitle = 'Confirm Deposit';
            $method    = $data->gatewayCurrency();
            $gateway   = $method->method;
            return view('Template::user.payment.manual', compact('data', 'pageTitle', 'method', 'gateway'));
        }
        abort(404);
    }

    public function manualDepositUpdate(Request $request)
    {
        $track = session()->get('Track');
        $data  = Deposit::with('gateway')->where('status', Status::PAYMENT_INITIATE)->where('trx', $track)->first();
        abort_if(!$data, 404);
        $gatewayCurrency = $data->gatewayCurrency();
        $gateway         = $gatewayCurrency->method;
        $formData        = $gateway->form->form_data;

        $formProcessor  = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $userData = $formProcessor->processFormData($request, $formData);


        $data->detail = $userData;
        $data->status = Status::PAYMENT_PENDING;
        $data->save();


        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = $data->user->id;
        $adminNotification->title     = 'Deposit request from ' . $data->user->username;
        $adminNotification->click_url = urlPath('admin.deposit.details', $data->id);
        $adminNotification->save();

        notify($data->user, 'DEPOSIT_REQUEST', [
            'method_name'     => $data->gatewayCurrency()->name,
            'method_currency' => $data->method_currency,
            'method_amount'   => showAmount($data->final_amount, currencyFormat: false),
            'amount'          => showAmount($data->amount, currencyFormat: false),
            'charge'          => showAmount($data->charge, currencyFormat: false),
            'rate'            => showAmount($data->rate, currencyFormat: false),
            'trx'             => $data->trx
        ]);

        $notify[] = ['success', 'You have deposit request has been taken'];
        return to_route('user.deposit.history')->withNotify($notify);
    }
}
