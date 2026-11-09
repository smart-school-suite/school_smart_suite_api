<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A school semester is tied to a school year, not to a student batch, and
 * nothing fills student_batch_id any more. Left as NOT NULL it makes every
 * new school semester insert fail.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('school_semesters') || !Schema::hasColumn('school_semesters', 'student_batch_id')) {
            return;
        }

        Schema::table('school_semesters', function (Blueprint $table) {
            $table->string('student_batch_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('school_semesters') || !Schema::hasColumn('school_semesters', 'student_batch_id')) {
            return;
        }

        Schema::table('school_semesters', function (Blueprint $table) {
            $table->string('student_batch_id')->nullable(false)->change();
        });
    }
};
