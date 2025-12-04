<?php

namespace App\Jobs;

use App\Models\DomainVerification;
use App\Models\VerificationAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CleanupExpiredVerifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting cleanup of expired verifications');

        // Mark expired verifications
        $expiredCount = DomainVerification::expired()
            ->where('status', '!=', DomainVerification::STATUS_EXPIRED)
            ->update([
                'status' => DomainVerification::STATUS_EXPIRED,
                'error_message' => 'Verification expired',
            ]);

        // Delete old verification attempts (older than 30 days)
        $oldAttemptsCount = VerificationAttempt::where('attempted_at', '<', now()->subDays(30))
            ->delete();

        // Delete very old expired verifications (older than 90 days)
        $oldVerificationsCount = DomainVerification::where('status', DomainVerification::STATUS_EXPIRED)
            ->where('expires_at', '<', now()->subDays(90))
            ->delete();

        Log::info('Cleanup completed', [
            'expired_marked' => $expiredCount,
            'old_attempts_deleted' => $oldAttemptsCount,
            'old_verifications_deleted' => $oldVerificationsCount,
        ]);
    }
}
