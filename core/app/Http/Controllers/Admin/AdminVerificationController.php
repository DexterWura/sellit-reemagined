<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomainVerification;
use App\Models\VerificationSetting;
use App\Models\VerificationAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminVerificationController extends Controller
{

    public function verifications(Request $request)
    {
        $pageTitle = 'Domain Verifications';

        $query = DomainVerification::with(['user', 'listing']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by method
        if ($request->filled('method')) {
            $query->where('verification_method', $request->method);
        }

        // Search by domain or user
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('domain', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('username', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $verifications = $query->latest()->paginate(getPaginate());

        return view('admin.verification.index', compact('pageTitle', 'verifications'));
    }

    public function show($id)
    {
        $verification = DomainVerification::with(['user', 'listing', 'attempts'])->findOrFail($id);
        $pageTitle = 'Verification Details: ' . $verification->domain;

        return view('admin.verification.show', compact('pageTitle', 'verification'));
    }

    public function expire($id)
    {
        $verification = DomainVerification::findOrFail($id);
        $verification->update([
            'status' => DomainVerification::STATUS_EXPIRED,
            'expires_at' => now(),
        ]);

        $notify[] = ['success', 'Verification expired successfully'];
        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        $verification = DomainVerification::findOrFail($id);
        $verification->delete();

        $notify[] = ['success', 'Verification deleted successfully'];
        return back()->withNotify($notify);
    }

    public function statistics()
    {
        $pageTitle = 'Verification Statistics';

        $stats = [
            'total_verifications' => DomainVerification::count(),
            'pending_verifications' => DomainVerification::where('status', DomainVerification::STATUS_PENDING)->count(),
            'verified_domains' => DomainVerification::where('status', DomainVerification::STATUS_VERIFIED)->count(),
            'failed_verifications' => DomainVerification::where('status', DomainVerification::STATUS_FAILED)->count(),
            'expired_verifications' => DomainVerification::where('status', DomainVerification::STATUS_EXPIRED)->count(),
            'file_method_count' => DomainVerification::where('verification_method', DomainVerification::METHOD_FILE)->count(),
            'dns_method_count' => DomainVerification::where('verification_method', DomainVerification::METHOD_DNS)->count(),
            'avg_attempts' => DomainVerification::avg('attempt_count'),
            'total_attempts' => VerificationAttempt::count(),
            'successful_attempts' => VerificationAttempt::whereNull('error_message')->count(),
            'failed_attempts' => VerificationAttempt::whereNotNull('error_message')->count(),
        ];

        // Recent activity
        $recentVerifications = DomainVerification::with(['user', 'listing'])
            ->latest()
            ->limit(10)
            ->get();

        $recentAttempts = VerificationAttempt::with(['domainVerification.user'])
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.verification.statistics', compact(
            'pageTitle',
            'stats',
            'recentVerifications',
            'recentAttempts'
        ));
    }

    public function cleanup()
    {
        $expiredCount = DomainVerification::expired()->update([
            'status' => DomainVerification::STATUS_EXPIRED,
        ]);

        // Delete old verification attempts (older than 30 days)
        $oldAttemptsCount = VerificationAttempt::where('attempted_at', '<', now()->subDays(30))->delete();

        $notify[] = ['success', "Cleanup completed. {$expiredCount} verifications expired, {$oldAttemptsCount} old attempts deleted."];
        return back()->withNotify($notify);
    }

    public function debug()
    {
        $debug = [
            'marketplace_settings' => [
                'require_domain_verification' => \App\Models\MarketplaceSetting::requireDomainVerification(),
                'require_website_verification' => \App\Models\MarketplaceSetting::requireWebsiteVerification(),
                'require_social_media_verification' => \App\Models\MarketplaceSetting::requireSocialMediaVerification(),
                'domain_verification_methods' => \App\Models\MarketplaceSetting::getDomainVerificationMethods(),
            ],
            'marketplace_setting_methods' => [
                'requireDomainVerification' => \App\Models\MarketplaceSetting::requireDomainVerification(),
                'requireWebsiteVerification' => \App\Models\MarketplaceSetting::requireWebsiteVerification(),
                'requireSocialMediaVerification' => \App\Models\MarketplaceSetting::requireSocialMediaVerification(),
                'getDomainVerificationMethods' => \App\Models\MarketplaceSetting::getDomainVerificationMethods(),
            ],
            'total_verifications' => DomainVerification::count(),
            'pending_verifications' => DomainVerification::where('status', DomainVerification::STATUS_PENDING)->count(),
            'verified_domains' => DomainVerification::where('status', DomainVerification::STATUS_VERIFIED)->count(),
            'failed_verifications' => DomainVerification::where('status', DomainVerification::STATUS_FAILED)->count(),
            'expired_verifications' => DomainVerification::where('status', DomainVerification::STATUS_EXPIRED)->count(),
        ];

        return view('admin.verification.debug', compact('debug'));
    }
}
