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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('category'); // coffee, tea, snack, main_course, dessert
            $table->string('image_url')->nullable();
            $table->boolean('is_available')->default(true);
            $table->integer('stock')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better query performance
            $table->index('category');
            $table->index('is_available');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
