<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Expense categories belong to a school branch and are active or
     * inactive: the service filters them by school_branch_id and the
     * update request takes a status, but the create migration never added
     * either column, so on a database built from the migrations every
     * category request failed with "no such column".
     *
     * Only missing columns are added, so a database that already has them
     * is left alone. A category without a branch takes the branch of the
     * expenses filed under it; one that no expense uses can't be traced to
     * a branch, stays without one and is logged.
     */
    public function up(): void
    {
        if (!Schema::hasTable('expense_categories')) {
            return;
        }

        if (!Schema::hasColumn('expense_categories', 'school_branch_id')) {
            Schema::table('expense_categories', function (Blueprint $table) {
                $table->string('school_branch_id')->nullable()->after('id')->index();
                $table->foreign('school_branch_id')->references('id')->on('school_branches');
            });
        }

        if (!Schema::hasColumn('expense_categories', 'status')) {
            Schema::table('expense_categories', function (Blueprint $table) {
                $table->string('status', 20)->default('active')->after('description');
            });
        }

        if (Schema::hasTable('school_expenses') && Schema::hasColumn('school_expenses', 'expenses_category_id')) {
            DB::table('expense_categories')->whereNull('school_branch_id')->orderBy('id')->each(function ($category) {
                $branchId = DB::table('school_expenses')
                    ->where('expenses_category_id', $category->id)
                    ->whereNotNull('school_branch_id')
                    ->value('school_branch_id');
                if ($branchId) {
                    DB::table('expense_categories')->where('id', $category->id)->update(['school_branch_id' => $branchId]);
                }
            });
        }

        $orphans = DB::table('expense_categories')->whereNull('school_branch_id')->count();
        if ($orphans > 0) {
            Log::warning("{$orphans} expense categories have no school branch and no expenses to take one from; no school sees them.");
        }
    }

    /** Nothing to undo: the service needs both columns. */
    public function down(): void
    {
    }
};
