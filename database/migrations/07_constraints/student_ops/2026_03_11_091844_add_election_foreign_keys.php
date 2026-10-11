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
        Schema::table('elections', function (Blueprint $table) {
            $table->uuid('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('election_type_id');
            $table->foreign('election_type_id')->references('id')->on('election_types');
            $table->uuid("academic_year_id");
            $table->foreign("academic_year_id")->references("id")->on("system_academic_years");
        });

        Schema::table('election_roles', function (Blueprint $table) {
            $table->uuid('election_type_id');
            $table->foreign('election_type_id')->references('id')->on('election_types');
            $table->uuid('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('election_applications', function (Blueprint $table) {
            $table->uuid('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('election_id');
            $table->foreign('election_id')->references('id')->on('elections');
            $table->uuid('election_role_id');
            $table->foreign('election_role_id')->references('id')->on('election_roles');
            $table->uuid('student_id');
            $table->foreign('student_id')->references('id')->on('students');
        });

        Schema::table('election_votes', function (Blueprint $table) {
            $table->uuid('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('election_id');
            $table->foreign('election_id')->references('id')->on('elections');
            $table->uuid('candidate_id');
            $table->foreign('candidate_id')->references('id')->on('election_candidates');
            $table->uuid('position_id');
            $table->foreign('position_id')->references('id')->on('election_roles');
        });

        Schema::table('elections_results', function (Blueprint $table) {
            $table->uuid('election_id');
            $table->foreign('election_id')->references('id')->on('elections');
            $table->uuid('position_id');
            $table->foreign('position_id')->references('id')->on('election_roles');
            $table->uuid('candidate_id');
            $table->foreign('candidate_id')->references('id')->on('election_candidates');
            $table->uuid('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('election_candidates', function (Blueprint $table) {
            $table->uuid('application_id');
            $table->foreign('application_id')->references('id')->on('election_applications');
            $table->uuid('election_id');
            $table->foreign('election_id')->references('id')->on('elections');
            $table->uuid('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('student_id');
            $table->foreign('student_id')->references('id')->on('students');
            $table->uuid('election_role_id');
            $table->foreign('election_role_id')->references('id')->on('election_roles');
        });

        Schema::table('voter_status', function (Blueprint $table) {
            $table->uuid('election_id')->index();
            $table->foreign('election_id')->references('id')->on('elections');
            $table->uuid('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('candidate_id');
            $table->foreign('candidate_id')->references('id')->on('election_candidates');
            $table->uuid('position_id');
            $table->foreign('position_id')->references('id')->on('election_roles');
        });

        Schema::table('election_participants', function (Blueprint $table) {
            $table->uuid('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->uuid('level_id');
            $table->foreign('level_id')->references('id')->on('levels');
            $table->uuid('election_id');
            $table->foreign('election_id')->references('id')->on('elections');
            $table->uuid('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('election_types', function (Blueprint $table) {
            $table->uuid('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('past_election_winners', function (Blueprint $table) {
            $table->uuid('election_type_id');
            $table->foreign('election_type_id')->references('id')->on('election_types');
            $table->uuid('election_role_id');
            $table->foreign('election_role_id')->references('id')->on('election_roles');
            $table->uuid('student_id');
            $table->foreign('student_id')->references('id')->on('students');
            $table->uuid('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('election_id')->index();
            $table->foreign('election_id')->references('id')->on('elections');
        });

        Schema::table('current_election_winners', function (Blueprint $table) {
            $table->uuid('election_id')->index();
            $table->foreign('election_id')->references('id')->on('elections');
            $table->uuid('election_type_id');
            $table->foreign('election_type_id')->references('id')->on('election_types');
            $table->uuid('election_role_id');
            $table->foreign('election_role_id')->references('id')->on('election_roles');
            $table->uuid('student_id');
            $table->foreign('student_id')->references('id')->on('students');
            $table->uuid('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });
    }

    /**
     * Reverse the migrations.
     */
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
    }
};
