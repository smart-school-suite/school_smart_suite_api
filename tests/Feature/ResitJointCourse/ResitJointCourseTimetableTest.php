<?php

namespace Tests\Feature\ResitJointCourse;

use App\Exceptions\AppException;
use App\Exceptions\ExamJointCourse\ExamJointCourseSlotConflict;
use App\Exceptions\ResitTimetable\ResitJointCoursesWithoutTime;
use App\Models\ExamTimetable\ExamTimetableVersion;
use App\Models\ResitJointCourse\ResitJointCourse;
use App\Models\ResitJointCourse\ResitJointCourseRef;
use App\Models\ResitJointCourse\ResitJointCourseSlot;
use App\Models\ResitTimetable\ResitInvigilator;
use App\Models\Teacher;
use App\Services\ExamTimetable\ActivateExamTimetableService;
use App\Services\ResitJointCourse\ResitJointCourseSync;
use App\Services\ResitJointCourse\ResitJointCourseTimetableService;
use App\Services\ResitTimetable\ResitTimetableGenerationDispatcher;
use App\Services\ResitTimetable\ResitTimetableSetupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The resit joint course timetable: courses students of several specialties
 * resit are found, get one time that suits every candidate, and resit
 * timetable generation waits for it.
 */
class ResitJointCourseTimetableTest extends TestCase
{
    use RefreshDatabase;

    private object $school;
    private ResitJointCourseTimetableService $timetable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = (object) ['id' => 'branch-1'];
        DB::table('school_branches')->insert(['id' => 'branch-1', 'name' => 'Main', 'abbreviation' => 'M', 'school_id' => 'school-1']);
        DB::table('system_academic_years')->insert(['id' => 'sys-2099', 'name' => '2099/2100', 'year_start' => 2099, 'year_end' => 2100]);
        DB::table('exam_types')->insert(['id' => 'resit-type', 'exam_name' => 'First Semester Resit', 'semester' => 'first', 'type' => 'resit', 'program_name' => 'resit', 'semester_id' => 'sem-1']);

        DB::table('halls')->insert(['id' => 'h-a', 'name' => 'Hall A', 'capacity' => 30, 'school_branch_id' => 'branch-1', 'status' => 'available']);
        DB::table('hall_types')->insert(['id' => 'ht-exam', 'name' => 'Exam hall', 'key' => 'exam.hall']);
        DB::table('school_hall_types')->insert(['id' => (string) Str::uuid(), 'hall_id' => 'h-a', 'hall_type_id' => 'ht-exam', 'school_branch_id' => 'branch-1']);

        foreach (['math', 'phys', 'e1'] as $course) {
            DB::table('courses')->insert(['id' => $course, 'course_code' => strtoupper($course), 'course_title' => ucfirst($course), 'credit' => 3, 'school_branch_id' => 'branch-1', 'semester_id' => 'sem-1']);
        }

