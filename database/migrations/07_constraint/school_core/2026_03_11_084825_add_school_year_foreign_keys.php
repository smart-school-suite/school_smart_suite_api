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
        Schema::table('school_academic_years', function (Blueprint $table) {
            $table->string('specialty_id');
            $table->foreign('specialty_id')
                ->references('id')
                ->on('specialties')
                ->onDelete('cascade');
            $table->string('system_academic_year_id');
            $table->foreign('system_academic_year_id')
                ->references('id')
                ->on('system_academic_years')
                ->onDelete('cascade');
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')
                ->references('id')
                ->on('school_branches')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('school_academic_years')) {
            Schema::table('school_academic_years', function (Blueprint $table) {
                $table->dropForeign(['system_academic_year_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }
    }
};
