<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Spec ref: §19.4 `products`
     *
     * ASSUMPTION: `status` enum truncated to "listed" in the source
     * doc. Using draft/listed/out_of_stock/archived as a reasonable
     * lifecycle — confirm against the vendor-center spec, since that's
     * likely where products actually get created/edited.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('brand');
            $table->decimal('price', 10, 2);
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->string('image');
            $table->json('images')->nullable();
            $table->text('description')->nullable();
            $table->string('short_description')->nullable();
            $table->string('sku')->nullable();
            $table->integer('stock')->default(0);
            $table->string('badge')->nullable();
            $table->string('warranty')->nullable();
            $table->json('specs')->nullable();
            $table->json('colors')->nullable();
            $table->json('styles')->nullable();
            $table->json('sizes')->nullable();
            $table->enum('status', ['draft', 'listed', 'out_of_stock', 'archived'])
                ->default('draft');
            $table->boolean('is_flash')->default(false);
            $table->boolean('is_trending')->default(false);
            $table->boolean('is_top_selling')->default(false);
            $table->timestamps();

            $table->index(['category_id', 'status']);
            $table->index(['shop_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
