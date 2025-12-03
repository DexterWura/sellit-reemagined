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

    /**
     * AJAX verification endpoint for listing creation page
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

        $domain = $request->domain;
        $method = $request->method;
        $token = $request->token;

        try {
            if ($method === DomainVerification::METHOD_TXT_FILE) {
                // Verify TXT file
                $filename = $request->filename;
                $urls = [
                    'https://' . $domain . '/' . $filename,
                    'https://' . $domain . '/.well-known/' . $filename,
                    'http://' . $domain . '/' . $filename,
                    'http://' . $domain . '/.well-known/' . $filename,
                ];

                foreach ($urls as $url) {
                    try {
                        $context = stream_context_create([
                            'http' => [
                                'timeout' => 10,
                                'follow_location' => true,
                                'user_agent' => 'Mozilla/5.0 (compatible; VerificationBot/1.0)',
                            ],
                            'ssl' => [
                                'verify_peer' => false,
                                'verify_peer_name' => false,
                            ],
                        ]);

                        $content = @file_get_contents($url, false, $context);
                        
                        if ($content !== false) {
                            // Normalize the content: remove BOM, normalize line endings, trim whitespace
                            $normalizedContent = $content;
                            // Remove UTF-8 BOM if present
                            if (substr($normalizedContent, 0, 3) === "\xEF\xBB\xBF") {
                                $normalizedContent = substr($normalizedContent, 3);
                            }
                            // Normalize line endings (CRLF, CR, LF to nothing, then trim)
                            $normalizedContent = preg_replace('/\r\n|\r|\n/', '', $normalizedContent);
                            // Trim all whitespace (including tabs, spaces, etc.)
                            $normalizedContent = trim($normalizedContent);
                            
                            // Normalize token as well
                            $normalizedToken = trim($token);
                            
                            if ($normalizedContent === $normalizedToken) {
                                return response()->json([
                                    'success' => true,
                                    'message' => 'Domain ownership verified successfully!'
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                // Try to get more details about why verification failed
                $testUrl = 'https://' . $domain . '/' . $filename;
                $testContent = @file_get_contents($testUrl, false, stream_context_create([
                    'http' => ['timeout' => 5, 'follow_location' => true, 'user_agent' => 'Mozilla/5.0'],
                    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
                ]));
                
                $errorDetails = 'Verification file not found or token mismatch.';
                if ($testContent !== false) {
                    // Normalize test content the same way we normalize in verification
                    $normalizedTest = $testContent;
                    if (substr($normalizedTest, 0, 3) === "\xEF\xBB\xBF") {
                        $normalizedTest = substr($normalizedTest, 3);
                    }
                    $normalizedTest = preg_replace('/\r\n|\r|\n/', '', $normalizedTest);
                    $normalizedTest = trim($normalizedTest);
                    
                    $errorDetails .= ' File found but content does not match. Expected: "' . trim($token) . '", Found: "' . $normalizedTest . '"';
                } else {
                    $errorDetails .= ' Please ensure the file is accessible at: ' . $testUrl;
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $errorDetails
                ]);

            } else {
                // Verify DNS TXT record
                $dnsName = $request->dns_name;
                $recordName = $dnsName . '.' . $domain;
                
                try {
                    $records = dns_get_record($recordName, DNS_TXT);
                    
                    if ($records) {
                        foreach ($records as $record) {
                            if (isset($record['txt']) && trim($record['txt']) === $token) {
                                return response()->json([
                                    'success' => true,
                                    'message' => 'Domain ownership verified successfully!'
                                ]);
                            }
                        }
                    }

                    // Also try without subdomain prefix
                    $records = dns_get_record($domain, DNS_TXT);
                    if ($records) {
                        foreach ($records as $record) {
                            if (isset($record['txt']) && trim($record['txt']) === $token) {
                                return response()->json([
                                    'success' => true,
                                    'message' => 'Domain ownership verified successfully!'
                                ]);
                            }
                        }
                    }

                    return response()->json([
                        'success' => false,
                        'message' => 'DNS TXT record not found. Please add a TXT record with name "' . $dnsName . '" and value "' . $token . '". DNS propagation may take up to 24-48 hours.'
                    ]);

                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'DNS lookup failed: ' . $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage()
            ], 500);
        }
    }
}

