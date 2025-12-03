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

                $lastError = null;
                $lastUrl = null;
                $lastContent = null;
                
                foreach ($urls as $url) {
                    try {
                        // Use cURL for better control and error handling
                        $ch = curl_init($url);
                        curl_setopt_array($ch, [
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_MAXREDIRS => 5,
                            CURLOPT_TIMEOUT => 10,
                            CURLOPT_CONNECTTIMEOUT => 10,
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => false,
                            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; VerificationBot/1.0)',
                            CURLOPT_HTTPHEADER => [
                                'Accept: text/plain, text/*, */*',
                            ],
                        ]);
                        
                        $content = curl_exec($ch);
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $curlError = curl_error($ch);
                        curl_close($ch);
                        
                        $lastUrl = $url;
                        
                        if ($content !== false && $httpCode >= 200 && $httpCode < 300) {
                            $lastContent = $content;
                            
                            // Normalize the content: remove BOM, normalize line endings, trim whitespace
                            $normalizedContent = $content;
                            
                            // Remove UTF-8 BOM if present
                            if (substr($normalizedContent, 0, 3) === "\xEF\xBB\xBF") {
                                $normalizedContent = substr($normalizedContent, 3);
                            }
                            
                            // Remove any null bytes
                            $normalizedContent = str_replace("\0", '', $normalizedContent);
                            
                            // Remove all line endings (CRLF, CR, LF)
                            $normalizedContent = preg_replace('/\r\n|\r|\n/', '', $normalizedContent);
                            
                            // Remove all other whitespace characters (tabs, spaces, etc.) from start and end
                            $normalizedContent = trim($normalizedContent);
                            
                            // Remove any remaining non-printable characters except alphanumeric and hyphens
                            // But keep the content as-is for now, just trim
                            
                            // Normalize token as well - remove any whitespace
                            $normalizedToken = trim($token);
                            
                            // Also try a more lenient comparison - remove all non-alphanumeric except hyphens
                            $strictContent = preg_replace('/[^a-zA-Z0-9\-]/', '', $normalizedContent);
                            $strictToken = preg_replace('/[^a-zA-Z0-9\-]/', '', $normalizedToken);
                            
                            // Debug: Log the comparison
                            \Log::info('Verification attempt', [
                                'url' => $url,
                                'http_code' => $httpCode,
                                'raw_content' => $content,
                                'raw_content_hex' => bin2hex(substr($content, 0, 50)),
                                'normalized_content' => $normalizedContent,
                                'strict_content' => $strictContent,
                                'expected_token' => $token,
                                'normalized_token' => $normalizedToken,
                                'strict_token' => $strictToken,
                                'exact_match' => $normalizedContent === $normalizedToken,
                                'strict_match' => $strictContent === $strictToken,
                                'content_length' => strlen($content),
                                'normalized_length' => strlen($normalizedContent),
                                'token_length' => strlen($normalizedToken),
                            ]);
                            
                            // Try exact match first
                            if ($normalizedContent === $normalizedToken) {
                                return response()->json([
                                    'success' => true,
                                    'message' => 'Domain ownership verified successfully!'
                                ]);
                            }
                            
                            // Try strict match (alphanumeric + hyphens only)
                            if ($strictContent === $strictToken && !empty($strictContent)) {
                                return response()->json([
                                    'success' => true,
                                    'message' => 'Domain ownership verified successfully!'
                                ]);
                            }
                        } else {
                            if ($curlError) {
                                $lastError = 'cURL Error: ' . $curlError;
                            } else {
                                $lastError = 'HTTP ' . $httpCode;
                            }
                        }
                    } catch (\Exception $e) {
                        $lastError = $e->getMessage();
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
                    
                    // Show detailed comparison
                    $errorDetails = 'File found but content does not match.';
                    $errorDetails .= ' Expected token: "' . trim($token) . '" (length: ' . strlen(trim($token)) . ')';
                    $errorDetails .= ' | Found in file: "' . $normalizedTest . '" (length: ' . strlen($normalizedTest) . ')';
                    if ($lastContent !== null) {
                        $errorDetails .= ' | Raw file content: "' . addslashes(substr($lastContent, 0, 100)) . '"';
                    }
                    $errorDetails .= ' | File URL: ' . $testUrl;
                } else {
                    $errorDetails .= ' Please ensure the file is accessible at: ' . $testUrl;
                    if ($lastError) {
                        $errorDetails .= ' | Error: ' . (is_array($lastError) ? $lastError['message'] : $lastError);
                    }
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

