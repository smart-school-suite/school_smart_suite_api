<?php

namespace Tests\Feature\ResitTimetable;

use App\Exceptions\AppException;
use App\Exceptions\ExamTimetable\ExamTimetableActivationConflict;
use App\Models\ExamTimetable\ExamInvigilator;
use App\Models\ExamTimetable\ExamTimetableVersion;
use App\Models\ResitExam;
use App\Models\Resitexamtimetable;
use App\Models\ResitTimetable\ResitInvigilator;
use App\Models\ResitTimetable\ResitSessionHall;
use App\Models\ResitTimetable\ResitSessionInvigilator;
use App\Models\ResitTimetable\ResitTimetableVersion;
use App\Models\Teacher;
use App\Schedular\ExamTimetable\Engine\SchedularEngine;
use App\Schedular\ExamTimetable\Placement\Support\Time;
use App\Services\ExamTimetable\ActivateExamTimetableService;
use App\Services\ExamTimetable\Support\ExamActiveCommitments;
use App\Services\ResitTimetable\ActivateResitTimetableService;
use App\Services\ResitTimetable\ResitInvigilatorService;
use App\Services\ResitTimetable\Support\JointResitCourses;
use App\Services\ResitTimetable\Support\ResitActiveCommitments;
use App\Services\ResitTimetable\Support\ResitCandidates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Resit timetables: candidates per course, commitments shared with exam
 * timetables, activation and its clash checks, invigilators.
 * Generated documents (Mongo) are kept in $generated instead.
 */
class ResitTimetableTest extends TestCase
{
    use RefreshDatabase;

    private object $school;
    private array $generated = [];
    private ActivateResitTimetableService $resitActivation;
    private ActivateExamTimetableService $examActivation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = (object) ['id' => 'branch-1'];
        DB::table('school_branches')->insert(['id' => 'branch-1', 'name' => 'Main', 'abbreviation' => 'M', 'school_id' => 'school-1']);

        foreach ([['h-a', 'Hall A', 30], ['h-b', 'Hall B', 40]] as [$id, $name, $capacity]) {
            DB::table('halls')->insert(['id' => $id, 'name' => $name, 'capacity' => $capacity, 'school_branch_id' => 'branch-1']);
        }
        foreach (['c1', 'c2', 'c3', 'e1'] as $course) {
            DB::table('courses')->insert(['id' => $course, 'course_code' => strtoupper($course), 'course_title' => "Course {$course}", 'credit' => 3, 'school_branch_id' => 'branch-1', 'semester_id' => 'sem-1']);
        }

