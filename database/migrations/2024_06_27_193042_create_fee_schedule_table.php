<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installments', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->unique();
            $table->string('program_name');
            $table->string('code');
            $table->integer('count');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('fee_schedules', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->enum('config_status', ['configured', 'not configured'])->default('not configured');
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->timestamps();
        });

        Schema::create('fee_schedule_slots', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('due_date');
            $table->decimal('fee_percentage', 5, 2);
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });

        Schema::create('student_fee_schedules', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->decimal('expected_amount', 15, 2);
            $table->decimal('amount_paid', 8, 2)->default(0.0);
            $table->decimal('amount_left', 8, 2);
            $table->decimal('percentage_paid', 6, 2)->default(0.0);
            $table->enum('status', ['completed', 'unpaid', 'inprogress'])->default('unpaid');
            $table->enum('gramification', ['late', 'paypunctual', 'pending'])->default('pending');
            $table->timestamps();
        });

        Schema::table('fee_schedules', function (Blueprint $table) {
            $table->string('specialty_id')->index();
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->string('level_id')->index();
            $table->foreign('level_id')->references('id')->on('levels');
            $table->string('school_semester_id')->index();
            $table->foreign('school_semester_id')->references('id')->on('school_semesters');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('fee_schedule_slots', function (Blueprint $table) {
            $table->string('installment_id')->index();
            $table->foreign('installment_id')->references('id')->on('installments');
            $table->string('fee_schedule_id')->index();
            $table->foreign('fee_schedule_id')->references('id')->on('fee_schedules');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('student_fee_schedules', function (Blueprint $table) {
            $table->string('tuition_fee_id')->index();
            $table->foreign('tuition_fee_id')->references('id')->on('tuition_fees');
            $table->string('student_id')->index();
            $table->foreign('student_id')->references('id')->on('students');
            $table->string('level_id')->index();
            $table->foreign('level_id')->references('id')->on('levels');
            $table->string('specialty_id')->index();
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->string('fee_schedule_slot_id')->index();
            $table->foreign('fee_schedule_slot_id')->references('id')->on('fee_schedule_slots')->onDelete('cascade');
            $table->string('fee_schedule_id')->index();
            $table->foreign('fee_schedule_id')->references('id')->on('fee_schedules');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('student_fee_schedules')) {
            Schema::table('student_fee_schedules', function (Blueprint $table) {
                $table->dropForeign(['tuition_fee_id']);
                $table->dropForeign(['student_id']);
                $table->dropForeign(['level_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['fee_schedule_slot_id']);
                $table->dropForeign(['fee_schedule_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('fee_schedule_slots')) {
            Schema::table('fee_schedule_slots', function (Blueprint $table) {
                $table->dropForeign(['installment_id']);
                $table->dropForeign(['fee_schedule_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('fee_schedules')) {
            Schema::table('fee_schedules', function (Blueprint $table) {
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['level_id']);
                $table->dropForeign(['school_semester_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        Schema::dropIfExists('student_fee_schedules');
        Schema::dropIfExists('fee_schedule_slots');
        Schema::dropIfExists('fee_schedules');
        Schema::dropIfExists('installments');
    }
};
