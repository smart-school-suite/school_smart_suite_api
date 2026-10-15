<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_result_publications', function (Blueprint $table) {
            $table->uuid('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('exam_id')->nullable()->index();
            $table->foreign('exam_id')->references('id')->on('exams');
        });
    }

    public function down(): void
    {
        Schema::table('exam_candidates', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['exam_id']);
            $table->dropColumn(['school_branch_id', 'exam_id']);
        });
    }
};
