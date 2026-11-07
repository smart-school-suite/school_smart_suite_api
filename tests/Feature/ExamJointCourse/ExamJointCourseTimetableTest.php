<?php

namespace Tests\Feature\ExamJointCourse;

use App\Exceptions\ExamJointCourse\ExamJointCourseSlotConflict;
use App\Models\ExamJointCourse\ExamJointCourse;
use App\Models\ExamJointCourse\ExamJointCourseRef;
use App\Models\ExamJointCourse\ExamJointCourseSlot;
use App\Models\Teacher;
use App\Services\ExamJointCourse\ExamJointCourseSync;
use App\Services\ExamJointCourse\ExamJointCourseTimetableService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Two specialties (Software Engineering and Networking) share Mathematics.
 * Each has its own final exam in the same week; the joint Mathematics exam
 * gets one time for both.
 */
class ExamJointCourseTimetableTest extends TestCase
{
    use RefreshDatabase;

    private object $school;
    private ExamJointCourseTimetableService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = (object) ['id' => 'branch-1'];
        $this->service = app(ExamJointCourseTimetableService::class);

        DB::table('school_branches')->insert(['id' => 'branch-1', 'name' => 'Main', 'abbreviation' => 'M', 'school_id' => 'school-1']);
        DB::table('system_academic_years')->insert(['id' => 'year-2099', 'name' => '2099/2100', 'year_start' => 2099, 'year_end' => 2100]);
        DB::table('semesters')->insert(['id' => 'sem-1', 'name' => 'First Semester', 'count' => 1, 'program_name' => 'HND']);
        DB::table('exam_types')->insert(['id' => 'final', 'exam_name' => 'First Semester Exam', 'semester' => 'first', 'type' => 'exam', 'program_name' => 'HND', 'semester_id' => 'sem-1']);

        foreach (['swe' => 'Software Engineering', 'net' => 'Networking'] as $id => $name) {
            DB::table('specialties')->insert(['id' => $id, 'specialty_name' => $name, 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'dep', 'level_id' => 'lvl']);
            DB::table('school_academic_years')->insert(['id' => "sy-{$id}", 'start_date' => '2099-09-01', 'end_date' => '2100-06-30', 'specialty_id' => $id, 'system_academic_year_id' => 'year-2099', 'school_branch_id' => 'branch-1']);
        }

        // Mathematics is shared; Algorithms is Software Engineering only.
        foreach ([['math', 'Mathematics', ['swe', 'net']], ['algo', 'Algorithms', ['swe']]] as [$id, $title, $specialties]) {
            DB::table('courses')->insert(['id' => $id, 'course_code' => strtoupper($id), 'course_title' => $title, 'credit' => 3, 'school_branch_id' => 'branch-1', 'semester_id' => 'sem-1']);
            foreach ($specialties as $specialty) {
                DB::table('course_specialties')->insert(['id' => (string) Str::uuid(), 'course_id' => $id, 'specialty_id' => $specialty, 'school_branch_id' => 'branch-1']);
            }
        }

        DB::table('hall_types')->insert(['id' => 'type-exam', 'name' => 'Exam hall', 'key' => 'exam.hall']);
        foreach ([['h-big', 'Big Hall', 100], ['h-small', 'Small Hall', 30]] as [$id, $name, $capacity]) {
            DB::table('halls')->insert(['id' => $id, 'name' => $name, 'capacity' => $capacity, 'status' => 'available', 'school_branch_id' => 'branch-1']);
            DB::table('school_hall_types')->insert(['id' => (string) Str::uuid(), 'hall_id' => $id, 'hall_type_id' => 'type-exam', 'school_branch_id' => 'branch-1']);
        }

        // Teacher t-math teaches Mathematics.
        DB::table('teacher_course_preferences')->insert(['id' => 'pref-1', 'teacher_id' => 't-math', 'course_id' => 'math', 'school_branch_id' => 'branch-1']);

