<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Spec ref: ZCOMUS_CUSTOMER_API_SPEC §19.1 (users — extend for SPA).
     * Folded straight into the base table since the project hasn't
     * shipped yet (migrate:fresh --seed workflow) — no need for a
     * separate alter-table migration.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('google_id')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable(); // nullable since Google users won't have one
            $table->string('phone')->nullable();
            $table->enum('role', ['customer', 'vendor', 'support'])->default('customer');

            // Payment methods per spec §6.
            $table->enum('preferred_payment', ['cod', 'aba', 'wing', 'khqr'])->nullable();

            $table->boolean('alert_order')->default(true);
            $table->boolean('alert_deal')->default(true);
            $table->boolean('alert_sms')->default(false);

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
