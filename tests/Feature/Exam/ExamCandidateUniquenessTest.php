<?php

namespace Tests\Feature\Exam;

use App\Jobs\Exam\CreateExamCandidateJob;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** A student is a candidate of an exam at most once. */
class ExamCandidateUniquenessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        DB::table('levels')->insert(['id' => 'lvl-1', 'name' => 'Level 100', 'level' => 100, 'program_name' => 'l1', 'level_type_id' => 't']);
        DB::table('specialties')->insert(['id' => 'swe', 'specialty_name' => 'SWE', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => 'lvl-1']);
        DB::table('school_academic_years')->insert(['id' => 'sy', 'start_date' => '2026-09-01', 'end_date' => '2027-07-31', 'specialty_id' => 'swe', 'system_academic_year_id' => 'sys', 'school_branch_id' => 'branch-1']);
        DB::table('exam_types')->insert(['id' => 'ca-1', 'exam_name' => 'First CA', 'semester' => 'first', 'type' => 'ca', 'program_name' => 'ca1', 'semester_id' => 'first']);
        DB::table('exams')->insert(['id' => 'exam', 'exam_type_id' => 'ca-1', 'school_year_id' => 'sy', 'max_score' => 30, 'school_branch_id' => 'branch-1']);
        $this->student('stu-1');
        $this->student('stu-2');
    }

    private function student(string $id): void
    {
        DB::table('students')->insert(['id' => $id, 'name' => $id, 'first_name' => $id, 'last_name' => 'L', 'email' => "$id@example.com", 'password' => 'x', 'username' => $id, 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe', 'guardian_id' => 'g', 'student_batch_id' => 'b', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);
    }

    private function runJob(): void
    {
        (new CreateExamCandidateJob('exam', 'branch-1'))->handle();
    }

    private function candidates(): array
    {
        return DB::table('exam_candidates')->where('exam_id', 'exam')->orderBy('student_id')->pluck('student_id')->all();
    }

    public function test_running_the_job_again_creates_no_duplicates(): void
    {
        $this->runJob();
        $this->runJob();

        $this->assertSame(['stu-1', 'stu-2'], $this->candidates());
    }

    public function test_a_later_run_adds_only_new_students(): void
    {
        $this->runJob();
        $this->student('stu-3');
        $this->runJob();

        $this->assertSame(['stu-1', 'stu-2', 'stu-3'], $this->candidates());
    }

    public function test_the_database_refuses_a_second_candidate_for_the_same_student(): void
    {
        $this->runJob();

        $this->expectException(QueryException::class);
        DB::table('exam_candidates')->insert(['id' => 'dup', 'exam_id' => 'exam', 'student_id' => 'stu-1', 'school_branch_id' => 'branch-1']);
    }

    public function test_a_candidate_added_by_another_run_during_the_job_is_skipped(): void
    {
        // Another run inserts stu-1 right after this one checked who already is a candidate.
        $raced = false;
        DB::listen(function ($query) use (&$raced) {
            if (!$raced && str_contains($query->sql, 'select "student_id" from "exam_candidates"')) {
                $raced = true;
                DB::table('exam_candidates')->insert(['id' => 'other-run', 'exam_id' => 'exam', 'student_id' => 'stu-1', 'school_branch_id' => 'branch-1']);
            }
        });

        $this->runJob();

        $this->assertTrue($raced, 'The race was simulated.');
        $this->assertSame(['stu-1', 'stu-2'], $this->candidates());
        $this->assertSame('other-run', DB::table('exam_candidates')->where('student_id', 'stu-1')->value('id'));
    }

    public function test_the_migration_merges_existing_duplicates_and_keeps_their_scores(): void
    {
        // Kept: c (most scores). algo: c's later edit wins over b's. db: b's newer
        // copy wins and moves to c. net: c's only score stays.
        $migration = require database_path('migrations/03_academics/2026_11_21_090000_make_exam_candidates_unique_per_student.php');
        $migration->down();

        // stu-1 is a candidate three times: a has no scores, b two, c three.
        DB::table('exam_candidates')->insert([
            ['id' => 'a', 'exam_id' => 'exam', 'student_id' => 'stu-1', 'school_branch_id' => 'branch-1', 'created_at' => '2026-10-01 08:00:00'],
            ['id' => 'b', 'exam_id' => 'exam', 'student_id' => 'stu-1', 'school_branch_id' => 'branch-1', 'created_at' => '2026-10-01 08:00:01'],
            ['id' => 'c', 'exam_id' => 'exam', 'student_id' => 'stu-1', 'school_branch_id' => 'branch-1', 'created_at' => '2026-10-01 08:00:02'],
            ['id' => 'solo', 'exam_id' => 'exam', 'student_id' => 'stu-2', 'school_branch_id' => 'branch-1', 'created_at' => '2026-10-01 08:00:00'],
        ]);
        $score = fn($id, $candidate, $course, $value, $updated) => DB::table('exam_scores')->insert(['id' => $id, 'score' => $value, 'candidate_id' => $candidate, 'course_id' => $course, 'exam_id' => 'exam', 'grade_id' => 'g', 'school_branch_id' => 'branch-1', 'updated_at' => $updated]);
        $score('b-algo', 'b', 'algo', 20, '2026-10-05 10:00:00');
        $score('b-db', 'b', 'db', 15, '2026-10-05 10:00:00');
        $score('c-algo', 'c', 'algo', 25, '2026-10-06 10:00:00'); // a later edit of the same course
        $score('c-net', 'c', 'net', 18, '2026-10-05 10:00:00');
        $score('c-db', 'c', 'db', 15, '2026-10-04 10:00:00');     // same score, older

        $migration->up();
        $migration->up(); // safe to repeat

        $this->assertSame(['c', 'solo'], DB::table('exam_candidates')->orderBy('id')->pluck('id')->all(), 'c has the most scores (3).');
        $this->assertSame(
            ['algo' => ['c-algo', 25.0], 'db' => ['b-db', 15.0], 'net' => ['c-net', 18.0]],
            DB::table('exam_scores')->orderBy('course_id')->get()->mapWithKeys(fn($s) => [$s->course_id => [$s->id, (float) $s->score]])->all(),
        );
        $this->assertSame(['c'], DB::table('exam_scores')->distinct()->pluck('candidate_id')->all(), 'Every score belongs to the kept candidate, b-db included.');

        $this->expectException(QueryException::class);
        DB::table('exam_candidates')->insert(['id' => 'again', 'exam_id' => 'exam', 'student_id' => 'stu-1', 'school_branch_id' => 'branch-1']);
    }

    public function test_without_scores_the_oldest_candidate_is_kept(): void
    {
        $migration = require database_path('migrations/03_academics/2026_11_21_090000_make_exam_candidates_unique_per_student.php');
        $migration->down();
        DB::table('exam_candidates')->insert([
            ['id' => 'newer', 'exam_id' => 'exam', 'student_id' => 'stu-1', 'school_branch_id' => 'branch-1', 'created_at' => '2026-10-02 08:00:00'],
            ['id' => 'older', 'exam_id' => 'exam', 'student_id' => 'stu-1', 'school_branch_id' => 'branch-1', 'created_at' => '2026-10-01 08:00:00'],
        ]);

        $migration->up();

        $this->assertSame(['older'], DB::table('exam_candidates')->pluck('id')->all());
    }
}
