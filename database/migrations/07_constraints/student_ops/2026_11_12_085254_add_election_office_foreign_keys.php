<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('election_office_terms', function (Blueprint $table) {
            $table->foreignUuid('school_branch_id')
                ->constrained('school_branches');
            $table->foreignUuid('election_role_id')
                ->constrained('election_roles')
                ->cascadeOnDelete();
            $table->unique(['school_branch_id', 'election_role_id'], 'election_office_terms_branch_role_unique');
        });

        Schema::table('election_office_holders', function (Blueprint $table) {
            $table->foreignUuid('school_branch_id')
                ->constrained('school_branches');
            $table->foreignUuid('election_id')
                ->nullable()
                ->constrained('elections')
                ->nullOnDelete();
            $table->foreignUuid('election_role_id')
                ->constrained('election_roles');
            $table->foreignUuid('election_candidate_id')
                ->nullable()
                ->constrained('election_candidates')
                ->nullOnDelete();
            $table->foreignUuid('start_school_academic_year_id')
                ->constrained('school_academic_years');
            $table->foreignUuid('end_school_academic_year_id')
                ->constrained('school_academic_years');
            $table->foreignUuid('replaces_holder_id')
                ->nullable()
                ->constrained('election_office_holders')
                ->nullOnDelete();
        });

        Schema::table('election_office_years', function (Blueprint $table) {
            $table->foreignUuid('school_branch_id')
                ->constrained('school_branches');
            $table->foreignUuid('election_office_holder_id')
                ->constrained('election_office_holders')
                ->cascadeOnDelete();
            $table->foreignUuid('school_academic_year_id')
                ->constrained('school_academic_years');
            $table->foreignUuid('system_academic_year_id')
                ->constrained('system_academic_years');
            $table->unique(
                ['election_office_holder_id', 'school_academic_year_id'],
                'election_office_years_holder_year_unique'
            );
            $table->unique(
                ['election_office_holder_id', 'system_academic_year_id'],
                'election_office_years_holder_system_year_unique'
            );
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('election_office_years')) {
            Schema::table('election_office_years', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['election_office_holder_id']);
                $table->dropForeign(['school_academic_year_id']);
                $table->dropForeign(['system_academic_year_id']);
                $table->dropUnique('election_office_years_holder_year_unique');
                $table->dropUnique('election_office_years_holder_system_year_unique');
                $table->dropColumn([
                    'school_branch_id',
                    'election_office_holder_id',
                    'school_academic_year_id',
                    'system_academic_year_id',
                ]);
            });
        }

        if (Schema::hasTable('election_office_holders')) {
            Schema::table('election_office_holders', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['election_id']);
                $table->dropForeign(['election_role_id']);
                $table->dropForeign(['election_candidate_id']);
                $table->dropForeign(['start_school_academic_year_id']);
                $table->dropForeign(['end_school_academic_year_id']);
                $table->dropForeign(['replaces_holder_id']);
                $table->dropColumn([
                    'school_branch_id',
                    'election_id',
                    'election_role_id',
                    'election_candidate_id',
                    'start_school_academic_year_id',
                    'end_school_academic_year_id',
                    'replaces_holder_id',
                ]);
            });
        }

        if (Schema::hasTable('election_office_terms')) {
            Schema::table('election_office_terms', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['election_role_id']);
                $table->dropUnique('election_office_terms_branch_role_unique');
                $table->dropColumn(['school_branch_id', 'election_role_id']);
            });
        }
    }
};
