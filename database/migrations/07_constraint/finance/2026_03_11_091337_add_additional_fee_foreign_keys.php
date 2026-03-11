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
        Schema::table('additional_fees', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('levels');
            $table->string('additionalfee_category_id');
            $table->foreign('additionalfee_category_id')->references('id')->on('additional_fee_categories');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('students');
        });

        Schema::table('additional_fee_categories', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('additional_fee_transactions', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('fee_id');
            $table->foreign('fee_id')->references('id')->on('additional_fees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('additional_fee_transactions')) {
            Schema::table('additional_fee_transactions', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['fee_id']);
            });
        }

        if (Schema::hasTable('additional_fees')) {
            Schema::table('additional_fees', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['level_id']);
                $table->dropForeign(['additionalfee_category_id']);
                $table->dropForeign(['student_id']);
            });
        }

        if (Schema::hasTable('additional_fee_categories')) {
            Schema::table('additional_fee_categories', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
            });
        }
    }
};
