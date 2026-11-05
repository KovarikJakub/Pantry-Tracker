<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Deleted products stay in the table so food items (and consumption history) keep pointing at them.
     */
    public function up(): void
    {
        Schema::table('product_catalogs', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_catalogs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
