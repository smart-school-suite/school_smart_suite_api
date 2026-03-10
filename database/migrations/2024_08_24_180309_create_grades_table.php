<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_scales', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->decimal('grade_points', 5, 2);
            $table->enum('grade_status', ['passed', 'failed'])->default('failed');
            $table->enum('resit_status', ['resit', 'no_resit', 'high_resit_potential', 'low_resit_potential']);
            $table->decimal('minimum_score', 5, 2);
            $table->decimal('maximum_score', 5, 2);
            $table->string('determinant');
            $table->timestamps();
        });

        Schema::create('letter_grades', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('letter_grade')->unique();
            $table->timestamps();
        });

        Schema::create('school_grade_scale_categories', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->boolean('isgrades_configured')->default(false);
            $table->decimal('max_score', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('grade_scale_categories', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->enum('status', ['active', 'inactive']);
            $table->enum('exam_type', ['exam', 'resit', 'ca']);
            $table->timestamps();
        });

        Schema::table('grade_scales', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('letter_grade_id');
            $table->foreign('letter_grade_id')->references('id')->on('letter_grades');
            $table->string('grades_category_id');
            $table->foreign('grades_category_id')->references('id')->on('grade_scale_categories');
        });

        Schema::table('school_grade_scale_categories', function (Blueprint $table) {
            $table->string('grades_category_id');
            $table->foreign('grades_category_id')->references('id')->on('grade_scale_categories');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('grade_scales')) {
            Schema::table('grade_scales', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['letter_grade_id']);
                $table->dropForeign(['grades_category_id']);
            });
        }

        if (Schema::hasTable('school_grade_scale_categories')) {
            Schema::table('school_grade_scale_categories', function (Blueprint $table) {
                $table->dropForeign(['grades_category_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        Schema::dropIfExists('grade_scales');
        Schema::dropIfExists('school_grade_scale_categories');
        Schema::dropIfExists('grade_scale_categories');
        Schema::dropIfExists('letter_grades');
    }
};
