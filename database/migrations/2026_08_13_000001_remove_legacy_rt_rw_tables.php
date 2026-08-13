<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cleanup seluruh tabel legacy modul RT/RW yang sudah dihapus dari aplikasi.
     *
     * Tabel yang di-drop (urutan leaf -> root sesuai dependency FK):
     *   1. bill_payments
     *   2. community_expenses
     *   3. community_cash_ledgers
     *   4. bills
     *   5. community_contributions
     *   6. fund_categories
     *   7. kks
     *   8. rts
     *
     * Sebelum drop `rts`, FK `users_rt_id_foreign` dilepas dan kolom
     * `users.rt_id` di-drop (tabel users tetap, hanya rt_id yang dibersihkan).
     *
     * Data/tabel Bank Sampah tidak disentuh.
     */
    public function up(): void
    {
        // 1. bill_payments
        Schema::dropIfExists('bill_payments');

        // 2. community_expenses
        Schema::dropIfExists('community_expenses');

        // 3. community_cash_ledgers
        Schema::dropIfExists('community_cash_ledgers');

        // 4. bills
        Schema::dropIfExists('bills');

        // 5. community_contributions
        Schema::dropIfExists('community_contributions');

        // 6. fund_categories
        Schema::dropIfExists('fund_categories');

        // 7. kks
        Schema::dropIfExists('kks');

        // 8. users: lepas FK rt_id lalu drop kolom rt_id (sebelum rts di-drop)
        if (Schema::hasColumn('users', 'rt_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['rt_id']);
                $table->dropColumn('rt_id');
            });
        }

        // 9. rts
        Schema::dropIfExists('rts');
    }

    /**
     * Reverse: re-create tabel legacy RT/RW dan kolom users.rt_id.
     */
    public function down(): void
    {
        // Re-create rts terlebih dahulu (akar dependency)
        Schema::create('rts', function (Blueprint $table) {
            $table->id();
            $table->string('rt_number', 10)->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Re-add users.rt_id + FK
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('rt_id')->nullable()->after('id')->constrained('rts')->nullOnDelete();
        });

        // kks
        Schema::create('kks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_id')->constrained('rts');
            $table->string('kk_number', 20)->unique()->nullable();
            $table->string('family_head', 100);
            $table->string('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();
        });

        // fund_categories
        Schema::create('fund_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_id')->nullable()->constrained('rts')->nullOnDelete();
            $table->string('name', 100);
            $table->string('description')->nullable();
            $table->decimal('target_amount', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_mandatory')->default(false);
            $table->decimal('monthly_amount', 15, 2)->default(0);
            $table->timestamps();
        });

        // community_contributions
        Schema::create('community_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fund_category_id')->constrained('fund_categories');
            $table->foreignId('rt_id')->nullable()->constrained('rts')->nullOnDelete();
            $table->string('member_name', 100)->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->string('description')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
        });

        // bills
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kk_id')->constrained('kks');
            $table->foreignId('fund_category_id')->constrained('fund_categories');
            $table->string('bill_code', 50)->unique()->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('due_date')->nullable();
            $table->tinyInteger('month');
            $table->integer('year');
            $table->string('status', 30)->default('unpaid');
            $table->timestamps();
        });

        // community_cash_ledgers
        Schema::create('community_cash_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fund_category_id')->constrained('fund_categories');
            $table->enum('type', ['in', 'out']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance', 15, 2);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->date('date');
            $table->string('description');
            $table->timestamps();
        });

        // community_expenses
        Schema::create('community_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fund_category_id')->constrained('fund_categories');
            $table->foreignId('rt_id')->nullable()->constrained('rts')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->string('description');
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
        });

        // bill_payments
        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills');
            $table->foreignId('community_contribution_id')->nullable()->constrained('community_contributions')->nullOnDelete();
            $table->string('receipt_number', 50)->unique()->nullable();
            $table->decimal('amount_paid', 15, 2);
            $table->string('payment_method', 50)->default('cash');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }
};
