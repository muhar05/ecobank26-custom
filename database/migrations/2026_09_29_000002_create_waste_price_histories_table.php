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
        Schema::create('waste_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waste_price_id')->nullable()->constrained('waste_prices')->nullOnDelete();
            $table->foreignId('collector_id')->constrained('collectors')->cascadeOnDelete();
            $table->foreignId('waste_category_id')->constrained('waste_categories')->cascadeOnDelete();
            $table->decimal('old_price', 15, 2)->nullable();
            $table->decimal('new_price', 15, 2);
            $table->timestamp('changed_at');
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('admin_device_id')->nullable()->constrained('admin_devices')->nullOnDelete();
            $table->string('source', 20)->default('MANUAL'); // 'MANUAL', 'IMPORT'
            $table->string('import_batch_id')->nullable();
            $table->timestamps();

            $table->index(['collector_id', 'waste_category_id']);
            $table->index('changed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_price_histories');
    }
};
