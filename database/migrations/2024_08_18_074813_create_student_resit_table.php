<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_resits', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('paid_status')->default('unpaid');
            $table->decimal('resit_fee', 8, 2)->default(3000.00);
            $table->unsignedInteger('attempt_number')->default(0);
            $table->unsignedInteger('iscarry_over')->default(false);
            $table->timestamps();
        });

        Schema::create('resit_fee_transactions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('transaction_id');
            $table->decimal('amount', 8, 2);
            $table->enum('payment_method', ['cash', 'cheque', 'credit_card', 'debit_card', 'bank_transfer']);
            $table->timestamps();
        });

        Schema::table('student_resits', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->string('course_id');
            $table->foreign('course_id')->references('id')->on('courses');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('levels');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('students');
            $table->string('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batches');
        });

        Schema::table('resit_fee_transactions', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('resitfee_id');
            $table->foreign('resitfee_id')->references('id')->on('student_resits');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('resit_fee_transactions')) {
            Schema::table('resit_fee_transactions', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['resitfee_id']);
            });
        }

        if (Schema::hasTable('student_resits')) {
            Schema::table('student_resits', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['course_id']);
                $table->dropForeign(['exam_id']);
                $table->dropForeign(['level_id']);
                $table->dropForeign(['student_id']);
                $table->dropForeign(['semester_id']);
                $table->dropForeign(['student_batch_id']);
            });
        }

        Schema::dropIfExists('resit_fee_transactions');
        Schema::dropIfExists('student_resits');
    }
};
