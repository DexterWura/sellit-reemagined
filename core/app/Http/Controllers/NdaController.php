<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Models\Listing;
use App\Models\NdaDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NdaController extends Controller
{
    public function show($listingId)
    {
        $listing = Listing::active()->findOrFail($listingId);
        
        // Check if listing requires NDA
        if (!$listing->is_confidential || !$listing->requires_nda) {
            abort(404);
        }

        // Check if user is seller
        if (auth()->check() && auth()->id() === $listing->user_id) {
            return redirect()->route('marketplace.listing.show', $listing->slug);
        }

        // Check if user has already signed NDA
        if (auth()->check() && $listing->hasSignedNda()) {
            return redirect()->route('marketplace.listing.show', $listing->slug);
        }

        $pageTitle = 'Non-Disclosure Agreement Required';
        
        return view('Template::marketplace.nda.show', compact('pageTitle', 'listing'));
    }

    public function sign(Request $request, $listingId)
    {
        $request->validate([
            'signature' => 'required|string|max:255',
            'agree_terms' => 'required|accepted',
        ]);

        try {
            $listing = Listing::active()->findOrFail($listingId);
            
            // Check if listing requires NDA
            if (!$listing->is_confidential || !$listing->requires_nda) {
                $notify[] = ['error', 'This listing does not require an NDA'];
                return back()->withNotify($notify);
            }

            // Check if user is seller
            if (auth()->id() === $listing->user_id) {
                $notify[] = ['error', 'You cannot sign an NDA for your own listing'];
                return back()->withNotify($notify);
            }

            // Check if already signed
            if ($listing->hasSignedNda()) {
                $notify[] = ['success', 'You have already signed the NDA'];
                return redirect()->route('marketplace.listing.show', $listing->slug)->withNotify($notify);
            }

            DB::beginTransaction();
            
            try {
                // Create NDA document record
                $nda = new NdaDocument();
                $nda->listing_id = $listing->id;
                $nda->user_id = auth()->id();
                $nda->signature = $request->signature;
                $nda->signed_at = now();
                $nda->status = 'signed';
                $nda->ip_address = $request->ip();
                $nda->user_agent = $request->userAgent();
                $nda->expires_at = now()->addYear(); // NDA valid for 1 year
                $nda->save();

                // Generate and store NDA document PDF (optional - can be implemented later)
                // $documentPath = $this->generateNdaPdf($nda);
                // $nda->document_path = $documentPath;
                // $nda->save();

                DB::commit();

                // Notify seller
                notify($listing->seller, 'NDA_SIGNED', [
                    'listing_title' => $listing->title,
                    'signer' => auth()->user()->username,
                    'signed_at' => now()->format('Y-m-d H:i:s'),
                ]);

                $notify[] = ['success', 'NDA signed successfully. You can now view the listing details.'];
                return redirect()->route('marketplace.listing.show', $listing->slug)->withNotify($notify);
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('NDA signing failed: ' . $e->getMessage(), [
                    'listing_id' => $listingId,
                    'user_id' => auth()->id(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('NDA signing error: ' . $e->getMessage());
            $notify[] = ['error', 'An error occurred while signing the NDA. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    public function download($id)
    {
        $nda = NdaDocument::where('user_id', auth()->id())->findOrFail($id);
        
        if (!$nda->document_path || !Storage::exists($nda->document_path)) {
            abort(404, 'NDA document not found');
        }

        return Storage::download($nda->document_path, 'nda-' . $nda->listing->listing_number . '.pdf');
    }

    public function myNdas()
    {
        $pageTitle = 'My NDA Documents';
        $user = auth()->user();

        $ndas = NdaDocument::where('user_id', $user->id)
            ->with(['listing.images', 'listing.seller'])
            ->orderBy('signed_at', 'desc')
            ->paginate(getPaginate());

        return view('Template::user.nda.index', compact('pageTitle', 'ndas'));
    }
}

