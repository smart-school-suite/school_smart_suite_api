<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('DOB')->nullable();
            $table->string('phone')->unique()->nullable();
            $table->string('email');
            $table->string('password');
            $table->string('profile_picture')->nullable();
            $table->boolean('deactivate')->default(false);
            $table->date('last_login_at')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('dropout_status')->default(false);
            $table->enum('payment_format', ['one time', 'installmental'])->default('installmental');
            $table->enum('sub_status', ['subscribed', 'expired', 'renewed', 'pending'])->default('pending');
            $table->timestamps();
        });

        Schema::create('student_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('student_sources', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name', 150);
            $table->text('description');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('genders', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name', 150);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('parents', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('address');
            $table->string('phone');
            $table->string('preferred_contact_method')->default('All');
            $table->string('preferred_language')->nullable();
            $table->timestamps();
        });

        Schema::create('stu_par_relationships', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->unique();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::table('parents', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties')->onDelete('cascade');
            $table->string('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('levels');
            $table->string('guardian_id');
            $table->foreign('guardian_id')->references('id')->on('parents');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batches');
            $table->string('relationship_id');
            $table->foreign('relationship_id')->references('id')->on('stu_par_relationships');
            $table->string('student_source_id');
            $table->foreign('student_source_id')->references('id')->on('student_sources');
            $table->string('gender_id');
            $table->foreign('gender_id')->references('id')->on('genders');
        });

        Schema::table('student_batches', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['department_id']);
                $table->dropForeign(['level_id']);
                $table->dropForeign(['guardian_id']);
                $table->dropForeign(['student_batch_id']);
                $table->dropForeign(['relationship_id']);
                $table->dropForeign(['student_source_id']);
                $table->dropForeign(['gender_id']);
            });
        }

        if (Schema::hasTable('parents')) {
            Schema::table('parents', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('student_batches')) {
            Schema::table('student_batches', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
            });
        }

        Schema::dropIfExists('students');
        Schema::dropIfExists('student_batches');
        Schema::dropIfExists('parents');
        Schema::dropIfExists('stu_par_relationships');
        Schema::dropIfExists('student_sources');
        Schema::dropIfExists('genders');
    }
};
