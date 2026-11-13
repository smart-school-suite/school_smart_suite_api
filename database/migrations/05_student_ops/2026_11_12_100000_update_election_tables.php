<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Election feature refactor (production-safe update).
 *
 * 1. Drops the redundant tables that were commented out of the original
 *    create / foreign-key migrations: elections_results, voter_status,
 *    past_election_winners and current_election_winners. Results are now
 *    derived from election_votes and winners live in election_office_holders.
 * 2. Reshapes election_participants into a polymorphic eligibility table
 *    (participantable + can_vote / can_apply) and moves the old
 *    specialty/level configuration into the new elections.participants JSON
 *    column (same pattern as announcements.audience).
 * 3. Makes applications and candidates polymorphic so students, teachers and
 *    school admins can all stand for election.
 * 4. Adds lifecycle / configuration columns to elections and seats to
 *    election_roles (positions), and a duplicate-vote guard on election_votes.
 *
 * Every step is guarded so the migration is safe on production databases
 * (where the legacy tables/columns exist), on fresh installs, and when
 * re-run after a partial failure.
 *
 * Morph columns are kept compact (ASCII, 100/64 chars): they only hold class
 * names and UUIDs, and full-size utf8mb4 varchar(255) pairs make the unique
 * keys exceed MySQL's index size limit (767/1000 bytes on some servers).
 */
