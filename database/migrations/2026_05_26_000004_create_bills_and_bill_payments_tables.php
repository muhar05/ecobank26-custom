<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kk_id')->constrained('kks')->onDelete('cascade');
            $table->foreignId('fund_category_id')->constrained('fund_categories')->onDelete('cascade');
            $table->string('bill_code', 50)->nullable()->unique();
            $table->decimal('amount', 15, 2);
            $table->date('due_date')->nullable();
            $table->tinyInteger('month');
            $table->integer('year');
            $table->string('status', 30)->default('unpaid'); // unpaid, partially_paid, paid
            $table->timestamps();

            $table->unique(['kk_id', 'fund_category_id', 'month', 'year'], 'kk_bill_period_unique');
        });

        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->onDelete('cascade');
            $table->foreignId('community_contribution_id')
                ->nullable()
                ->constrained('community_contributions')
                ->onDelete('set null');
            $table->string('receipt_number', 50)->nullable()->unique();
            $table->decimal('amount_paid', 15, 2);
            $table->string('payment_method', 50)->default('cash');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_payments');
        Schema::dropIfExists('bills');
    }
};
