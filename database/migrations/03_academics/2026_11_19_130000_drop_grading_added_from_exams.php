<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops grading_added from exams and resit_exams. The create migration no
 * longer defines it, but databases built before that still have it, and code
 * that kept writing it failed on databases without it. Whether an exam is
 * graded is whether grades_category_id is set.
 *
 * Only drops the column where it exists, so it is safe on every database.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['exams', 'resit_exams'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'grading_added')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('grading_added');
                });
            }
        }
    }

    public function down(): void
    {
        // Not restored: no code reads the column, and fresh databases never had it.
    }
};