        foreach (['swe' => 50, 'net' => 40] as $specialty => $candidates) {
            $examId = "exam-{$specialty}";
            DB::table('exams')->insert(['id' => $examId, 'school_branch_id' => 'branch-1', 'exam_type_id' => 'final', 'school_year_id' => "sy-{$specialty}", 'start_date' => '2099-12-07', 'end_date' => '2099-12-08']);
            for ($i = 0; $i < $candidates; $i++) {
                DB::table('exam_candidates')->insert(['id' => (string) Str::uuid(), 'school_branch_id' => 'branch-1', 'exam_id' => $examId]);
            }
            foreach (['t-math', "t-{$specialty}"] as $teacher) {
                DB::table('exam_invigilators')->insert(['id' => (string) Str::uuid(), 'school_branch_id' => 'branch-1', 'exam_id' => $examId, 'actorable_id' => $teacher, 'actorable_type' => Teacher::class]);
            }
            app(ExamJointCourseSync::class)->forExam('branch-1', $examId);
        }
    }

    private function jointCourse(): ExamJointCourse
    {
        return ExamJointCourse::where('course_id', 'math')->firstOrFail();
    }

    public function test_shared_courses_become_one_joint_exam_linked_to_both_exams(): void
    {
        $this->assertSame(1, ExamJointCourse::count());
        $this->assertSame('final', $this->jointCourse()->exam_type_id);
        $this->assertEqualsCanonicalizing(['exam-swe', 'exam-net'], ExamJointCourseRef::pluck('exam_id')->all());

        // Syncing again changes nothing.
        app(ExamJointCourseSync::class)->forExam('branch-1', 'exam-swe');
        $this->assertSame(2, ExamJointCourseRef::count());

        $term = $this->service->term($this->school, 'year-2099', 'final');
        $this->assertSame(1, $term['summary']['not_configured_count']);
        $this->assertCount(2, $term['courses'][0]['specialties']);
    }

    public function test_generates_times_for_both_specialties_together_and_saves_the_picked_one(): void
    {
        $proposal = $this->service->generate($this->school, $this->jointCourse()->id, [
            'duration' => 120, 'start_time' => '08:00', 'end_time' => '12:00',
        ]);

        $this->assertSame(90, $proposal['candidates']['total']);
        $this->assertCount(2, $proposal['dates']);
        $this->assertNotNull($proposal['recommended_option_id']);
        foreach ($proposal['dates'] as $date) {
            foreach ($date['times'] as $time) {
                $this->assertSame(90, $time['seats_needed']);
                // Only t-swe and t-net may invigilate: t-math teaches the course.
                $this->assertSame(2, $time['invigilators_free']);
            }
        }

        $option = $proposal['dates'][1]['times'][0];
        $result = $this->service->confirmProposal($this->school, $this->jointCourse()->id, $proposal['proposal_id'], $option['option_id']);

        $this->assertSame('configured', $result['course']['status']);
        $this->assertSame($option['date'], $result['course']['slot']['date']);
        $this->assertSame($option['start_time'], $result['course']['slot']['start_time']);
        $this->assertSame(1, ExamJointCourseSlot::count());
    }

    public function test_picking_again_replaces_the_single_time(): void
    {
        $id = $this->jointCourse()->id;
        $this->service->saveSlot($this->school, $id, ['date' => '2099-12-07', 'start_time' => '08:00', 'end_time' => '10:00']);
        $this->service->saveSlot($this->school, $id, ['date' => '2099-12-08', 'start_time' => '10:00', 'end_time' => '12:00']);

        $this->assertSame(1, ExamJointCourseSlot::count());
        $this->assertSame('2099-12-08', $this->service->show($this->school, $id)['slot']['date']);

        $this->service->removeSlot($this->school, $id);
        $this->assertSame('not_configured', $this->service->show($this->school, $id)['status']);
    }

    public function test_a_time_outside_a_linked_exam_period_is_refused(): void
    {
        try {
            $this->service->saveSlot($this->school, $this->jointCourse()->id, ['date' => '2099-12-09', 'start_time' => '08:00', 'end_time' => '10:00']);
            $this->fail('A date outside the exam periods was saved.');
        } catch (ExamJointCourseSlotConflict $conflict) {
            $this->assertSame('date', $conflict->conflicts()[0]['type']);
        }
        $this->assertSame(0, ExamJointCourseSlot::count());
    }

    public function test_times_when_the_students_sit_another_active_exam_are_not_offered(): void
    {
        // Software Engineering's active timetable has Algorithms on Monday 08:00 – 10:00.
        DB::table('exam_timetable_versions')->insert(['id' => 'v1', 'version_number' => 1, 'label' => 'Version 1', 'exam_id' => 'exam-swe', 'school_branch_id' => 'branch-1', 'scheduler_status' => 'optimal']);
        DB::table('active_exam_timetables')->insert(['id' => 'a1', 'version_id' => 'v1', 'exam_id' => 'exam-swe', 'school_branch_id' => 'branch-1']);
        DB::table('exam_timetable_slots')->insert(['id' => 's1', 'date' => '2099-12-07', 'start_time' => '08:00:00', 'end_time' => '10:00:00', 'course_id' => 'algo', 'version_id' => 'v1', 'exam_id' => 'exam-swe', 'school_branch_id' => 'branch-1']);

        $proposal = $this->service->generate($this->school, $this->jointCourse()->id, ['duration' => 120, 'start_time' => '08:00', 'end_time' => '12:00']);
        $monday = collect($proposal['dates'])->firstWhere('date', '2099-12-07');

        $this->assertSame(['10:00'], array_column($monday['times'], 'start_time'));

        $this->expectException(ExamJointCourseSlotConflict::class);
        $this->service->saveSlot($this->school, $this->jointCourse()->id, ['date' => '2099-12-07', 'start_time' => '09:00', 'end_time' => '11:00']);
    }
}
