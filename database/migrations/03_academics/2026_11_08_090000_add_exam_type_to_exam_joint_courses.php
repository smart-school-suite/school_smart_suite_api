<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A joint exam belongs to one exam type: the CA and the final exam of a
     * semester share a course but are separate exams with separate times.
     */
    public function up(): void
    {
        Schema::table('exam_jcs', function (Blueprint $table) {
            $table->string('exam_type_id')->nullable()->after('semester_id');
            $table->foreign('exam_type_id')->references('id')->on('exam_types');
            $table->index(['school_branch_id', 'school_year_id', 'semester_id', 'exam_type_id'], 'exam_jcs_term_index');
        });
    }

    public function down(): void
    {
        Schema::table('exam_jcs', function (Blueprint $table) {
            $table->dropIndex('exam_jcs_term_index');
            $table->dropForeign(['exam_type_id']);
            $table->dropColumn('exam_type_id');
        });
    }
};
