<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Spec ref: §19.8 `orders`
     *
     * ASSUMPTION: `payment_status` enum was truncated to "pending" only
     * in the source doc. Using pending/paid/failed/refunded — matches
     * the payments.status set below and the poll endpoint (§6, GET
     * /api/payments/{id}/status). Confirm against the real payment
     * provider docs once you integrate ABA/Wing/KHQR.
     *
     * Shipping fields are a snapshot (copied from the chosen address at
     * checkout time) so a later address edit/delete never changes what
     * an already-placed order shows — that's why address_id is nullable
     * with SET NULL on delete instead of a hard FK requirement.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique(); // e.g. ZC-1001

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained('shops')->restrictOnDelete();
            $table->foreignId('address_id')->nullable()->constrained('addresses')->nullOnDelete();

            // Shipping snapshot — copied from the address at checkout time.
            $table->string('shipping_name');
            $table->string('shipping_phone');
            $table->string('shipping_line1');
            $table->string('shipping_city');

            $table->enum('status', ['placed', 'paid', 'packed', 'shipped', 'delivered', 'cancelled'])
                ->default('placed');

            $table->enum('payment_method', ['cod', 'aba', 'wing', 'khqr']);
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');

            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping_fee', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);

            $table->string('coupon_code')->nullable();
            $table->text('notes')->nullable();

            $table->string('tracking_code')->nullable();
            $table->string('carrier')->nullable();

            $table->timestamp('placed_at');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('packed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['shop_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
