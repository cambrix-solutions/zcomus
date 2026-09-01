<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Spec ref: §13, items 62–63 (marked Optional / "can stay
     * client-side localStorage in phase 1" — building the backend
     * version anyway, since a signed-in customer switching devices
     * loses localStorage history entirely otherwise).
     *
     * One row per (user, product) — re-viewing a product updates
     * `viewed_at` rather than creating a duplicate row, so "recently
     * viewed" always reflects the LATEST view, not a running log of
     * every view ever.
     */
    public function up(): void
    {
        Schema::create('recently_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamp('viewed_at');
            $table->timestamps();

            $table->unique(['user_id', 'product_id']);
            $table->index(['user_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recently_views');
    }
};
