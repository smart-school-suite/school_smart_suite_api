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
        Schema::create('specialties', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('specialty_name');
            $table->decimal('registration_fee', 12, 2);
            $table->decimal('school_fee', 12, 2);
            $table->enum('status', ['active', 'inactive']);
            $table->text("description")->nullable();
            $table->enum('hall_assignment_status', ['unassigned','assigned'])->default('unassigned');
            $table->integer('num_assigned_hall')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specialties');
    }
};
