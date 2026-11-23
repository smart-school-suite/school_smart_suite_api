<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('school_logos', 'school') || Schema::hasColumn('school_logos', 'school_id')) {
            return;
        }

        Schema::table('school_logos', function (Blueprint $table) {
            $table->dropForeign(['school']);
        });

        Schema::table('school_logos', function (Blueprint $table) {
            $table->renameColumn('school', 'school_id');
        });

        Schema::table('school_logos', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('school_logos', 'school_id') || Schema::hasColumn('school_logos', 'school')) {
            return;
        }

        Schema::table('school_logos', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
        });

        Schema::table('school_logos', function (Blueprint $table) {
            $table->renameColumn('school_id', 'school');
        });

        Schema::table('school_logos', function (Blueprint $table) {
            $table->foreign('school')->references('id')->on('schools');
        });
    }
};
