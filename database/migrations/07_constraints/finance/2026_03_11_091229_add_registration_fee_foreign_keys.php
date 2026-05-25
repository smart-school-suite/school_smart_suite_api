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
        Schema::table('registration_fees', function (Blueprint $table) {
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('students');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('levels');
        });

        Schema::table('registration_fee_transactions', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('registrationfee_id');
            $table->foreign('registrationfee_id')->references('id')->on('registration_fees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('registration_fee_transactions')) {
            Schema::table('registration_fee_transactions', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['registrationfee_id']);
            });
        }

        if (Schema::hasTable('registration_fees')) {
            Schema::table('registration_fees', function (Blueprint $table) {
                $table->dropForeign(['student_id']);
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['level_id']);
            });
        }
    }
};
