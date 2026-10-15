<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Set once the household has copied the default category tree into its own rows.
     */
    public function up(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->boolean('has_custom_categories')->default(false)->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->dropColumn('has_custom_categories');
        });
    }
};
