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
            $table->string('title')->unique();
            $table->date('election_start_date');
            $table->date('election_end_date');
            $table->time('starting_time');
            $table->time('ending_time');
            $table->text('description');
            $table->integer("school_year_start");
            $table->integer('school_year_end');
            $table->enum('status', ['active', 'inactive', 'finished'])->default('inactive');
            $table->boolean('is_results_published');
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
