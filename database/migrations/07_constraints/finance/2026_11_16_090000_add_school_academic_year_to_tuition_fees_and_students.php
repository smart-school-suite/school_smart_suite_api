<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ties tuition fee records and students to a school academic year.
 *
 * - tuition_fees.school_academic_year_id: the academic year a tuition fee
 *   record bills. The unique index on (student_id, school_academic_year_id)
 *   is the database-level guarantee that a student is billed at most once
 *   per academic year. Records created before this change keep a null year
 *   (NULLs never collide in a unique index).
 * - students.school_academic_year_id: the student's current school
 *   academic year, assigned automatically on registration, promotion and
 *   academic year creation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tuition_fees', function (Blueprint $table) {
            $table->string('school_academic_year_id')->nullable()->after('specialty_id');
            $table->foreign('school_academic_year_id')
                ->references('id')
                ->on('school_academic_years')
                ->cascadeOnDelete();
            $table->unique(['student_id', 'school_academic_year_id'], 'tuition_fees_student_academic_year_unique');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('school_academic_year_id')->nullable()->after('specialty_id');
            $table->foreign('school_academic_year_id')
                ->references('id')
                ->on('school_academic_years')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['school_academic_year_id']);
            $table->dropColumn('school_academic_year_id');
        });

        Schema::table('tuition_fees', function (Blueprint $table) {
            $table->dropForeign(['school_academic_year_id']);
            $table->dropUnique('tuition_fees_student_academic_year_unique');
            $table->dropColumn('school_academic_year_id');
        });
    }
};
