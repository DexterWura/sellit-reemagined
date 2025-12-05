<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SocialMediaVerificationController extends Controller
{
    /**
     * Generate verification data for social media account
     */
    public function generateVerification(Request $request)
    {
        $request->validate([
            'platform' => 'required|string',
            'username' => 'required|string',
            'method' => 'required|in:post_verification',
        ]);

        $platform = trim($request->platform);
        $username = trim($request->username);
        $method = $request->method;

        // Check if verification is enabled
        if (!MarketplaceSetting::requireSocialMediaVerification()) {
            return response()->json([
                'success' => false,
                'message' => 'Social media verification is not enabled'
            ], 400);
        }

        // Check if user already has a verified account
        $verifiedCacheKey = 'verified_social_' . auth()->id() . '_' . $platform . '_' . $username;
        $verifiedData = Cache::get($verifiedCacheKey);

        if ($verifiedData) {
            return response()->json([
                'success' => false,
                'message' => 'This account is already verified'
            ], 400);
        }

        // Generate a unique token
        $token = $this->generateToken();

        $message = $this->generateVerificationMessage($platform, $token);

        // Store verification data in cache (expires in 24 hours)
        $cacheKey = 'verification_' . auth()->id() . '_' . $platform . '_' . $username;
        Cache::put($cacheKey, [
            'platform' => $platform,
            'username' => $username,
            'token' => $token,
            'message' => $message,
            'created_at' => now(),
        ], 86400);

        return response()->json([
            'success' => true,
            'token' => $token,
            'message' => $message,
            'instructions' => $this->getPlatformInstructions($platform, $username, $message),
        ]);
    }

    /**
     * Verify social media account ownership
     */
    public function verifySocialMedia(Request $request)
    {
        $request->validate([
            'platform' => 'required|string',
            'username' => 'required|string',
            'token' => 'required|string',
        ]);

        $platform = trim($request->platform);
        $username = trim($request->username);
        $token = trim($request->token);

        // Check if verification is enabled
        if (!MarketplaceSetting::requireSocialMediaVerification()) {
            return response()->json([
                'success' => false,
                'message' => 'Social media verification is not enabled'
            ], 400);
        }

        // Check if user already has a pending verification for this account
        $cacheKey = 'verification_' . auth()->id() . '_' . $platform . '_' . $username;
        $verificationData = Cache::get($cacheKey);

        if (!$verificationData || $verificationData['token'] !== $token) {
            return response()->json([
                'success' => false,
                'message' => 'Verification session expired or invalid. Please start verification again.'
            ], 400);
        }

        // For now, this is manual verification - user claims they posted the message
        // In a real implementation, you'd use platform APIs to check if the post exists
        // For this simplified version, we'll accept the user's claim and store verification

        // Store successful verification
        $verifiedCacheKey = 'verified_social_' . auth()->id() . '_' . $platform . '_' . $username;
        Cache::put($verifiedCacheKey, [
            'platform' => $platform,
            'username' => $username,
            'verified_at' => now(),
            'token' => $token,
            'method' => 'post_verification'
        ], 2592000); // 30 days

        return response()->json([
            'success' => true,
            'message' => 'Social media account verified successfully!',
        ]);
    }

    /**
     * Generate platform-specific verification message
     */
    private function generateVerificationMessage($platform, $token)
    {
        $platforms = [
            'twitter' => 'Twitter',
            'instagram' => 'Instagram',
            'facebook' => 'Facebook',
            'linkedin' => 'LinkedIn',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok',
        ];

        $platformName = $platforms[$platform] ?? ucfirst($platform);

        return "Verifying ownership of this {$platformName} account. Verification token: {$token}";
    }

    /**
     * Get platform-specific instructions
     */
    private function getPlatformInstructions($platform, $username, $message)
    {
        $instructions = [
            'twitter' => [
                'steps' => [
                    "Make a public post with this exact message:",
                    "Post it from the account: @{$username}",
                    "Once posted, click 'Verify Account' below"
                ]
            ],
            'instagram' => [
                'steps' => [
                    "Create a new post with this exact caption:",
                    "Post it from the account: @{$username}",
                    "Once posted, click 'Verify Account' below"
                ]
            ],
            'facebook' => [
                'steps' => [
                    "Create a new post with this exact text:",
                    "Post it from the page/profile: {$username}",
                    "Once posted, click 'Verify Account' below"
                ]
            ],
            'linkedin' => [
                'steps' => [
                    "Create a new post with this exact text:",
                    "Post it from your LinkedIn profile",
                    "Once posted, click 'Verify Account' below"
                ]
            ],
            'youtube' => [
                'steps' => [
                    "Create a new video with this exact title or description:",
                    "Post it from the channel: {$username}",
                    "Once posted, click 'Verify Account' below"
                ]
            ],
            'tiktok' => [
                'steps' => [
                    "Create a new video with this exact caption:",
                    "Post it from the account: @{$username}",
                    "Once posted, click 'Verify Account' below"
                ]
            ],
        ];

        return $instructions[$platform] ?? [
            'steps' => [
                "Post this message on your {$platform} account:",
                "Once posted, click 'Verify Account' below"
            ]
        ];
    }

    /**
     * Validate token format (basic validation)
     */
    private function validateTokenFormat($token)
    {
        // Basic validation - token should start with 'verify_' and be 32 chars long
        return strlen($token) === 32 && strpos($token, 'verify_') === 0;
    }

    /**
     * Generate a unique verification token
     */
    private function generateToken()
    {
        return 'verify_' . bin2hex(random_bytes(16));
    }
}