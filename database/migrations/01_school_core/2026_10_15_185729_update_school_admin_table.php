<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_admins', function (Blueprint $table) {
            $table->dropForeign(['gender_id']);
        });


        Schema::table('school_admins', function (Blueprint $table) {
            $table->foreignId('gender_id')
                ->nullable()
                ->change();
        });

        Schema::table('school_admins', function (Blueprint $table) {
            $table->foreign('gender_id')
                ->references('id')
                ->on('genders')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('school_admins', function (Blueprint $table) {
            $table->dropForeign(['gender_id']);
        });

        Schema::table('school_admins', function (Blueprint $table) {
            $table->foreignId('gender_id')
                ->nullable(false)
                ->change();
        });
        Schema::table('school_admins', function (Blueprint $table) {
            $table->foreign('gender_id')
                ->references('id')
                ->on('genders')
                ->cascadeOnDelete();
        });
    }
};
