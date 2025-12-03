<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\SocialMediaVerification;
use App\Lib\SocialLogin;
use Illuminate\Http\Request;
use Socialite;
use Illuminate\Support\Facades\Config;

class SocialMediaVerificationController extends Controller
{
    /**
     * Redirect to social media platform for verification
     * Can work with or without a listing (for pre-verification)
     */
    public function redirect($platform, $listingId = null)
    {
        // If listing ID provided, verify it belongs to user
        if ($listingId) {
            $listing = Listing::where('user_id', auth()->id())
                ->where('business_type', 'social_media_account')
                ->findOrFail($listingId);
        } else {
            $listing = null;
        }

        // Validate platform
        $allowedPlatforms = ['instagram', 'youtube', 'tiktok', 'twitter', 'facebook'];
        if (!in_array($platform, $allowedPlatforms)) {
            $notify[] = ['error', 'Invalid platform'];
            return back()->withNotify($notify);
        }

        // Create verification record if listing exists, otherwise store in session
        if ($listing) {
            $verification = SocialMediaVerification::createForListing($listing, $platform);
            session([
                'social_verification_listing_id' => $listingId,
                'social_verification_id' => $verification->id,
            ]);
        } else {
            // Store verification data in session for later use
            session([
                'social_verification_platform' => $platform,
                'social_verification_pending' => true,
            ]);
        }

        // Configure and redirect to platform
        try {
            $this->configureProvider($platform);
            return Socialite::driver($platform)->redirect();
        } catch (\Exception $e) {
            $verification->markAsFailed('Failed to initiate OAuth: ' . $e->getMessage());
            $notify[] = ['error', 'Failed to connect to ' . ucfirst($platform) . '. Please check platform configuration.'];
            return back()->withNotify($notify);
        }
    }

    /**
     * Handle OAuth callback from social media platform
     */
    public function callback($platform)
    {
        $listingId = session('social_verification_listing_id');
        $verificationId = session('social_verification_id');
        $isPending = session('social_verification_pending', false);

        // Handle pending verification (no listing yet)
        if ($isPending && !$listingId) {
            try {
                $this->configureProvider($platform);
                $socialUser = Socialite::driver($platform)->user();

                $accountId = $socialUser->getId();
                $accountUsername = $socialUser->getNickname() ?? $socialUser->getName() ?? null;

                // Store verification in session for form submission
                session([
                    'social_verified' => true,
                    'verified_platform' => $platform,
                    'verified_account_id' => $accountId,
                    'verified_account_username' => $accountUsername,
                    'social_verification_pending' => false,
                ]);

                $notify[] = ['success', ucfirst($platform) . ' account verified successfully! You can now continue creating your listing.'];
                return redirect()->route('user.listing.create')->withNotify($notify);

            } catch (\Exception $e) {
                session()->forget(['social_verification_platform', 'social_verification_pending']);
                $notify[] = ['error', 'Verification failed: ' . $e->getMessage()];
                return redirect()->route('user.listing.create')->withNotify($notify);
            }
        }

        // Handle existing listing verification
        if (!$listingId || !$verificationId) {
            $notify[] = ['error', 'Verification session expired. Please try again.'];
            return redirect()->route('user.listing.index')->withNotify($notify);
        }

        $listing = Listing::where('user_id', auth()->id())->findOrFail($listingId);
        $verification = SocialMediaVerification::findOrFail($verificationId);

        try {
            $this->configureProvider($platform);
            $socialUser = Socialite::driver($platform)->user();

            // Extract account information
            $accountId = $socialUser->getId();
            $accountUsername = $socialUser->getNickname() ?? $socialUser->getName() ?? null;

            // Mark as verified
            $verification->markAsVerified($accountId, $accountUsername);

            // Update listing with account info if needed
            if (!$listing->platform || $listing->platform === $platform) {
                $listing->platform = $platform;
                if ($accountUsername && !$listing->url) {
                    // Try to construct URL from platform and username
                    $listing->url = $this->constructAccountUrl($platform, $accountUsername);
                }
                $listing->save();
            }

            // Clear session
            session()->forget(['social_verification_listing_id', 'social_verification_id']);

            $notify[] = ['success', ucfirst($platform) . ' account verified successfully!'];
            return redirect()->route('user.listing.edit', $listingId)->withNotify($notify);

        } catch (\Exception $e) {
            $verification->markAsFailed('OAuth callback failed: ' . $e->getMessage());
            $notify[] = ['error', 'Verification failed: ' . $e->getMessage()];
            return redirect()->route('user.listing.edit', $listingId)->withNotify($notify);
        }
    }

    /**
     * Configure social media provider
     */
    private function configureProvider($platform)
    {
        // Map platform to socialite provider name
        $providerMap = [
            'instagram' => 'instagram',
            'youtube' => 'google', // YouTube uses Google OAuth
            'tiktok' => 'tiktok',
            'twitter' => 'twitter',
            'facebook' => 'facebook',
        ];

        $provider = $providerMap[$platform] ?? $platform;

        // Get credentials from general settings
        $credentials = gs('socialite_credentials');
        
        if (!$credentials || !isset($credentials->$provider)) {
            throw new \Exception('Platform credentials not configured');
        }

        $config = $credentials->$provider;

        if (!isset($config->status) || $config->status != 1) {
            throw new \Exception('Platform OAuth is not enabled');
        }

        Config::set('services.' . $provider, [
            'client_id' => $config->client_id,
            'client_secret' => $config->client_secret,
            'redirect' => route('user.social.verification.callback', $platform),
        ]);
    }

    /**
     * Construct account URL from platform and username
     */
    private function constructAccountUrl($platform, $username)
    {
        $urls = [
            'instagram' => 'https://instagram.com/' . ltrim($username, '@'),
            'youtube' => 'https://youtube.com/@' . ltrim($username, '@'),
            'tiktok' => 'https://tiktok.com/@' . ltrim($username, '@'),
            'twitter' => 'https://twitter.com/' . ltrim($username, '@'),
            'facebook' => 'https://facebook.com/' . ltrim($username, '@'),
        ];

        return $urls[$platform] ?? null;
    }
}

