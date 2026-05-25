<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('school_expenses', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('expenses_category_id');
            $table->foreign('expenses_category_id')->references('id')->on('expense_categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('school_expenses')) {
            Schema::table('school_expenses', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['expenses_category_id']);
            });
        }
    }
};
