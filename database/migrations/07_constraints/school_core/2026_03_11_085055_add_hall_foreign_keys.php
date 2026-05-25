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
        Schema::table('halls', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('school_hall_types', function (Blueprint $table) {
            $table->string('hall_id');
            $table->foreign('hall_id')->references('id')->on('halls');
            $table->string('hall_type_id');
            $table->foreign('hall_type_id')->references('id')->on('hall_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('specialty_halls', function (Blueprint $table) {
            $table->string('hall_id');
            $table->foreign('hall_id')->references('id')->on('halls');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('levels');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('specialty_halls')) {
            Schema::table('specialty_halls', function (Blueprint $table) {
                $table->dropForeign(['hall_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['level_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('school_hall_types')) {
            Schema::table('school_hall_types', function (Blueprint $table) {
                $table->dropForeign(['hall_id']);
                $table->dropForeign(['hall_type_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('halls')) {
            Schema::table('halls', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
            });
        }
    }
};
