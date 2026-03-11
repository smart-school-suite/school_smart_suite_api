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
        Schema::table('semester_timetable_constraints', function (Blueprint $table) {
            $table->string('constraint_type_id');
            $table->foreign('constraint_type_id')->references('id')->on('constraint_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('semester_timetable_constraints', function (Blueprint $table) {
            $table->dropForeign(['constraint_type_id']);
            $table->dropColumn('constraint_type_id');
        });
    }
};
