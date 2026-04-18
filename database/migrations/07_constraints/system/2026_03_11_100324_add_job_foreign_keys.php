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
        Schema::table('system_job_events', function (Blueprint $table) {
            $table->string('system_job_id');
            $table->foreign('system_job_id')->references('id')->on('system_jobs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_job_events', function (Blueprint $table) {
            $table->dropForeign(['system_job_id']);
            $table->dropColumn('system_job_id');
        });
    }
};
