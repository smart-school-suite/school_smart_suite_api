<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The academic year a registration fee belongs to: the first academic year
     * the student is billed in that specialty (the same year their tuition
     * starts in). Tuition fees already record theirs.
     *
     * Existing records take, in order:
     *  - the academic year of the student's earliest tuition fee in the same
     *    specialty;
     *  - else the student's current academic year, if they are still in that
     *    specialty.
     * Anything else stays empty until the student's tuition is billed.
     * Only records without an academic year are touched, so it is safe to repeat.
     */
    public function up(): void
    {
        if (!Schema::hasTable('registration_fees')) {
            return;
        }

        if (!Schema::hasColumn('registration_fees', 'school_academic_year_id')) {
            Schema::table('registration_fees', function (Blueprint $table) {
                $table->string('school_academic_year_id')->nullable()->after('specialty_id')->index();
                $table->foreign('school_academic_year_id')->references('id')->on('school_academic_years')->nullOnDelete();
            });
        }

        DB::table('registration_fees')->whereNull('school_academic_year_id')->orderBy('id')->each(function ($fee) {
            $yearId = Schema::hasColumn('tuition_fees', 'school_academic_year_id')
                ? DB::table('tuition_fees')
                    ->join('school_academic_years', 'school_academic_years.id', '=', 'tuition_fees.school_academic_year_id')
                    ->where('tuition_fees.student_id', $fee->student_id)
                    ->where('tuition_fees.specialty_id', $fee->specialty_id)
                    ->orderBy('school_academic_years.start_date')
                    ->value('tuition_fees.school_academic_year_id')
                : null;

            if (!$yearId && Schema::hasColumn('students', 'school_academic_year_id')) {
                $yearId = DB::table('students')
                    ->where('id', $fee->student_id)
                    ->where('specialty_id', $fee->specialty_id)
                    ->value('school_academic_year_id');
            }

            if ($yearId) {
                DB::table('registration_fees')->where('id', $fee->id)->update(['school_academic_year_id' => $yearId]);
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('registration_fees') && Schema::hasColumn('registration_fees', 'school_academic_year_id')) {
            Schema::table('registration_fees', function (Blueprint $table) {
                $table->dropForeign(['school_academic_year_id']);
                $table->dropColumn('school_academic_year_id');
            });
        }
    }
};
