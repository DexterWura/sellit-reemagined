<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domain_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('domain');
            $table->enum('verification_method', ['txt_file', 'dns_record']);
            $table->string('verification_token', 100);
            $table->string('txt_filename', 100)->nullable()->comment('For file upload method');
            $table->string('dns_record_name', 100)->nullable()->comment('For DNS method');
            $table->string('dns_record_value', 255)->nullable()->comment('For DNS method');
            $table->tinyInteger('status')->unsigned()->default(0)->comment('0:pending, 1:verified, 2:failed');
            $table->integer('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique('listing_id');
            $table->index('domain');
            $table->index('verification_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_verifications');
    }
};

