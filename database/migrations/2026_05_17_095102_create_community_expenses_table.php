<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fund_category_id')->constrained('fund_categories');
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->string('description', 255);
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_expenses');
    }
};
