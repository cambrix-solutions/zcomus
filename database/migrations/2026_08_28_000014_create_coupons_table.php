<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Spec ref: §19.12 `coupons`
     *
     * ASSUMPTION: `type` enum had no value list in the source doc —
     * using percent/fixed, the two common discount shapes, matching
     * `value` being either a percentage or a flat USD amount.
     *
     * ADDED: `rule` (nullable string) isn't in the §19.12 DB table, but
     * the §20.15 GET /api/vouchers response requires a `rule` field
     * (e.g. "Min. spend $20") that isn't reconstructable from the other
     * columns alone — min_order alone can't produce arbitrary rule text
     * like "First order only". Storing it directly avoids inventing
     * that copy in the controller.
     */
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->enum('type', ['percent', 'fixed']);
            $table->decimal('value', 10, 2);
            $table->decimal('min_order', 10, 2)->nullable();
            $table->string('rule')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
