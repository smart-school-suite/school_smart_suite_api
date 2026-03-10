<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('department_name');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::create('specialties', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('specialty_name');
            $table->decimal('registration_fee', 12, 2);
            $table->decimal('school_fee', 12, 2);
            $table->enum('status', ['active', 'inactive']);
            $table->text('description')->nullable();
            $table->enum('hall_assignment_status', ['unassigned', 'assigned'])->default('unassigned');
            $table->integer('num_assigned_hall')->default(0);
            $table->timestamps();
        });

        Schema::table('specialties', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('levels');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('specialties')) {
            Schema::table('specialties', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['department_id']);
                $table->dropForeign(['level_id']);
            });
        }

        if (Schema::hasTable('departments')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
            });
        }

        Schema::dropIfExists('specialties');
        Schema::dropIfExists('departments');
    }
};
