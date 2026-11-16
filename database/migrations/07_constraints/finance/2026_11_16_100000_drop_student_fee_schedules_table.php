<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Student fee schedules are no longer stored. A student's progress on each
 * fee schedule installment is derived from their tuition fee record, its
 * transactions and the fee schedule's installments (fee_sched_installments).
 *
 * - fee_sched_installments is created where it is missing (databases migrated
 *   before it replaced fee_schedule_slots).
 * - Installments still kept in the old fee_schedule_slots table are copied
 *   into fee_sched_installments (same ids, fee_percentage -> percentage)
 *   before that table is dropped, so configured schedules are kept.
 * - student_fee_schedules is dropped.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fee_sched_installments')) {
            Schema::create('fee_sched_installments', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->date('due_date');
                $table->decimal('percentage', 5, 2);
                $table->string('installment_id')->index();
                $table->foreign('installment_id')->references('id')->on('installments');
                $table->string('fee_schedule_id')->index();
                $table->foreign('fee_schedule_id')->references('id')->on('fee_schedules');
                $table->string('school_branch_id')->index();
                $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('fee_schedule_slots')) {
            $existing = DB::table('fee_sched_installments')->pluck('id')->flip();

            DB::table('fee_schedule_slots')->orderBy('id')->each(function ($slot) use ($existing) {
                if (isset($existing[$slot->id]) || !$slot->fee_schedule_id || !$slot->installment_id) {
                    return;
                }

                DB::table('fee_sched_installments')->insert([
                    'id' => $slot->id,
                    'due_date' => $slot->due_date,
                    'percentage' => $slot->fee_percentage,
                    'installment_id' => $slot->installment_id,
                    'fee_schedule_id' => $slot->fee_schedule_id,
                    'school_branch_id' => $slot->school_branch_id,
                    'created_at' => $slot->created_at,
                    'updated_at' => $slot->updated_at,
                ]);
            });
        }

        // student_fee_schedules references fee_schedule_slots, so it goes first.
        Schema::dropIfExists('student_fee_schedules');
        Schema::dropIfExists('fee_schedule_slots');
    }

    public function down(): void
    {
        // Not restored: student fee schedules are derived, and the installments
        // live on in fee_sched_installments.
    }
};
