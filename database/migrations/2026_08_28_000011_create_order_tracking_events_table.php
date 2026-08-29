<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Spec ref: §19.9 `order_tracking_events` / §20.11 GET /api/orders/{id}/tracking
    public function up(): void
    {
        Schema::create('order_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('status'); // matches an orders.status step, e.g. "shipped"
            $table->string('label');  // human text, e.g. "Out for delivery"
            $table->timestamp('happened_at');
            $table->timestamps();

            $table->index(['order_id', 'happened_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_tracking_events');
    }
};
