<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DomainVerification;
use App\Models\Listing;
use App\Models\MarketplaceSetting;
use Illuminate\Http\Request;

class DomainVerificationController extends Controller
{
    public function index()
    {
        $pageTitle = 'Domain Verifications';
        $verifications = DomainVerification::where('user_id', auth()->id())
            ->with('listing')
            ->latest()
            ->paginate(getPaginate());

        return view('templates.basic.user.verification.index', compact('pageTitle', 'verifications'));
    }

    public function show($id)
    {
        $verification = DomainVerification::where('user_id', auth()->id())
            ->with('listing')
            ->findOrFail($id);

        $pageTitle = 'Verify Domain: ' . $verification->domain;
        $instructions = $verification->getInstructions();

        return view('templates.basic.user.verification.show', compact('pageTitle', 'verification', 'instructions'));
    }

    public function initiate(Request $request, $listingId)
    {
        $listing = Listing::where('user_id', auth()->id())->findOrFail($listingId);

        // Check if verification is required
        if ($listing->business_type === 'domain' && !MarketplaceSetting::requireDomainVerification()) {
            return back()->with('error', 'Domain verification is not required');
        }

        if ($listing->business_type === 'website' && !MarketplaceSetting::requireWebsiteVerification()) {
            return back()->with('error', 'Website verification is not required');
        }

        // Check if already verified
        if ($listing->is_verified) {
            return back()->with('info', 'This listing is already verified');
        }

        $request->validate([
            'verification_method' => 'required|in:txt_file,dns_record',
        ]);

        // Check if method is allowed
        $allowedMethods = MarketplaceSetting::getDomainVerificationMethods();
        if (!in_array($request->verification_method, $allowedMethods)) {
            return back()->with('error', 'This verification method is not allowed');
        }

        // Create or update verification
        $verification = DomainVerification::createForListing($listing, $request->verification_method);

        if (!$verification) {
            return back()->with('error', 'Could not extract domain from listing');
        }

        return redirect()->route('user.verification.show', $verification->id);
    }

    public function verify(Request $request, $id)
    {
        $verification = DomainVerification::where('user_id', auth()->id())
            ->pending()
            ->notExpired()
            ->findOrFail($id);

        $result = $verification->verify();

        if ($result) {
            $notify[] = ['success', 'Domain verified successfully! Your listing is now pending admin approval.'];
            return redirect()->route('user.listing.index')->withNotify($notify);
        }

        $notify[] = ['error', $verification->error_message ?? 'Verification failed. Please check the instructions and try again.'];
        return back()->withNotify($notify);
    }

    public function changeMethod(Request $request, $id)
    {
        $verification = DomainVerification::where('user_id', auth()->id())
            ->pending()
            ->findOrFail($id);

        $request->validate([
            'verification_method' => 'required|in:txt_file,dns_record',
        ]);

        // Check if method is allowed
        $allowedMethods = MarketplaceSetting::getDomainVerificationMethods();
        if (!in_array($request->verification_method, $allowedMethods)) {
            return back()->with('error', 'This verification method is not allowed');
        }

        // Regenerate verification with new method
        $verification = DomainVerification::createForListing($verification->listing, $request->verification_method);

        $notify[] = ['success', 'Verification method changed successfully'];
        return redirect()->route('user.verification.show', $verification->id)->withNotify($notify);
    }

    public function downloadFile($id)
    {
        $verification = DomainVerification::where('user_id', auth()->id())
            ->where('verification_method', DomainVerification::METHOD_TXT_FILE)
            ->findOrFail($id);

        $content = $verification->verification_token;
        $filename = $verification->txt_filename;

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}

