<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Spec ref: §19.10 `payments`, updated for §20.9's
     * POST /api/payments/initiate response, which needs `pay_url`
     * (ABA/Wing — link-based) and `qr_payload` (KHQR — scan-based).
     * Neither was in the original §19.12 table; added here since
     * you're still pre-launch (folded into the base migration rather
     * than a separate alter-table one, same pattern as earlier steps).
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->enum('provider', ['cod', 'aba', 'wing', 'khqr']);
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('provider_ref')->nullable(); // provider's transaction/reference id
            $table->string('pay_url')->nullable(); // ABA/Wing redirect link
            $table->text('qr_payload')->nullable(); // KHQR scan payload
            $table->timestamp('paid_at')->nullable();
            $table->json('raw_payload')->nullable(); // full webhook payload, for debugging/audits
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