        foreach (['spec-1' => 'Software', 'spec-2' => 'Networks'] as $id => $name) {
            DB::table('specialties')->insert(['id' => $id, 'specialty_name' => $name, 'school_branch_id' => 'branch-1', 'level_id' => 'level-1', 'department_id' => 'dep-1', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active']);
            DB::table('school_academic_years')->insert(['id' => "year-{$id}", 'specialty_id' => $id, 'school_branch_id' => 'branch-1', 'system_academic_year_id' => 'sys-2099', 'start_date' => '2099-01-01', 'end_date' => '2099-12-31']);
        }

        // The exams students failed, and the resit exam referring to them.
        $this->exam('exam-1', 'year-spec-1');
        $this->resitExam('resit-1', 'year-spec-1', ['exam-1']);

        // s1 resits c1 and c3, s2 resits c1, s3 resits c2.
        $this->resits('exam-1', ['s1' => ['c1', 'c3'], 's2' => ['c1'], 's3' => ['c2']]);

        $generated = &$this->generated;
        $this->resitActivation = new class($generated) extends ActivateResitTimetableService {
            public function __construct(private array &$generated)
            {
                parent::__construct(app(ResitActiveCommitments::class));
            }

            protected function generatedTimetable(object $currentSchool, ResitTimetableVersion $version): array
            {
                return $this->generated[$version->id];
            }
        };
        $this->examActivation = new class($generated) extends ActivateExamTimetableService {
            public function __construct(private array &$generated) {}

            protected function generatedTimetable(object $currentSchool, ExamTimetableVersion $version): array
            {
                return $this->generated[$version->id];
            }
        };
    }

    // ── Fixtures ─────────────────────────────────────────────────────────

    private function exam(string $id, string $yearId): void
    {
        DB::table('exams')->insert(['id' => $id, 'school_branch_id' => 'branch-1', 'exam_type_id' => 'type-1', 'school_year_id' => $yearId, 'start_date' => '2099-12-01', 'end_date' => '2099-12-31']);
    }

    private function resitExam(string $id, string $yearId, array $examIds): void
    {
        DB::table('resit_exams')->insert(['id' => $id, 'school_branch_id' => 'branch-1', 'exam_type_id' => 'resit-type-1', 'school_year_id' => $yearId, 'start_date' => '2099-12-07', 'end_date' => '2099-12-08']);
        foreach ($examIds as $examId) {
            DB::table('resit_exam_references')->insert(['id' => (string) Str::uuid(), 'school_branch_id' => 'branch-1', 'exam_id' => $examId, 'resit_exam_id' => $id]);
        }
    }

    private function resits(string $examId, array $byStudent): void
    {
        foreach ($byStudent as $student => $courses) {
            foreach ($courses as $course) {
                DB::table('student_resits')->insert(['id' => (string) Str::uuid(), 'school_branch_id' => 'branch-1', 'student_id' => $student, 'course_id' => $course, 'exam_id' => $examId]);
            }
        }
    }

    private function resitInvigilators(string $resitExamId, array $teacherIds): array
    {
        $ids = [];
        foreach ($teacherIds as $teacherId) {
            $invigilator = ResitInvigilator::create(['school_branch_id' => 'branch-1', 'resit_exam_id' => $resitExamId, 'actorable_id' => $teacherId, 'actorable_type' => Teacher::class]);
            $ids[$invigilator->id] = $teacherId;
        }
        return $ids;
    }

    /** The scheduler input the job builds for a resit exam. */
    private function resitInput(string $resitExamId, array $invigilators, array $busy = [], array $joint = []): array
    {
        $candidates = app(ResitCandidates::class)->byCourse('branch-1', ResitExam::find($resitExamId));

        return [
            'exam_id' => $resitExamId,
            'resit_exam_id' => $resitExamId,
            'start_date' => '2099-12-07',
            'end_date' => '2099-12-08',
            'candidate_count' => collect($candidates)->flatten()->unique()->count(),
            'courses' => collect($candidates)->map(fn($students, $course) => [
                'course_id' => $course,
                'course_title' => "Course {$course}",
                'candidate_count' => count($students),
                'student_ids' => $students,
            ])->values()->all(),
            'halls' => [
                ['hall_id' => 'h-a', 'hall_name' => 'Hall A', 'capacity' => 30],
                ['hall_id' => 'h-b', 'hall_name' => 'Hall B', 'capacity' => 40],
            ],
            'invigilators' => array_map(fn($id) => ['invigilator_id' => $id, 'name' => $id, 'courses_taught' => []], array_keys($invigilators)),
            'hall_busy_periods' => $busy['hall_busy_periods'] ?? [],
            'invigilator_busy_periods' => $busy['invigilator_busy_periods'] ?? [],
            'candidate_busy_periods' => $busy['candidate_busy_periods'] ?? [],
            'hard_constraints' => array_filter([
                'operational_period' => ['start_time' => '08:00', 'end_time' => '12:00'],
                'session_duration' => ['duration' => 120],
                'required_joint_course_periods' => $joint ?: null,
            ]),
            'soft_constraints' => [],
        ];
    }

    private function generateResitVersion(string $resitExamId, array $input): ResitTimetableVersion
    {
        $response = app(SchedularEngine::class)->run($input, withSolutions: false);
        $version = ResitTimetableVersion::createNext('branch-1', $resitExamId, $response->status);
        $this->generated[$version->id] = ['timetable_slots' => $response->timetable, 'scheduler_input' => $input];
        return $version;
    }

    /** An active exam timetable: e1 on 7 Dec 08:00-10:00 in Hall A, invigilated by teacher $teacher, sat by $students. */
    private function activeExam(string $teacher, array $students): void
    {
        $this->exam('exam-2', 'year-spec-2');
        foreach ($students as $student) {
            DB::table('exam_candidates')->insert(['id' => (string) Str::uuid(), 'school_branch_id' => 'branch-1', 'exam_id' => 'exam-2', 'student_id' => $student]);
        }
        $invigilator = ExamInvigilator::create(['school_branch_id' => 'branch-1', 'exam_id' => 'exam-2', 'actorable_id' => $teacher, 'actorable_type' => Teacher::class]);

        $version = ExamTimetableVersion::createNext('branch-1', 'exam-2', ExamTimetableVersion::STATUS_OPTIMAL);
        $this->generated[$version->id] = [
            'timetable_slots' => [['date' => '2099-12-07', 'slots' => [[
                'course_id' => 'e1', 'course_title' => 'Course e1', 'start_time' => '08:00', 'end_time' => '10:00', 'candidate_count' => 20,
                'halls' => [['hall_id' => 'h-a', 'hall_name' => 'Hall A', 'capacity' => 30, 'candidate_count' => 20, 'invigilators' => [['invigilator_id' => $invigilator->id, 'name' => $teacher]]]],
            ]]]],
            'scheduler_input' => ['specialty_id' => 'spec-2', 'halls' => [['hall_id' => 'h-a', 'capacity' => 30]]],
        ];
        $this->examActivation->activate($this->school, $version->id);
    }

    private function sessions(ResitTimetableVersion $version): array
    {
        $sessions = [];
        foreach ($this->generated[$version->id]['timetable_slots'] as $day) {
            foreach ($day['slots'] as $slot) {
                $sessions[$slot['course_id']] = [...$slot, 'date' => $day['date']];
            }
        }
        return $sessions;
    }

    // ── Tests ────────────────────────────────────────────────────────────

    public function test_candidates_are_grouped_by_the_course_they_resit(): void
    {
        $byCourse = app(ResitCandidates::class)->byCourse('branch-1', ResitExam::find('resit-1'));

        $this->assertEqualsCanonicalizing(['s1', 's2'], $byCourse['c1']);
        $this->assertSame(['s3'], $byCourse['c2']);
        $this->assertSame(['s1'], $byCourse['c3']);
    }

    public function test_a_generated_resit_timetable_activates_with_halls_and_invigilators(): void
    {
        $invigilators = $this->resitInvigilators('resit-1', ['t1', 't2', 't3']);
        $version = $this->generateResitVersion('resit-1', $this->resitInput('resit-1', $invigilators));
        $sessions = $this->sessions($version);

        $this->assertCount(3, $sessions);
        // s1 sits c1 and c3: never at once.
        $this->assertFalse($sessions['c1']['date'] === $sessions['c3']['date'] && Time::overlaps(
            Time::toMinutes($sessions['c1']['start_time']), Time::toMinutes($sessions['c1']['end_time']),
            Time::toMinutes($sessions['c3']['start_time']), Time::toMinutes($sessions['c3']['end_time'])
        ));

        $result = $this->resitActivation->activate($this->school, $version->id);

        $this->assertSame(3, $result['session_count']);
        $this->assertSame(3, Resitexamtimetable::where('version_id', $version->id)->count());
        $this->assertSame(4, (int) ResitSessionHall::sum('candidate_count'));
        $this->assertGreaterThan(0, ResitSessionInvigilator::count());
        $this->assertSame(0, ResitSessionInvigilator::whereNull('resit_session_hall_id')->count());
        $this->assertSame('spec-1', Resitexamtimetable::first()->specialty_id);
        $this->assertSame($version->id, $this->resitActivation->activeFor($this->school, 'resit-1')['active_version']['id']);
    }

    public function test_active_exams_make_halls_invigilators_and_candidates_busy_for_resits(): void
    {
        // s1 also sits e1 in another specialty's exam, invigilated by teacher t1.
        $this->activeExam('t1', ['s1', 's9']);
        $invigilators = $this->resitInvigilators('resit-1', ['t1', 't2']);

        $busy = app(ResitActiveCommitments::class)->busyPeriodsForGeneration(
            'branch-1', 'resit-1', '2099-12-07', '2099-12-08', ['h-a', 'h-b'],
            ResitInvigilator::where('resit_exam_id', 'resit-1')->get(), ['s1', 's2', 's3'],
        );

        $this->assertSame(['h-a'], array_column($busy['hall_busy_periods'], 'hall_id'));
        $this->assertSame([array_search('t1', $invigilators, true)], array_column($busy['invigilator_busy_periods'], 'invigilator_id'));
        $this->assertSame(['s1'], array_column($busy['candidate_busy_periods'], 'student_id'));

        $version = $this->generateResitVersion('resit-1', $this->resitInput('resit-1', $invigilators, $busy));
        $sessions = $this->sessions($version);

        // s1's resits are never on 7 Dec 08:00-10:00.
        foreach (['c1', 'c3'] as $course) {
            $this->assertFalse($sessions[$course]['date'] === '2099-12-07' && Time::toMinutes($sessions[$course]['start_time']) < Time::toMinutes('10:00'), "{$course} clashes with e1");
        }

        $check = $this->resitActivation->check($this->school, $version->id);
        $this->assertTrue($check['can_activate'], json_encode($check['conflicts']));
    }

    public function test_a_resit_clashing_with_an_exam_a_candidate_sits_is_not_activated(): void
    {
        $invigilators = $this->resitInvigilators('resit-1', ['t2', 't3']);
        // Generated before the exam was active, with only 7 Dec mornings: c1 starts at 08:00.
        $version = $this->generateResitVersion('resit-1', $this->resitInput('resit-1', $invigilators));
        $this->activeExam('t1', ['s1', 's2', 's3']);

        $check = $this->resitActivation->check($this->school, $version->id);

        $this->assertFalse($check['can_activate']);
        $this->assertContains('students', array_column($check['conflicts'], 'type'));

        $this->expectException(ExamTimetableActivationConflict::class);
        $this->resitActivation->activate($this->school, $version->id);
    }

    public function test_active_resits_make_halls_and_invigilators_busy_for_exams(): void
    {
        $invigilators = $this->resitInvigilators('resit-1', ['t1', 't2', 't3']);
        $this->resitActivation->activate($this->school, $this->generateResitVersion('resit-1', $this->resitInput('resit-1', $invigilators))->id);

        $this->exam('exam-2', 'year-spec-2');
        $examInvigilator = ExamInvigilator::create(['school_branch_id' => 'branch-1', 'exam_id' => 'exam-2', 'actorable_id' => 't1', 'actorable_type' => Teacher::class]);

        $busy = app(ExamActiveCommitments::class)->busyPeriodsForGeneration(
            'branch-1', 'exam-2', '2099-12-07', '2099-12-08', ['h-a', 'h-b'],
            ExamInvigilator::where('exam_id', 'exam-2')->get(),
        );

        $this->assertNotEmpty($busy['hall_busy_periods']);
        $this->assertContains($examInvigilator->id, array_column($busy['invigilator_busy_periods'], 'invigilator_id'));
    }

    public function test_a_course_resat_by_another_specialty_keeps_its_active_time(): void
    {
        // spec-2 also resits c1; its resit timetable is active first.
        $this->exam('exam-3', 'year-spec-2');
        $this->resitExam('resit-2', 'year-spec-2', ['exam-3']);
        $this->resits('exam-3', ['s7' => ['c1']]);
        $other = $this->resitInvigilators('resit-2', ['t9']);
        $this->resitActivation->activate($this->school, $this->generateResitVersion('resit-2', $this->resitInput('resit-2', $other))->id);
        $theirs = Resitexamtimetable::where('resit_exam_id', 'resit-2')->first();

        $joint = app(JointResitCourses::class)->periodsFor('branch-1', ResitExam::find('resit-1'), ['c1', 'c2', 'c3'], '2099-12-07', '2099-12-08');

        $this->assertCount(1, $joint);
        $this->assertSame('c1', $joint[0]['course_id']);
        $this->assertSame(Time::normalize($theirs->getRawOriginal('start_time')), $joint[0]['start_time']);

        $invigilators = $this->resitInvigilators('resit-1', ['t1', 't2', 't3']);
        $busy = app(ResitActiveCommitments::class)->busyPeriodsForGeneration(
            'branch-1', 'resit-1', '2099-12-07', '2099-12-08', ['h-a', 'h-b'],
            ResitInvigilator::where('resit_exam_id', 'resit-1')->get(), ['s1', 's2', 's3'],
        );
        $version = $this->generateResitVersion('resit-1', $this->resitInput('resit-1', $invigilators, $busy, $joint));
        $c1 = $this->sessions($version)['c1'];

        $this->assertSame($joint[0]['date'], $c1['date']);
        $this->assertSame($joint[0]['start_time'], $c1['start_time']);
        $this->assertTrue($this->resitActivation->check($this->school, $version->id)['can_activate']);
    }

    public function test_an_invigilator_used_by_the_active_timetable_cannot_be_removed(): void
    {
        $invigilators = $this->resitInvigilators('resit-1', ['t1']);
        $this->resitActivation->activate($this->school, $this->generateResitVersion('resit-1', $this->resitInput('resit-1', $invigilators))->id);

        $this->expectException(AppException::class);
        app(ResitInvigilatorService::class)->remove($this->school, ['resit_exam_id' => 'resit-1', 'invigilator_ids' => array_keys($invigilators)]);
    }

    public function test_active_versions_cannot_be_deleted_and_incomplete_ones_not_activated(): void
    {
        $invigilators = $this->resitInvigilators('resit-1', ['t1', 't2']);
        $version = $this->generateResitVersion('resit-1', $this->resitInput('resit-1', $invigilators));
        $this->resitActivation->activate($this->school, $version->id);

        try {
            app(\App\Services\ResitTimetable\ResitTimetableVersionService::class)->deleteVersion($this->school, $version->id);
            $this->fail('An active version was deleted');
        } catch (AppException $e) {
            $this->assertSame(422, $e->getCode());
        }

        $failed = ResitTimetableVersion::createNext('branch-1', 'resit-1', ResitTimetableVersion::STATUS_ERROR);
        $this->expectException(AppException::class);
        $this->resitActivation->activate($this->school, $failed->id);
    }
}
