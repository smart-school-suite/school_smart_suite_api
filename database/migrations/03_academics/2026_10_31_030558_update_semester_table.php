<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('school_semesters')) {
            return;
        }

        if (!Schema::hasColumn('school_semesters', 'specialty_id')) {
            return;
        }

        Schema::table('school_semesters', function (Blueprint $table) {
            $table->dropForeign(['specialty_id']);
            $table->dropColumn('specialty_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('school_semesters')) {
            return;
        }

        if (Schema::hasColumn('school_semesters', 'specialty_id')) {
            return;
        }

        Schema::table('school_semesters', function (Blueprint $table) {
            $table->unsignedBigInteger('specialty_id')->nullable()->after('id');
        });
    }
};
