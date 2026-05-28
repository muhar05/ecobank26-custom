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
        Schema::create('waste_bank_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_code')->unique();
            $table->decimal('amount', 12, 2);
            $table->text('description');
            $table->date('expense_date');
            $table->foreignId('recorded_by')->constrained('users');
            $table->string('proof_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_bank_expenses');
    }
};