        // Two specialties, each with a failed exam and its resit exam.
        foreach (['spec-1' => 'Software', 'spec-2' => 'Networks'] as $id => $name) {
            DB::table('specialties')->insert(['id' => $id, 'specialty_name' => $name, 'school_branch_id' => 'branch-1', 'level_id' => 'level-1', 'department_id' => 'dep-1', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active']);
            DB::table('school_academic_years')->insert(['id' => "year-{$id}", 'specialty_id' => $id, 'school_branch_id' => 'branch-1', 'system_academic_year_id' => 'sys-2099', 'start_date' => '2099-01-01', 'end_date' => '2099-12-31']);
            DB::table('exams')->insert(['id' => "exam-{$id}", 'school_branch_id' => 'branch-1', 'exam_type_id' => 'type-1', 'school_year_id' => "year-{$id}", 'start_date' => '2099-11-01', 'end_date' => '2099-11-30']);
            DB::table('resit_exams')->insert(['id' => "resit-{$id}", 'school_branch_id' => 'branch-1', 'exam_type_id' => 'resit-type', 'school_year_id' => "year-{$id}", 'start_date' => '2099-12-07', 'end_date' => '2099-12-08']);
            DB::table('resit_exam_references')->insert(['id' => (string) Str::uuid(), 'school_branch_id' => 'branch-1', 'exam_id' => "exam-{$id}", 'resit_exam_id' => "resit-{$id}"]);
        }

        // Maths is resat in both specialties; Physics only in Software.
        $this->resit('exam-spec-1', 's1', 'math');
        $this->resit('exam-spec-1', 's2', 'phys');
        $this->resit('exam-spec-2', 's3', 'math');

        $this->timetable = app(ResitJointCourseTimetableService::class);
        app(ResitJointCourseSync::class)->forTerm('branch-1', 'sys-2099', 'resit-type');
    }

    private function resit(string $examId, string $student, string $course): void
    {
        DB::table('student_resits')->insert(['id' => (string) Str::uuid(), 'school_branch_id' => 'branch-1', 'student_id' => $student, 'course_id' => $course, 'exam_id' => $examId]);
    }

    private function mathsJointCourse(): ResitJointCourse
    {
        return ResitJointCourse::where('course_id', 'math')->firstOrFail();
    }

    public function test_courses_several_specialties_resit_become_joint_resits(): void
    {
        $term = $this->timetable->term($this->school, 'sys-2099', 'resit-type');

        $this->assertSame(['math'], array_column(array_column($term['courses'], 'course'), 'id'));
        $this->assertSame('not_configured', $term['courses'][0]['status']);
        $this->assertEqualsCanonicalizing(['resit-spec-1', 'resit-spec-2'], array_column($term['courses'][0]['specialties'], 'resit_exam_id'));

        $terms = $this->timetable->terms($this->school);
        $this->assertSame(1, $terms[0]['joint_course_count']);
        $this->assertSame(2, $terms[0]['exam_count']);
    }

    public function test_a_course_no_longer_shared_stops_being_joint(): void
    {
        app(ResitJointCourseSync::class)->forTerm('branch-1', 'sys-2099', 'resit-type');
        $this->assertSame(1, ResitJointCourse::count());

        DB::table('student_resits')->where('student_id', 's3')->delete();
        app(ResitJointCourseSync::class)->forTerm('branch-1', 'sys-2099', 'resit-type');

        $this->assertSame(0, ResitJointCourse::count());
        $this->assertSame(0, ResitJointCourseRef::count());
    }

    public function test_free_times_avoid_exams_the_candidates_already_sit_and_a_pick_is_saved(): void
    {
        // s3 (resits Maths) sits e1 in an active exam timetable on 7 Dec 08:00-10:00, in Hall A.
        DB::table('exam_candidates')->insert(['id' => (string) Str::uuid(), 'school_branch_id' => 'branch-1', 'exam_id' => 'exam-spec-2', 'student_id' => 's3']);
        $version = ExamTimetableVersion::createNext('branch-1', 'exam-spec-2', ExamTimetableVersion::STATUS_OPTIMAL);
        $slotId = (string) Str::uuid();
        DB::table('exam_timetable_slots')->insert(['id' => $slotId, 'date' => '2099-12-07', 'start_time' => '08:00:00', 'end_time' => '10:00:00', 'course_id' => 'e1', 'version_id' => $version->id, 'exam_id' => 'exam-spec-2', 'school_branch_id' => 'branch-1']);
        DB::table('exam_session_halls')->insert(['id' => (string) Str::uuid(), 'candidate_count' => 20, 'exam_slot_id' => $slotId, 'specialty_id' => 'spec-2', 'hall_id' => 'h-a', 'school_branch_id' => 'branch-1']);

        $joint = $this->mathsJointCourse();
        $proposal = $this->timetable->generate($this->school, $joint->id, ['duration' => 120, 'start_time' => '08:00', 'end_time' => '12:00']);

        $times = collect($proposal['dates'])->flatMap(fn(array $date) => $date['times']);
        $this->assertNotEmpty($times);
        $this->assertSame(2, $proposal['candidates']['total']);
        foreach ($times as $time) {
            $this->assertFalse($time['date'] === '2099-12-07' && $time['start_time'] < '10:00', "{$time['date']} {$time['start_time']} clashes with s3's exam");
        }

        $pick = $times->first();
        $saved = $this->timetable->confirmProposal($this->school, $joint->id, $proposal['proposal_id'], $pick['option_id']);

        $this->assertSame('configured', $saved['course']['status']);
        $this->assertSame([$pick['date'], $pick['start_time']], [$saved['course']['slot']['date'], $saved['course']['slot']['start_time']]);

        // A time when s3 sits e1 is refused.
        $this->expectException(ExamJointCourseSlotConflict::class);
        $this->timetable->saveSlot($this->school, $joint->id, ['date' => '2099-12-07', 'start_time' => '08:00', 'end_time' => '10:00']);
    }

    public function test_a_teacher_of_the_course_is_not_counted_as_a_free_invigilator(): void
    {
        DB::table('teacher_course_preferences')->insert(['id' => (string) Str::uuid(), 'school_branch_id' => 'branch-1', 'teacher_id' => 't-maths', 'course_id' => 'math']);
        ResitInvigilator::create(['school_branch_id' => 'branch-1', 'resit_exam_id' => 'resit-spec-1', 'actorable_id' => 't-maths', 'actorable_type' => Teacher::class]);

        $proposal = $this->timetable->generate($this->school, $this->mathsJointCourse()->id, ['duration' => 120]);

        $this->assertSame(0, $proposal['summary']['option_count']);
        $this->assertStringContainsString('teach the course', $proposal['summary']['message']);
    }

    public function test_resit_timetable_generation_waits_for_joint_resit_times(): void
    {
        $setup = app(ResitTimetableSetupService::class)->schedulingData($this->school, 'resit-spec-1');
        $maths = collect($setup['courses'])->firstWhere('course_id', 'math');
        $this->assertTrue($maths['is_joint']);
        $this->assertNull($maths['joint']['slot']);

        try {
            app(ResitTimetableGenerationDispatcher::class)->dispatch($this->school, ['resit_exam_id' => 'resit-spec-1'], (object) ['id' => 'admin-1']);
            $this->fail('Generation started without a joint resit time');
        } catch (ResitJointCoursesWithoutTime $missing) {
            $this->assertSame(['math'], array_column($missing->courses(), 'course_id'));
        }

        $this->timetable->saveSlot($this->school, $this->mathsJointCourse()->id, ['date' => '2099-12-08', 'start_time' => '10:00', 'end_time' => '12:00']);
        $setup = app(ResitTimetableSetupService::class)->schedulingData($this->school, 'resit-spec-1');
        $this->assertSame('10:00', collect($setup['courses'])->firstWhere('course_id', 'math')['joint']['slot']['start_time']);
    }

    public function test_removing_a_time_unsets_it(): void
    {
        $joint = $this->mathsJointCourse();
        $this->timetable->saveSlot($this->school, $joint->id, ['date' => '2099-12-08', 'start_time' => '10:00', 'end_time' => '12:00']);

        $result = $this->timetable->removeSlot($this->school, $joint->id);

        $this->assertSame('not_configured', $result['course']['status']);
        $this->assertSame(0, ResitJointCourseSlot::count());
    }
}
