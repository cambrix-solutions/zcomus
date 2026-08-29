<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Spec ref: §19.8 `order_items` / §20.11 "Order item columns"
     *
     * product_name / product_image / sku / unit_price are all snapshots
     * copied from the product at checkout time — same reasoning as the
     * shipping snapshot on `orders`: a vendor renaming or repricing a
     * product later must never rewrite history on a past order.
     * product_id is kept (restrict on delete) for "reorder" (§7 #41)
     * and reporting, but the receipt itself never depends on it.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();

            $table->string('product_name');
            $table->string('product_image');
            $table->string('sku')->nullable();

            $table->unsignedInteger('qty');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 10, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
