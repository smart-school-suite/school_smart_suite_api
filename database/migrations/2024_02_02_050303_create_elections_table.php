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
            $table->dateTime('application_start')->nullable();
            $table->dateTime('application_end')->nullable();
            $table->dateTime('voting_start')->nullable();
            $table->dateTime('voting_end')->nullable();
            $table->enum('voting_status', ['ongoing', 'ended', 'pending'])->default('pending');
            $table->enum('application_status', ['ongoing', 'ended', 'pending'])->default('pending');
            $table->string('school_year')->nullable();
            $table->enum('status', ['upcoming', 'ongoing', 'finished'])->default('upcoming');
            $table->boolean('is_results_published')->default(false);
            $table->timestamps();
        });

        Schema::create('election_roles', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('election_applications', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->text('manifesto');
            $table->text('personal_vision');
            $table->text('commitment_statement');
            $table->enum('application_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });

        Schema::create('election_votes', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamp('voted_at');
            $table->string('votable_type');
            $table->string('votable_id');
            $table->timestamps();
        });

        Schema::create('elections_results', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('vote_count')->default(0);
            $table->enum('election_status', ['won', 'lost', 'pending'])->default('pending');
            $table->timestamps();
        });

        Schema::create('election_candidates', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->boolean('isActive')->default(true);
            $table->timestamps();
        });

        Schema::create('voter_status', function (Blueprint $table) {
            $table->string('id');
            $table->string('votable_id');
            $table->string('votable_type');
            $table->boolean('status')->default(false);
            $table->timestamps();
        });

        Schema::create('election_participants', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
        });
        Schema::create('election_types', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('election_title');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('description');
            $table->timestamps();
        });
        Schema::create('past_election_winners', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('total_votes');
            $table->timestamps();
        });
        Schema::create('current_election_winners', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('total_votes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elections');
        Schema::dropIfExists('election_roles');
        Schema::dropIfExists('election_applications');
        Schema::dropIfExists('election_votes');
        Schema::dropIfExists('elections_results');
        Schema::dropIfExists('election_candidates');
        Schema::dropIfExists('voter_status');
        Schema::dropIfExists('election_participants');
        Schema::dropIfExists('election_types');
        Schema::dropIfExists('past_election_winners');
        Schema::dropIfExists('current_election_winners');
    }
};