return new class extends Migration
{
    private const STUDENT_MORPH = 'App\\Models\\Student';
    private const MORPH_TYPE_LENGTH = 100;
    private const MORPH_ID_LENGTH = 64;
    private const KEY_PREFIX_LENGTH = 64;

    private array $droppedTables = [
        'current_election_winners',
        'past_election_winners',
        'voter_status',
        'elections_results',
    ];

    public function up(): void
    {
        $this->dropRedundantTables();
        $this->updateElections();
        $this->updateElectionRoles();
        $this->updateElectionParticipants();
        $this->updateElectionApplications();
        $this->updateElectionCandidates();
        $this->updateElectionVotes();
    }

    // ------------------------------------------------------------------ up

    private function dropRedundantTables(): void
    {
        foreach ($this->droppedTables as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function updateElections(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            if (!Schema::hasColumn('elections', 'title')) {
                $table->string('title')->nullable()->after('id');
            }
            if (!Schema::hasColumn('elections', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('elections', 'status')) {
                $table->enum('status', [
                    'draft',
                    'upcoming',
                    'application_open',
                    'application_closed',
                    'voting_open',
                    'voting_closed',
                    'completed',
                    'cancelled',
                ])->default('draft')->after('description');
            }
            if (!Schema::hasColumn('elections', 'participants')) {
                $table->json('participants')->nullable();
            }
            if (!Schema::hasColumn('elections', 'max_applications_per_participant')) {
                $table->unsignedTinyInteger('max_applications_per_participant')->default(1);
            }
            if (!Schema::hasColumn('elections', 'allow_candidate_self_vote')) {
                $table->boolean('allow_candidate_self_vote')->default(true);
            }
            if (!Schema::hasColumn('elections', 'results_published_at')) {
                $table->timestamp('results_published_at')->nullable();
            }
        });

        // Back-fill a lifecycle status for elections that already exist.
        $now = Carbon::now();
        DB::table('elections')->orderBy('id')->each(function ($election) use ($now) {
            DB::table('elections')->where('id', $election->id)->update([
                'status' => $this->deriveStatus($election, $now),
                'results_published_at' => $election->is_results_published ? ($election->voting_end ?? $now) : null,
            ]);
        });
    }

    private function deriveStatus(object $election, Carbon $now): string
    {
        if ($election->is_results_published) {
            return 'completed';
        }
        $after = fn($value) => $value !== null && $now->greaterThan(Carbon::parse($value));
        $before = fn($value) => $value !== null && $now->lessThan(Carbon::parse($value));

        if ($after($election->voting_end)) {
            return 'voting_closed';
        }
        if ($after($election->voting_start)) {
            return 'voting_open';
        }
        if ($after($election->application_end)) {
            return 'application_closed';
        }
        if ($after($election->application_start)) {
            return 'application_open';
        }
        if ($before($election->application_start)) {
            return 'upcoming';
        }
        return 'draft';
    }

    private function updateElectionRoles(): void
    {
        if (!Schema::hasColumn('election_roles', 'seats')) {
            Schema::table('election_roles', function (Blueprint $table) {
                $table->unsignedTinyInteger('seats')->default(1)->after('description');
            });
        }
    }

    private function updateElectionParticipants(): void
    {
        // 1. Add the polymorphic participant columns.
        Schema::table('election_participants', function (Blueprint $table) {
            if (!Schema::hasColumn('election_participants', 'participantable_type')) {
                $this->addMorphColumns($table, 'participantable');
            }
            if (!Schema::hasColumn('election_participants', 'can_vote')) {
                $table->boolean('can_vote')->default(true);
            }
            if (!Schema::hasColumn('election_participants', 'can_apply')) {
                $table->boolean('can_apply')->default(false);
            }
        });

        $this->compactMorphColumns('election_participants', 'participantable', true);
        $this->ensureIndex('election_participants', ['participantable_type', 'participantable_id'], 'election_participants_participantable_index');

        // 2. Convert the legacy specialty/level rows into the JSON audience on
        //    elections and materialise one participant row per student.
        //    The legacy rows are captured first, then the legacy columns are
        //    dropped (they are NOT NULL foreign keys), then the rows are rebuilt.
        if (Schema::hasColumn('election_participants', 'specialty_id')) {
            $legacy = DB::table('election_participants')
                ->whereNull('participantable_id')
                ->get(['election_id', 'school_branch_id', 'specialty_id']);

            DB::table('election_participants')->whereNull('participantable_id')->delete();

            Schema::table('election_participants', function (Blueprint $table) {
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['level_id']);
            });
            Schema::table('election_participants', function (Blueprint $table) {
                $table->dropColumn(['specialty_id', 'level_id']);
            });

            $this->convertLegacyParticipants($legacy);
        }

        // 3. One eligibility row per participant per election.
        $this->ensureIndex(
            'election_participants',
            ['election_id', 'participantable_type', 'participantable_id'],
            'election_participants_unique_participant',
            true
        );
    }

    private function convertLegacyParticipants(\Illuminate\Support\Collection $legacy): void
    {
        $now = Carbon::now();

        foreach ($legacy->groupBy('election_id') as $electionId => $rows) {
            $specialtyIds = $rows->pluck('specialty_id')->filter()->unique()->values()->all();
            $schoolBranchId = $rows->first()->school_branch_id;
            $audience = ['student_audience' => [['specialty_ids' => $specialtyIds]]];

            DB::table('elections')->where('id', $electionId)->update([
                'participants' => json_encode([
                    'voters' => $audience,
                    'candidates' => $audience,
                ]),
            ]);

            DB::table('students')
                ->where('school_branch_id', $schoolBranchId)
                ->whereIn('specialty_id', $specialtyIds)
                ->orderBy('id')
                ->select('id')
                ->chunk(1000, function ($students) use ($electionId, $schoolBranchId, $now) {
                    DB::table('election_participants')->insert(
                        $students->map(fn($student) => [
                            'id' => (string) Str::uuid(),
                            'school_branch_id' => $schoolBranchId,
                            'election_id' => $electionId,
                            'participantable_type' => self::STUDENT_MORPH,
                            'participantable_id' => $student->id,
                            'can_vote' => true,
                            'can_apply' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ])->all()
                    );
                });
        }
    }

    private function updateElectionApplications(): void
    {
        Schema::table('election_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('election_applications', 'applicantable_type')) {
                $this->addMorphColumns($table, 'applicantable');
            }
            if (!Schema::hasColumn('election_applications', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable();
                $table->string('reviewer_type')->nullable();
                $table->string('reviewer_id')->nullable();
                $table->text('review_remarks')->nullable();
            }
        });

        $this->compactMorphColumns('election_applications', 'applicantable', true);
        $this->ensureIndex('election_applications', ['applicantable_type', 'applicantable_id'], 'election_applications_applicantable_index');

        Schema::table('election_applications', function (Blueprint $table) {
            $table->enum('application_status', ['pending', 'approved', 'rejected', 'withdrawn'])
                ->default('pending')
                ->change();
        });

        if (Schema::hasColumn('election_applications', 'student_id')) {
            DB::table('election_applications')
                ->whereNull('applicantable_id')
                ->update([
                    'applicantable_type' => self::STUDENT_MORPH,
                    'applicantable_id' => DB::raw('student_id'),
                ]);

            Schema::table('election_applications', function (Blueprint $table) {
                $table->dropForeign(['student_id']);
            });
            Schema::table('election_applications', function (Blueprint $table) {
                $table->dropColumn('student_id');
            });
        }

        $this->ensureIndex(
            'election_applications',
            ['election_id', 'election_role_id', 'applicantable_type', 'applicantable_id'],
            'election_applications_unique_applicant',
            true
        );
    }

    private function updateElectionCandidates(): void
    {
        Schema::table('election_candidates', function (Blueprint $table) {
            if (!Schema::hasColumn('election_candidates', 'candidateable_type')) {
                $this->addMorphColumns($table, 'candidateable');
            }
            if (!Schema::hasColumn('election_candidates', 'status')) {
                $table->enum('status', ['active', 'disqualified', 'withdrawn'])->default('active');
                $table->text('status_reason')->nullable();
            }
        });

        $this->compactMorphColumns('election_candidates', 'candidateable', true);
        $this->ensureIndex('election_candidates', ['candidateable_type', 'candidateable_id'], 'election_candidates_candidateable_index');

        if (Schema::hasColumn('election_candidates', 'isActive')) {
            DB::table('election_candidates')->where('isActive', false)->update(['status' => 'disqualified']);
            Schema::table('election_candidates', function (Blueprint $table) {
                $table->dropColumn('isActive');
            });
        }

        if (Schema::hasColumn('election_candidates', 'student_id')) {
            DB::table('election_candidates')
                ->whereNull('candidateable_id')
                ->update([
                    'candidateable_type' => self::STUDENT_MORPH,
                    'candidateable_id' => DB::raw('student_id'),
                ]);

            Schema::table('election_candidates', function (Blueprint $table) {
                $table->dropForeign(['student_id']);
            });
            Schema::table('election_candidates', function (Blueprint $table) {
                $table->dropColumn('student_id');
            });
        }

        $this->ensureIndex('election_candidates', ['application_id'], 'election_candidates_application_unique', true);
    }

    private function updateElectionVotes(): void
    {
        $this->compactMorphColumns('election_votes', 'votable', false);

        // Remove any duplicated ballots (same voter, same candidate, same position)
        // so the unique guard can be created; the earliest ballot is kept.
        $duplicates = DB::table('election_votes')
            ->select('election_id', 'position_id', 'candidate_id', 'votable_type', 'votable_id')
            ->groupBy('election_id', 'position_id', 'candidate_id', 'votable_type', 'votable_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $ids = DB::table('election_votes')
                ->where('election_id', $duplicate->election_id)
                ->where('position_id', $duplicate->position_id)
                ->where('candidate_id', $duplicate->candidate_id)
                ->where('votable_type', $duplicate->votable_type)
                ->where('votable_id', $duplicate->votable_id)
                ->orderBy('voted_at')
                ->pluck('id');

            DB::table('election_votes')->whereIn('id', $ids->slice(1)->all())->delete();
        }

        $this->ensureIndex(
            'election_votes',
            ['election_id', 'position_id', 'candidate_id', 'votable_type', 'votable_id'],
            'election_votes_unique_ballot',
            true
        );
        $this->ensureIndex('election_votes', ['election_id', 'votable_type', 'votable_id'], 'election_votes_voter_index');

        // Office holders are created by an earlier migration; keep their morph compact too.
        if (Schema::hasTable('election_office_holders')) {
            $this->compactMorphColumns('election_office_holders', 'holderable', false);
        }
    }

    // ---------------------------------------------------------- helpers

    private function supportsCharsets(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }

    /** Compact morph columns: class names and UUIDs only need ASCII. */
    private function addMorphColumns(Blueprint $table, string $name, bool $nullable = true): void
    {
        foreach (["{$name}_type" => self::MORPH_TYPE_LENGTH, "{$name}_id" => self::MORPH_ID_LENGTH] as $column => $length) {
            $definition = $table->string($column, $length)->nullable($nullable);
            if ($this->supportsCharsets()) {
                $definition->charset('ascii')->collation('ascii_bin');
            }
        }
    }

    /** Shrink existing morph columns (e.g. varchar(255) utf8mb4) before indexing them. */
    private function compactMorphColumns(string $table, string $name, bool $nullable): void
    {
        if (!Schema::hasColumn($table, "{$name}_type")) {
            return;
        }
        $columns = collect(Schema::getColumns($table))->keyBy('name');
        $type = $columns["{$name}_type"] ?? null;
        $id = $columns["{$name}_id"] ?? null;
        $isCompact = fn($column, $length) => $column
            && str_contains(strtolower((string) $column['type']), "({$length})")
            && (!$this->supportsCharsets() || str_starts_with(strtolower((string) $column['collation']), 'ascii'));

        if ($isCompact($type, self::MORPH_TYPE_LENGTH) && $isCompact($id, self::MORPH_ID_LENGTH)) {
            return;
        }
        Schema::table($table, function (Blueprint $blueprint) use ($name, $nullable) {
            foreach (["{$name}_type" => self::MORPH_TYPE_LENGTH, "{$name}_id" => self::MORPH_ID_LENGTH] as $column => $length) {
                $definition = $blueprint->string($column, $length)->nullable($nullable);
                if ($this->supportsCharsets()) {
                    $definition->charset('ascii')->collation('ascii_bin');
                }
                $definition->change();
            }
        });
    }

    /**
     * Create an index or unique key once.
     *
     * On MySQL/MariaDB, ID columns wider than KEY_PREFIX_LENGTH (e.g. a
     * varchar(255) election_role_id on older databases) are indexed on their
     * first KEY_PREFIX_LENGTH characters. The IDs are 36-character UUIDs, so
     * uniqueness is unchanged and the key stays under MySQL's 3072-byte limit
     * whatever the column widths are.
     */
    private function ensureIndex(string $table, array $columns, string $name, bool $unique = false): void
    {
        if ($this->hasIndex($table, $name)) {
            return;
        }

        if (!$this->supportsCharsets()) {
            Schema::table($table, fn(Blueprint $t) => $unique ? $t->unique($columns, $name) : $t->index($columns, $name));
            return;
        }

        $definitions = collect(Schema::getColumns($table))->keyBy('name');
        $parts = collect($columns)->map(function (string $column) use ($definitions) {
            $type = strtolower((string) ($definitions[$column]['type'] ?? ''));
            $ascii = str_starts_with(strtolower((string) ($definitions[$column]['collation'] ?? '')), 'ascii');
            $length = preg_match('/^varchar\((\d+)\)/', $type, $match) ? (int) $match[1] : null;
            // Only wide multi-byte (utf8mb4) columns need a prefix; compact ASCII ones are small.
            return $length !== null && !$ascii && $length > self::KEY_PREFIX_LENGTH
                ? sprintf('`%s`(%d)', $column, self::KEY_PREFIX_LENGTH)
                : sprintf('`%s`', $column);
        })->implode(', ');

        DB::statement(sprintf(
            'ALTER TABLE `%s` ADD %s `%s` (%s)',
            $table,
            $unique ? 'UNIQUE INDEX' : 'INDEX',
            $name,
            $parts
        ));
    }

    private function hasIndex(string $table, string $index): bool
    {
        return collect(Schema::getIndexes($table))->contains(fn($i) => $i['name'] === $index);
    }

    // ---------------------------------------------------------------- down

    private function dropIndexKeepingForeignKey(string $table, string $index, string $column, bool $unique = false): void
    {
        if (!$this->hasIndex($table, $index)) {
            return;
        }
        $plain = "{$table}_{$column}_index";
        if (!$this->hasIndex($table, $plain)) {
            Schema::table($table, fn(Blueprint $t) => $t->index($column, $plain));
        }
        Schema::table($table, fn(Blueprint $t) => $unique ? $t->dropUnique($index) : $t->dropIndex($index));
    }

    public function down(): void
    {
        // election_votes
        // MySQL drops a foreign key's implicit index once a composite index can
        // serve it, so a plain index is restored before the composite is removed.
        $this->dropIndexKeepingForeignKey('election_votes', 'election_votes_unique_ballot', 'election_id', true);
        $this->dropIndexKeepingForeignKey('election_votes', 'election_votes_voter_index', 'election_id');

        // The legacy schema only supports student applicants/candidates, so
        // teacher and admin records (and what depends on them) are discarded.
        $nonStudentCandidates = Schema::hasColumn('election_candidates', 'candidateable_type')
            ? DB::table('election_candidates')->where('candidateable_type', '!=', self::STUDENT_MORPH)->pluck('id')
            : collect();
        if ($nonStudentCandidates->isNotEmpty()) {
            DB::table('election_votes')->whereIn('candidate_id', $nonStudentCandidates)->delete();
            if (Schema::hasTable('election_office_holders')) {
                DB::table('election_office_holders')->whereIn('election_candidate_id', $nonStudentCandidates)
                    ->update(['election_candidate_id' => null]);
            }
        }

        // election_candidates
        if (Schema::hasColumn('election_candidates', 'candidateable_type')) {
            if (!Schema::hasColumn('election_candidates', 'student_id')) {
                Schema::table('election_candidates', function (Blueprint $table) {
                    $table->uuid('student_id')->nullable();
                    $table->boolean('isActive')->default(true);
                });
            }
            DB::table('election_candidates')->where('candidateable_type', self::STUDENT_MORPH)
                ->update(['student_id' => DB::raw('candidateable_id')]);
            DB::table('election_candidates')->where('status', '!=', 'active')->update(['isActive' => false]);
            DB::table('election_candidates')->whereNull('student_id')->delete();
            $this->dropIndexKeepingForeignKey('election_candidates', 'election_candidates_application_unique', 'application_id', true);
            Schema::table('election_candidates', function (Blueprint $table) {
                $table->foreign('student_id')->references('id')->on('students');
                $table->dropIndex('election_candidates_candidateable_index');
                $table->dropColumn(['candidateable_type', 'candidateable_id', 'status', 'status_reason']);
            });
        }

        // election_applications
        if (Schema::hasColumn('election_applications', 'applicantable_type')) {
            if (!Schema::hasColumn('election_applications', 'student_id')) {
                Schema::table('election_applications', function (Blueprint $table) {
                    $table->uuid('student_id')->nullable();
                });
            }
            DB::table('election_applications')->where('applicantable_type', self::STUDENT_MORPH)
                ->update(['student_id' => DB::raw('applicantable_id')]);
            $orphaned = DB::table('election_applications')->whereNull('student_id')->pluck('id');
            DB::table('election_candidates')->whereIn('application_id', $orphaned)->delete();
            DB::table('election_applications')->whereIn('id', $orphaned)->delete();
            DB::table('election_applications')->where('application_status', 'withdrawn')
                ->update(['application_status' => 'rejected']);
            $this->dropIndexKeepingForeignKey('election_applications', 'election_applications_unique_applicant', 'election_id', true);
            Schema::table('election_applications', function (Blueprint $table) {
                $table->foreign('student_id')->references('id')->on('students');
                $table->dropIndex('election_applications_applicantable_index');
                $table->dropColumn([
                    'applicantable_type', 'applicantable_id',
                    'reviewed_at', 'reviewer_type', 'reviewer_id', 'review_remarks',
                ]);
                $table->enum('application_status', ['pending', 'approved', 'rejected'])->default('pending')->change();
            });
        }

        // election_participants (legacy specialty/level rows are not restored)
        if (!Schema::hasColumn('election_participants', 'specialty_id')) {
            DB::table('election_participants')->delete();
            $this->dropIndexKeepingForeignKey('election_participants', 'election_participants_unique_participant', 'election_id', true);
            Schema::table('election_participants', function (Blueprint $table) {
                $table->dropIndex('election_participants_participantable_index');
                $table->dropColumn(['participantable_type', 'participantable_id', 'can_vote', 'can_apply']);
                $table->uuid('specialty_id');
                $table->foreign('specialty_id')->references('id')->on('specialties');
                $table->uuid('level_id');
                $table->foreign('level_id')->references('id')->on('levels');
            });
        }

        if (Schema::hasColumn('election_roles', 'seats')) {
            Schema::table('election_roles', fn(Blueprint $table) => $table->dropColumn('seats'));
        }

        Schema::table('elections', function (Blueprint $table) {
            $columns = array_values(array_filter([
                'title', 'description', 'status', 'participants',
                'max_applications_per_participant', 'allow_candidate_self_vote', 'results_published_at',
            ], fn($column) => Schema::hasColumn('elections', $column)));
            if ($columns) {
                $table->dropColumn($columns);
            }
        });

        // The legacy tables are intentionally not recreated: their data is now
        // derived from election_votes / election_office_holders.
    }
};
