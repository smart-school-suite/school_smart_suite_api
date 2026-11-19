<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * exams.grades_category_id and resit_exams.grades_category_id reference a
 * school's grade scale category (school_grade_scale_categories.id). Bulk
 * exam grading stored the system category id instead
 * (school_grade_scale_categories.grades_category_id), so those exams looked
 * graded but their grade scale could not be found ("Grade Scale Not Set").
 *
 * Each such reference is pointed at the same branch's school category for
 * that system category, or cleared when the branch has none. References that
 * are already valid are left alone, so this is safe to run on any database.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['exams', 'resit_exams'] as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'grades_category_id')) {
                continue;
            }

            $broken = DB::table($table)
                ->whereNotNull('grades_category_id')
                ->whereNotIn('grades_category_id', DB::table('school_grade_scale_categories')->select('id'))
                ->get(['id', 'school_branch_id', 'grades_category_id']);

            foreach ($broken as $row) {
                $schoolCategoryId = DB::table('school_grade_scale_categories')
                    ->where('school_branch_id', $row->school_branch_id)
                    ->where('grades_category_id', $row->grades_category_id)
                    ->value('id');

                DB::table($table)->where('id', $row->id)->update(['grades_category_id' => $schoolCategoryId]);
            }
        }
    }

    public function down(): void
    {
        // A data repair: the wrong references are not restored.
    }
};
