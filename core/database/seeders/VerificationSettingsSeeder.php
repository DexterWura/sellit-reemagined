<?php

namespace Database\Seeders;

use App\Models\VerificationSetting;
use Illuminate\Database\Seeder;

class VerificationSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        VerificationSetting::firstOrCreate([], [
            'require_verification' => false,
            'allowed_methods' => ['file', 'dns'],
            'max_verification_attempts' => 5,
            'verification_timeout_seconds' => 300,
        ]);
    }
}
