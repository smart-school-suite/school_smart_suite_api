<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_expenses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('date');
            $table->decimal('amount', 8, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('school_expenses', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('expenses_category_id');
            $table->foreign('expenses_category_id')->references('id')->on('expense_categories');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('school_expenses')) {
            Schema::table('school_expenses', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['expenses_category_id']);
            });
        }

        Schema::dropIfExists('school_expenses');
        Schema::dropIfExists('expense_categories');
    }
};
