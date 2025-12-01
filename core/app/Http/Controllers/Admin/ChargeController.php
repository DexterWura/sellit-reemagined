<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EscrowCharge;
use Illuminate\Http\Request;

class ChargeController extends Controller
{

    public function index()
    {
        $pageTitle = 'Escrow Charges';
        $charges   = EscrowCharge::all();
        return view('admin.escrow.charges', compact('pageTitle', 'charges'));
    }

    public function globalCharge(Request $request)
    {
        $request->validate([
            'charge_cap'     => 'required|numeric|gte:0',
            'fixed_charge'   => 'required|numeric|gte:0',
            'percent_charge' => 'required|numeric|gte:0',
        ]);

        $general                 = gs();
        $general->charge_cap     = $request->charge_cap;
        $general->fixed_charge   = $request->fixed_charge;
        $general->percent_charge = $request->percent_charge;
        $general->save();

        $notify[] = ['success', 'Global charge settings updated successfully'];
        return back()->withNotify($notify);
    }

    public function store(Request $request, $id = 0)
    {
        $request->validate([
            'minimum'        => 'required|numeric|gt:0',
            'maximum'        => 'required|numeric|gt:minimum',
            'fixed_charge'   => 'required|numeric|gte:0',
            'percent_charge' => 'required|numeric|gte:0|regex:/^\d+(\.\d{1,2})?$/',
        ]);

        if ($id) {
            $charge  = EscrowCharge::findOrFail($id);
            $message = 'Charge updated successfully';
        } else {
            $charge  = new EscrowCharge();
            $message = 'Charge added successfully';
        }

        $charge->minimum        = $request->minimum;
        $charge->maximum        = $request->maximum;
        $charge->fixed_charge   = $request->fixed_charge;
        $charge->percent_charge = $request->percent_charge;
        $charge->save();

        $notify[] = ['success', $message];
        return back()->withNotify($notify);
    }

    public function remove($id)
    {
        $charge = EscrowCharge::findOrFail($id);
        $charge->delete();
        $notify[] = ['success', 'Charge deleted successfully'];
        return back()->withNotify($notify);
    }
}
