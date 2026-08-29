<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Team decision (post-spec): users can hold multiple roles, no
     * longer a single `users.role` enum. Only covers the three roles
     * the app's own middleware checks (customer/vendor/support) —
     * Admin stays a fully separate model/table/guard, unaffected by
     * this change.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
