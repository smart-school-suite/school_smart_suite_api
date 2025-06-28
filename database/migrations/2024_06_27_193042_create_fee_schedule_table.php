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
        Schema::create('installments', function(Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->unique();
            $table->string('program_name');
            $table->string('code');
            $table->integer('count');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('fee_schedules', function(Blueprint $table){
            $table->string('id')->primary();
            $table->enum('config_status', ['configured', 'not configured'])->default('not configured');
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->timestamps();
        });
        Schema::create('fee_schedule_slots', function(Blueprint $table){
            $table->string('id')->primary();
            $table->date('due_date');
            $table->decimal('fee_percentage', 5, 2);
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });

        Schema::create('student_fee_schedule', function(Blueprint $table){
           $table->string('id')->primary();
           $table->decimal('expected_amount', 15, 2);
           $table->enum('status', ['paid', 'unpaid'])->default('unpaid');
           $table->enum('gramification', ['late', 'on time', 'pending'])->default('pending');
           $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_schedule');
        Schema::dropIfExists('fee_schedule_slots');
        Schema::dropIfExists('student_fee_schedule');
        Schema::dropIfExists('installments');

    }
};
