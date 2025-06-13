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
        Schema::table('school_exam_stats', function (Blueprint $table) {
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
             $table->string('stat_type_id')->nullable()->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
