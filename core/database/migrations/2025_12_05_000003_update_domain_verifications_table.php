<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domain_verifications', function (Blueprint $table) {
            // Add new fields from technical plan
            $table->jsonb('verification_data')->nullable()->after('verification_token');
            $table->integer('attempt_count')->default(0)->after('verification_data');
            $table->timestamp('last_attempt_at')->nullable()->after('attempt_count');
            $table->timestamp('verified_at')->nullable()->after('last_attempt_at');

            // Rename status values to match technical plan
            // We'll handle data migration in a separate seeder if needed

            // Add indexes as per technical plan
            $table->index(['status'], 'idx_domain_verifications_status');
            $table->index(['user_id'], 'idx_domain_verifications_user');
            $table->index(['listing_id'], 'idx_domain_verifications_listing');
            $table->index(['expires_at'], 'idx_domain_verifications_expires');

            // Add unique constraint as per technical plan
            // First drop existing unique constraint if it exists
            try {
                $table->dropUnique('listing_id');
            } catch (\Exception $e) {
                // Constraint might not exist, continue
            }

            $table->unique(['domain', 'user_id', 'listing_id'], 'unique_domain_user_listing');
        });
    }

    public function down(): void
    {
        Schema::table('domain_verifications', function (Blueprint $table) {
            $table->dropIndex('idx_domain_verifications_status');
            $table->dropIndex('idx_domain_verifications_user');
            $table->dropIndex('idx_domain_verifications_listing');
            $table->dropIndex('idx_domain_verifications_expires');
            $table->dropUnique('unique_domain_user_listing');

            // Drop new columns
            $table->dropColumn([
                'verification_data',
                'attempt_count',
                'last_attempt_at',
                'verified_at'
            ]);

            // Restore original unique constraint
            $table->unique('listing_id');
        });
    }
};
