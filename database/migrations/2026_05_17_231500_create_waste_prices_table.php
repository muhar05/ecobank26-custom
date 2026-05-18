<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waste_category_id')->constrained('waste_categories')->cascadeOnDelete();
            $table->foreignId('collector_id')->constrained('collectors')->cascadeOnDelete();
            $table->decimal('price_per_unit', 15, 2);
            $table->timestamps();

            $table->unique(['waste_category_id', 'collector_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_prices');
    }
};
