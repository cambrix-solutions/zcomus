<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Spec ref: §19.13 `notifications`
     *
     * HEADS UP: Laravel's own notification system (the `Notifiable`
     * trait + `php artisan notifications:table`) also wants a table
     * called `notifications`, but with a different shape (uuid id,
     * type, notifiable_type/id, data json). This migration uses the
     * spec's schema instead (title/body/tone/icon columns), which is
     * incompatible with Laravel's built-in one. If you ever also want
     * Laravel's database notification channel, give that one a
     * different table name (e.g. `system_notifications`) rather than
     * running both against `notifications`.
     *
     * ASSUMPTION: `tone` enum had no value list in the source doc —
     * using info/success/warning/danger as a standard toast/badge set.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->enum('tone', ['info', 'success', 'warning', 'danger'])->nullable();
            $table->string('icon')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
