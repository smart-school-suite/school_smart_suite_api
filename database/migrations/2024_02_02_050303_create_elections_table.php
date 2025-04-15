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
        Schema::create('elections', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('election_type_id');
            $table->foreign('election_type_id')->references('id')->on('election_type');
            $table->dateTime('application_start')->nullable();
            $table->dateTime('application_end')->nullable();
            $table->dateTime('voting_start')->nullable();
            $table->dateTime('voting_end')->nullable();
            $table->enum('voting_status', ['ongoing', 'ended', 'pending'])->default('pending');
            $table->enum('application_status', ['ongoing', 'ended', 'pending'])->default('pending');
            $table->string('school_year')->nullable();
            $table->enum('status', ['pending', 'ongoing', 'finished'])->default('pending');
            $table->boolean('is_results_published')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elections');
    }
};
