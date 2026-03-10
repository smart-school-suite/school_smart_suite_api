<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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

        Schema::table('elections', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('election_type_id');
            $table->foreign('election_type_id')->references('id')->on('election_types');
        });

        Schema::table('election_roles', function (Blueprint $table) {
            $table->string('election_type_id');
            $table->foreign('election_type_id')->references('id')->on('election_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('election_applications', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('election_id');
            $table->foreign('election_id')->references('id')->on('elections');
            $table->string('election_role_id');
            $table->foreign('election_role_id')->references('id')->on('election_roles');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('students');
        });

        Schema::table('election_votes', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('election_id');
            $table->foreign('election_id')->references('id')->on('elections');
            $table->string('candidate_id');
            $table->foreign('candidate_id')->references('id')->on('election_candidates');
            $table->string('position_id');
            $table->foreign('position_id')->references('id')->on('election_roles');
        });

        Schema::table('elections_results', function (Blueprint $table) {
            $table->string('election_id');
            $table->foreign('election_id')->references('id')->on('elections');
            $table->string('position_id');
            $table->foreign('position_id')->references('id')->on('election_roles');
            $table->string('candidate_id');
            $table->foreign('candidate_id')->references('id')->on('election_candidates');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('election_candidates', function (Blueprint $table) {
            $table->string('application_id');
            $table->foreign('application_id')->references('id')->on('election_applications');
            $table->string('election_id');
            $table->foreign('election_id')->references('id')->on('elections');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('students');
            $table->string('election_role_id');
            $table->foreign('election_role_id')->references('id')->on('election_roles');
        });

        Schema::table('voter_status', function (Blueprint $table) {
            $table->string('election_id')->index();
            $table->foreign('election_id')->references('id')->on('elections');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('candidate_id');
            $table->foreign('candidate_id')->references('id')->on('election_candidates');
            $table->string('position_id');
            $table->foreign('position_id')->references('id')->on('election_roles');
        });

        Schema::table('election_participants', function (Blueprint $table) {
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('levels');
            $table->string('election_id');
            $table->foreign('election_id')->references('id')->on('elections');
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('election_types', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('past_election_winners', function (Blueprint $table) {
            $table->string('election_type_id');
            $table->foreign('election_type_id')->references('id')->on('election_types');
            $table->string('election_role_id');
            $table->foreign('election_role_id')->references('id')->on('election_roles');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('students');
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('election_id')->index();
            $table->foreign('election_id')->references('id')->on('elections');
        });

        Schema::table('current_election_winners', function (Blueprint $table) {
            $table->string('election_id')->index();
            $table->foreign('election_id')->references('id')->on('elections');
            $table->string('election_type_id');
            $table->foreign('election_type_id')->references('id')->on('election_types');
            $table->string('election_role_id');
            $table->foreign('election_role_id')->references('id')->on('election_roles');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('students');
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('current_election_winners')) {
            Schema::table('current_election_winners', function (Blueprint $table) {
                $table->dropForeign(['election_id']);
                $table->dropForeign(['election_type_id']);
                $table->dropForeign(['election_role_id']);
                $table->dropForeign(['student_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('past_election_winners')) {
            Schema::table('past_election_winners', function (Blueprint $table) {
                $table->dropForeign(['election_type_id']);
                $table->dropForeign(['election_role_id']);
                $table->dropForeign(['student_id']);
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['election_id']);
            });
        }

        if (Schema::hasTable('election_types')) {
            Schema::table('election_types', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('election_participants')) {
            Schema::table('election_participants', function (Blueprint $table) {
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['level_id']);
                $table->dropForeign(['election_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('voter_status')) {
            Schema::table('voter_status', function (Blueprint $table) {
                $table->dropForeign(['election_id']);
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['candidate_id']);
                $table->dropForeign(['position_id']);
            });
        }

        if (Schema::hasTable('election_candidates')) {
            Schema::table('election_candidates', function (Blueprint $table) {
                $table->dropForeign(['application_id']);
                $table->dropForeign(['election_id']);
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['student_id']);
                $table->dropForeign(['election_role_id']);
            });
        }

        if (Schema::hasTable('elections_results')) {
            Schema::table('elections_results', function (Blueprint $table) {
                $table->dropForeign(['election_id']);
                $table->dropForeign(['position_id']);
                $table->dropForeign(['candidate_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('election_votes')) {
            Schema::table('election_votes', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['election_id']);
                $table->dropForeign(['candidate_id']);
                $table->dropForeign(['position_id']);
            });
        }

        if (Schema::hasTable('election_applications')) {
            Schema::table('election_applications', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['election_id']);
                $table->dropForeign(['election_role_id']);
                $table->dropForeign(['student_id']);
            });
        }

        if (Schema::hasTable('election_roles')) {
            Schema::table('election_roles', function (Blueprint $table) {
                $table->dropForeign(['election_type_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('elections')) {
            Schema::table('elections', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['election_type_id']);
            });
        }

        Schema::dropIfExists('current_election_winners');
        Schema::dropIfExists('past_election_winners');
        Schema::dropIfExists('election_participants');
        Schema::dropIfExists('voter_status');
        Schema::dropIfExists('elections_results');
        Schema::dropIfExists('election_votes');
        Schema::dropIfExists('election_candidates');
        Schema::dropIfExists('election_applications');
        Schema::dropIfExists('election_roles');
        Schema::dropIfExists('elections');
        Schema::dropIfExists('election_types');
    }
};
