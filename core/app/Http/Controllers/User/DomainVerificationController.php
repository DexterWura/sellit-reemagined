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
            $notify[] = ['info', 'Domain verification is not required for this listing'];
            return back()->withNotify($notify);
        }

        if ($listing->business_type === 'website' && !MarketplaceSetting::requireWebsiteVerification()) {
            $notify[] = ['info', 'Website verification is not required for this listing'];
            return back()->withNotify($notify);
        }

        // Check if already verified
        if ($listing->is_verified) {
            $notify[] = ['success', 'This listing is already verified'];
            return back()->withNotify($notify);
        }

        // Validate domain/website is accessible before starting verification
        $domain = DomainVerification::extractDomain($listing);
        if (!$domain) {
            $notify[] = ['error', 'Could not extract domain from listing. Please ensure the domain/website URL is valid.'];
            return back()->withNotify($notify);
        }

        // Check accessibility
        $url = $listing->url ?? ($listing->business_type === 'domain' ? 'https://' . $domain : null);
        if ($url) {
            $accessibility = checkDomainAccessibility($url, 5);
            if (!$accessibility['accessible']) {
                $notify[] = ['error', 'Domain/website is not accessible. Please ensure it is live and accessible before verification. Error: ' . ($accessibility['error'] ?? 'Unknown error')];
                return back()->withNotify($notify);
            }
        }

        $request->validate([
            'verification_method' => 'required|in:txt_file,dns_record',
        ]);

        // Check if method is allowed
        $allowedMethods = MarketplaceSetting::getDomainVerificationMethods();
        if (!in_array($request->verification_method, $allowedMethods)) {
            $notify[] = ['error', 'This verification method is not allowed'];
            return back()->withNotify($notify);
        }

        // Create or update verification
        $verification = DomainVerification::createForListing($listing, $request->verification_method);

        if (!$verification) {
            $notify[] = ['error', 'Could not create verification. Please ensure the domain/website URL is valid.'];
            return back()->withNotify($notify);
        }

        $notify[] = ['success', 'Verification process started. Please follow the instructions below.'];
        return redirect()->route('user.verification.show', $verification->id)->withNotify($notify);
    }

    public function verify(Request $request, $id)
    {
        $verification = DomainVerification::where('user_id', auth()->id())
            ->pending()
            ->notExpired()
            ->findOrFail($id);

        $result = $verification->verify();

        if ($result) {
            // Update listing status
            $listing = $verification->listing;
            if ($listing) {
                $listing->is_verified = true;
                $listing->requires_verification = false;
                if ($listing->status === Status::LISTING_DRAFT) {
                    $listing->status = Status::LISTING_PENDING;
                }
                $listing->save();
            }
            
            $notify[] = ['success', 'Domain verified successfully! Your listing is now pending admin approval.'];
            return redirect()->route('user.listing.index')->withNotify($notify);
        }

        $errorMessage = $verification->error_message ?? 'Verification failed. Please check the instructions and try again.';
        
        // Provide helpful error messages
        if (strpos($errorMessage, 'File not accessible') !== false) {
            $errorMessage .= "\n\nTips:\n- Ensure the file is uploaded to your domain root directory\n- Check that the file is accessible via HTTPS\n- Wait a few minutes for DNS/CDN propagation\n- Verify the file contains ONLY the verification token (no extra spaces or characters)";
        } elseif (strpos($errorMessage, 'DNS') !== false) {
            $errorMessage .= "\n\nTips:\n- DNS changes can take 24-48 hours to propagate\n- Ensure the TXT record name and value are exactly as shown\n- Check your DNS provider's documentation for adding TXT records";
        }
        
        $notify[] = ['error', $errorMessage];
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

    /**
     * AJAX verification endpoint for listing creation page
     * Simplified, more reliable verification
     */
    public function verifyAjax(Request $request)
    {
        $request->validate([
            'domain' => 'required|string',
            'method' => 'required|in:txt_file,dns_record',
            'token' => 'required|string',
            'filename' => 'required_if:method,txt_file|string',
            'dns_name' => 'required_if:method,dns_record|string',
        ]);

        // Check if method is allowed
        $allowedMethods = MarketplaceSetting::getDomainVerificationMethods();
        if (!in_array($request->method, $allowedMethods)) {
            return response()->json([
                'success' => false,
                'message' => 'This verification method is not allowed'
            ], 400);
        }

        $domain = trim($request->domain);
        $method = $request->method;
        $token = trim($request->token);

        try {
            if ($method === DomainVerification::METHOD_TXT_FILE) {
                return $this->verifyTxtFileAjax($domain, $token, $request->filename);
            } else {
                return $this->verifyDnsAjax($domain, $token, $request->dns_name);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify TXT file via AJAX
     */
    private function verifyTxtFileAjax($domain, $token, $filename)
    {
        // Try these locations in order
        $urls = [
            'https://' . $domain . '/' . $filename,
            'http://' . $domain . '/' . $filename,
            'https://' . $domain . '/.well-known/' . $filename,
        ];

        $lastError = null;
        $foundContent = null;

        foreach ($urls as $url) {
            try {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 3,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_CONNECTTIMEOUT => 8,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; VerificationBot/1.0)',
                ]);
                
                $content = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);
                
                if ($content !== false && $httpCode >= 200 && $httpCode < 300) {
                    $foundContent = $content;
                    
                    // Simple normalization: remove all whitespace
                    $cleanContent = preg_replace('/\s+/', '', trim($content));
                    $cleanToken = preg_replace('/\s+/', '', trim($token));
                    
                    if ($cleanContent === $cleanToken) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Domain ownership verified successfully!'
                        ]);
                    }
                } else {
                    if ($curlError) {
                        $lastError = $curlError;
                    } else {
                        $lastError = "HTTP $httpCode";
                    }
                }
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                continue;
            }
        }

        // Build helpful error message
        $errorMessage = "Verification failed. ";
        
        if ($foundContent !== null) {
            $errorMessage = "File found but content doesn't match.\n\n";
            $errorMessage .= "Expected: " . substr($token, 0, 50) . "...\n";
            $errorMessage .= "Found: " . substr(trim($foundContent), 0, 50) . "...\n\n";
            $errorMessage .= "Please ensure the file contains ONLY the verification token with no extra spaces or characters.";
        } else {
            $errorMessage .= "File not accessible at: https://{$domain}/{$filename}\n\n";
            $errorMessage .= "Please ensure:\n";
            $errorMessage .= "1. The file is uploaded to your domain root directory\n";
            $errorMessage .= "2. The file is accessible via HTTPS\n";
            $errorMessage .= "3. The file name is exactly: {$filename}\n";
            $errorMessage .= "4. The file contains ONLY this text: {$token}";
            
            if ($lastError) {
                $errorMessage .= "\n\nError: {$lastError}";
            }
        }
        
        return response()->json([
            'success' => false,
            'message' => $errorMessage
        ]);
    }

    /**
     * Verify DNS TXT record via AJAX
     */
    private function verifyDnsAjax($domain, $token, $dnsName)
    {
        try {
            // Try with subdomain prefix
            $recordName = $dnsName . '.' . $domain;
            $records = @dns_get_record($recordName, DNS_TXT);
            
            if ($records && is_array($records)) {
                foreach ($records as $record) {
                    if (isset($record['txt'])) {
                        $recordValue = trim($record['txt'], '"');
                        if ($recordValue === $token) {
                            return response()->json([
                                'success' => true,
                                'message' => 'Domain ownership verified successfully!'
                            ]);
                        }
                    }
                }
            }

            // Try at domain root
            $records = @dns_get_record($domain, DNS_TXT);
            if ($records && is_array($records)) {
                foreach ($records as $record) {
                    if (isset($record['txt'])) {
                        $recordValue = trim($record['txt'], '"');
                        if ($recordValue === $token) {
                            return response()->json([
                                'success' => true,
                                'message' => 'Domain ownership verified successfully!'
                            ]);
                        }
                    }
                }
            }

            $errorMessage = "DNS TXT record not found.\n\n";
            $errorMessage .= "Please add a TXT record with:\n";
            $errorMessage .= "Name/Host: {$dnsName}\n";
            $errorMessage .= "Value/Content: {$token}\n\n";
            $errorMessage .= "Note: DNS changes can take 5 minutes to 48 hours to propagate. Please wait a few minutes and try again.";

            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'DNS lookup failed: ' . $e->getMessage() . '. Please check your DNS settings.'
            ]);
        }
    }
}

