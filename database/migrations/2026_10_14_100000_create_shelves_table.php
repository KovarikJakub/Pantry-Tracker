<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shelves', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('appliance_id')->constrained('appliances')->cascadeOnDelete();
            $table->string('label');
            $table->unsignedInteger('shelf_order');
            $table->decimal('max_load_capacity_g', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shelves');
    }
};
