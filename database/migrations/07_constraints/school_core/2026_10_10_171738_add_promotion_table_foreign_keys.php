<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotion_runs', function (Blueprint $table) {
            $table->string('school_year_id');
            $table->foreign('school_year_id')->references('id')->on('school_academic_years');

            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('student_promotion_records', function (Blueprint $table) {
            $table->foreignUuid('promotion_run_id')
                ->constrained('promotion_runs')
                ->cascadeOnDelete();

            $table->foreignUuid('student_id')
                ->constrained('students');

            $table->foreignUuid('from_specialty_id')->nullable()->constrained('specialties');
            $table->foreignUuid('to_specialty_id')->nullable()->constrained('specialties');

            $table->uuid('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });
    }

    public function down(): void
    {

        Schema::table('student_promotion_records', function (Blueprint $table) {
            $table->dropForeign(['promotion_run_id']);
            $table->dropForeign(['student_id']);
            $table->dropForeign(['from_specialty_id']);
            $table->dropForeign(['to_specialty_id']);
            $table->dropForeign(['school_branch_id']);

            $table->dropColumn([
                'promotion_run_id',
                'student_id',
                'from_specialty_id',
                'to_specialty_id',
                'school_branch_id',

            ]);
        });

        Schema::table('promotion_runs', function (Blueprint $table) {
            $table->dropForeign(['school_year_id']);
            $table->dropForeign(['school_branch_id']);

            $table->dropColumn([
                'school_year_id',
                'school_branch_id',
            ]);
        });
    }
};
