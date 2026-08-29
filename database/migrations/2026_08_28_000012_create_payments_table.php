<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Spec ref: §19.10 `payments`
     *
     * ASSUMPTION: `provider` and `status` enums had no value list at all
     * in the source doc (not even a truncated one). `provider` mirrors
     * the payment_method codes from §6; `cod` is included so a delivery
     * collection can still be logged as a payment row for reconciliation,
     * even though it never hits POST /api/payments/initiate.
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
