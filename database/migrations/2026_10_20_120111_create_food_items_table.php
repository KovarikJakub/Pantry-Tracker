<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A concrete instance of a catalogue product stored on a shelf. Status is stored, consumed or discarded;
     * removed_at is set when the item leaves the shelf.
     */
    public function up(): void
    {
        Schema::create('food_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shelf_id')->constrained('shelves')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('product_catalogs')->restrictOnDelete();
            $table->foreignUuid('registered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('weight_volume', 10, 2);
            $table->decimal('purchase_price', 10, 2);
            $table->date('expiration_date');
            $table->string('status')->default('stored');
            $table->dateTime('added_at');
            $table->dateTime('removed_at')->nullable();
            $table->timestamps();

            $table->index(['shelf_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_items');
    }
};
