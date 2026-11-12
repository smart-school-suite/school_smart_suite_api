<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('election_office_terms', function (Blueprint $table) {
            $table->foreignUuid('school_branch_id')
                ->constrained('school_branches');
            $table->foreignUuid('election_role_id')
                ->constrained('election_roles');
            $table->unique(['school_branch_id', 'election_role_id']);
        });
        Schema::table('election_office_holders', function (Blueprint $table) {
            $table->foreignUuid('school_branch_id')
                ->constrained('school_branches');
            $table->foreignUuid('election_id')
                ->nullable()
                ->constrained('elections');
            $table->foreignUuid('election_role_id')
                ->constrained('election_roles');
            $table->nullableMorphs('holderable');
            $table->foreignUuid('start_school_academic_year_id')
                ->constrained('school_academic_years');
            $table->foreignUuid('end_school_academic_year_id')
                ->constrained('school_academic_years');
            $table->foreignUuid('replaces_holder_id')
                ->nullable()
                ->constrained('election_office_holders');
        });
        Schema::table('election_office_years', function (Blueprint $table) {
            $table->foreignUuid('election_office_holder_id')
                ->constrained('election_office_holders')
                ->cascadeOnDelete();
            $table->foreignUuid('school_academic_year_id')
                ->constrained('school_academic_years');
            $table->unique([
                'election_office_holder_id',
                'school_academic_year_id',
            ]);
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('election_office_years')) {
            Schema::table('election_office_years', function (Blueprint $table) {
                $table->dropUnique(['election_office_holder_id', 'school_academic_year_id']);
                $table->dropForeign(['election_office_holder_id']);
                $table->dropForeign(['school_academic_year_id']);
                $table->dropColumn([
                    'election_office_holder_id',
                    'school_academic_year_id',
                ]);
            });
        }

        if (Schema::hasTable('election_office_holders')) {
            Schema::table('election_office_holders', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['election_id']);
                $table->dropForeign(['election_role_id']);
                $table->dropForeign(['start_school_academic_year_id']);
                $table->dropForeign(['end_school_academic_year_id']);
                $table->dropForeign(['replaces_holder_id']);

                $table->dropMorphs('holderable');

                $table->dropColumn([
                    'school_branch_id',
                    'election_id',
                    'election_role_id',
                    'start_school_academic_year_id',
                    'end_school_academic_year_id',
                    'replaces_holder_id',
                ]);
            });
        }

        Schema::dropIfExists('election_office_terms');
    }
};
