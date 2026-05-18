<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_bank_cash_ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10); // 'in' or 'out'
            $table->decimal('amount', 15, 2);
            $table->decimal('balance', 15, 2);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->date('date');
            $table->string('description', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_bank_cash_ledgers');
    }
};
