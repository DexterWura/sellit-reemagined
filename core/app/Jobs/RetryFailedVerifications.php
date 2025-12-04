<?php

namespace App\Jobs;

use App\Models\DomainVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RetryFailedVerifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting retry of failed verifications');

        // Find verifications that can be retried
        $retryableVerifications = DomainVerification::where('status', DomainVerification::STATUS_FAILED)
            ->where('attempt_count', '<', \App\Models\VerificationSetting::getMaxAttempts())
            ->where('expires_at', '>', now())
            ->where('last_attempt_at', '<', now()->subHours(1)) // Don't retry too frequently
            ->get();

        $retryCount = 0;
        $successCount = 0;

        foreach ($retryableVerifications as $verification) {
            $retryCount++;

            try {
                $result = $verification->verify();

                if ($result) {
                    $successCount++;
                    Log::info('Verification retry succeeded', [
                        'verification_id' => $verification->id,
                        'domain' => $verification->domain,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Verification retry failed', [
                    'verification_id' => $verification->id,
                    'domain' => $verification->domain,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Retry job completed', [
            'total_retried' => $retryCount,
            'successful_retries' => $successCount,
        ]);
    }
}
