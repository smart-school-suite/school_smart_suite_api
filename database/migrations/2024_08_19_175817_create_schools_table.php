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
        Schema::create('schools', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->unique();
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->integer('semester')->default(2);
            $table->string('type'); // e.g., public, private
            $table->date('established_year')->nullable();
            $table->string('school_logo')->nullable();
            $table->string('director_name')->nullable();
            $table->text('motor');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
