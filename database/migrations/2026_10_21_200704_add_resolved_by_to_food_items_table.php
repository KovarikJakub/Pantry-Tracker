<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The food item itself records its resolution: status says how it left, removed_at when,
     * and resolved_by_user_id who consumed or discarded it.
     */
    public function up(): void
    {
        Schema::table('food_items', function (Blueprint $table) {
            $table->foreignUuid('resolved_by_user_id')->nullable()->after('removed_at')->constrained('users')->nullOnDelete();

            $table->index(['resolved_by_user_id', 'status', 'removed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_items', function (Blueprint $table) {
            $table->dropIndex(['resolved_by_user_id', 'status', 'removed_at']);
            $table->dropConstrainedForeignId('resolved_by_user_id');
        });
    }
};
