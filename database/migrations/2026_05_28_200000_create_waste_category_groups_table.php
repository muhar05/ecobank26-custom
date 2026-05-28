<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create waste_category_groups table
        Schema::create('waste_category_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Add waste_category_group_id to waste_categories
        Schema::table('waste_categories', function (Blueprint $table) {
            $table->foreignId('waste_category_group_id')
                ->nullable()
                ->after('category_group')
                ->constrained('waste_category_groups')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('waste_categories', function (Blueprint $table) {
            $table->dropForeign(['waste_category_group_id']);
            $table->dropColumn('waste_category_group_id');
        });

        Schema::dropIfExists('waste_category_groups');
    }
};
