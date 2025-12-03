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
        
        // Log the incoming request for debugging
        \Log::info('Verification request received', [
            'domain' => $domain,
            'method' => $method,
            'token' => $token,
            'token_length' => strlen($token),
            'token_hex' => bin2hex(substr($token, 0, 50)),
            'filename' => $request->filename ?? null,
            'dns_name' => $request->dns_name ?? null,
        ]);

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
                            
                            // Also remove any zero-width spaces or other invisible characters
                            $normalizedContent = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $normalizedContent);
                            
                            // Normalize token as well - remove any whitespace and invisible characters
                            $normalizedToken = trim($token);
                            $normalizedToken = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $normalizedToken);
                            
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

                // Build detailed error message
                $errorDetails = 'Verification failed. ';
                
                if ($lastContent !== null) {
                    // We got content but it didn't match
                    $normalizedLast = $lastContent;
                    if (substr($normalizedLast, 0, 3) === "\xEF\xBB\xBF") {
                        $normalizedLast = substr($normalizedLast, 3);
                    }
                    $normalizedLast = str_replace("\0", '', $normalizedLast);
                    $normalizedLast = preg_replace('/\r\n|\r|\n/', '', $normalizedLast);
                    $normalizedLast = trim($normalizedLast);
                    
                    $normalizedToken = trim($token);
                    
                    // Show detailed comparison with hex dumps for debugging
                    $errorDetails = 'File found but content does not match.';
                    $errorDetails .= "\n\nExpected: \"" . $normalizedToken . "\" (length: " . strlen($normalizedToken) . ")";
                    $errorDetails .= "\nFound:    \"" . $normalizedLast . "\" (length: " . strlen($normalizedLast) . ")";
                    
                    // Show first 50 chars in hex for both
                    $errorDetails .= "\n\nExpected (hex): " . bin2hex(substr($normalizedToken, 0, 50));
                    $errorDetails .= "\nFound (hex):    " . bin2hex(substr($normalizedLast, 0, 50));
                    
                    // Show raw content (first 200 chars)
                    $errorDetails .= "\n\nRaw file content (first 200 chars): " . addslashes(substr($lastContent, 0, 200));
                    $errorDetails .= "\nFile URL: " . ($lastUrl ?: 'https://' . $domain . '/' . $filename);
                    
                    // Character-by-character comparison for first 50 chars
                    $errorDetails .= "\n\nCharacter comparison (first 50):";
                    $maxLen = max(strlen($normalizedToken), strlen($normalizedLast), 50);
                    for ($i = 0; $i < min($maxLen, 50); $i++) {
                        $expChar = isset($normalizedToken[$i]) ? $normalizedToken[$i] : '[MISSING]';
                        $foundChar = isset($normalizedLast[$i]) ? $normalizedLast[$i] : '[MISSING]';
                        $expHex = isset($normalizedToken[$i]) ? bin2hex($normalizedToken[$i]) : '--';
                        $foundHex = isset($normalizedLast[$i]) ? bin2hex($normalizedLast[$i]) : '--';
                        $match = ($expChar === $foundChar) ? '✓' : '✗';
                        $errorDetails .= "\n  [$i] Expected: '$expChar' (0x$expHex) | Found: '$foundChar' (0x$foundHex) $match";
                    }
                } else {
                    // File not accessible
                    $testUrl = 'https://' . $domain . '/' . $filename;
                    $errorDetails .= 'File not accessible at: ' . $testUrl;
                    if ($lastError) {
                        $errorDetails .= ' | Error: ' . (is_array($lastError) ? $lastError['message'] : $lastError);
                    }
                    $errorDetails .= "\n\nPlease ensure:";
                    $errorDetails .= "\n1. The file is uploaded to your domain root";
                    $errorDetails .= "\n2. The file is accessible via HTTPS";
                    $errorDetails .= "\n3. The file contains ONLY the verification token (no extra spaces or characters)";
                    $errorDetails .= "\n4. Expected token: \"" . trim($token) . "\"";
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

