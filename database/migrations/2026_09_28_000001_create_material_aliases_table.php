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
        Schema::create('material_aliases', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 30)->default('BANK_SAMPAH'); // 'BANK_SAMPAH', 'AGGREGATOR'
            $table->foreignId('source_id')->nullable()->constrained('collectors')->nullOnDelete();
            $table->string('external_name');
            $table->string('normalized_name')->index();
            $table->foreignId('waste_category_id')->constrained('waste_categories')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['source_type', 'source_id', 'normalized_name'], 'unique_scoped_material_alias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_aliases');
    }
};
