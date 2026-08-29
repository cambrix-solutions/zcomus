<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Spec ref: §19.3 `shops` (public vendor storefront)
     *
     * ASSUMPTION: `theme` enum truncated to "classic" in the source
     * doc. Using a small placeholder set — swap in the real design
     * options whenever the frontend team confirms them.
     */
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('cover')->nullable();
            $table->string('industry')->nullable();
            $table->text('description')->nullable();
            $table->string('tagline')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('accent_color')->nullable();
            $table->enum('theme', ['classic', 'modern', 'minimal'])->default('classic');
            $table->string('announcement')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
