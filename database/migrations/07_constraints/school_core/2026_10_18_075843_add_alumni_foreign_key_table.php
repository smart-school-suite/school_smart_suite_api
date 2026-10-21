<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumni', function (Blueprint $table) {
            $table->uuid('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('record_id');
            $table->foreign('record_id')->references('id')->on('graduation_records');
        });
    }

    public function down(): void
    {
        Schema::table('alumni', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['record_id']);

            $table->dropColumn([
                'school_branch_id',
                'record_id',
            ]);
        });
    }
};
