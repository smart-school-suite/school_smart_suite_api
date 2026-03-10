<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tuition_fees', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->decimal('amount_paid', 15, 2)->default(0.00);
            $table->decimal('amount_left', 15, 2)->default(0.00);
            $table->decimal('tution_fee_total', 15, 2);
            $table->enum('status', ['completed', 'owing', 'bad debt'])->default('owing');
            $table->timestamps();
        });

        Schema::create('tuition_fee_transactions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('transaction_id');
            $table->decimal('amount', 8, 2);
            $table->enum('payment_method', ['cash', 'cheque', 'credit_card', 'debit_card', 'bank_transfer']);
            $table->timestamps();
        });

        Schema::create('fee_waivers', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description')->nullable();
            $table->enum('status', ['expired', 'active']);
            $table->timestamps();
        });

        Schema::table('tuition_fees', function (Blueprint $table) {
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('students');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('levels');
        });

        Schema::table('tuition_fee_transactions', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('tuition_id');
            $table->foreign('tuition_id')->references('id')->on('tuition_fees');
        });

        Schema::table('fee_waivers', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('levels');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('students');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('fee_waivers')) {
            Schema::table('fee_waivers', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['level_id']);
                $table->dropForeign(['student_id']);
            });
        }

        if (Schema::hasTable('tuition_fee_transactions')) {
            Schema::table('tuition_fee_transactions', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['tuition_id']);
            });
        }

        if (Schema::hasTable('tuition_fees')) {
            Schema::table('tuition_fees', function (Blueprint $table) {
                $table->dropForeign(['student_id']);
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['level_id']);
            });
        }

        Schema::dropIfExists('fee_waivers');
        Schema::dropIfExists('tuition_fee_transactions');
        Schema::dropIfExists('tuition_fees');
    }
};
