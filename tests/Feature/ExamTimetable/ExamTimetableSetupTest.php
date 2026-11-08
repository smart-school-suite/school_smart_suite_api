<?php

namespace Tests\Feature\ExamTimetable;

use App\Models\Teacher;
use App\Services\ExamTimetable\ExamTimetableSetupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExamTimetableSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_constraint_list_covers_every_configurable_exam_constraint(): void
    {
        $keys = collect(app(ExamTimetableSetupService::class)->constraints())->flatMap(fn($category) => array_column($category['constraints'], 'key'))->all();

        foreach (['operational_period', 'session_duration', 'student_daily_load_range', 'course_requested_slot', 'requested_assignment', 'hall_requested_slot', 'invigilator_requested_slot', 'candidates_per_invigilator'] as $key) {
            $this->assertContains($key, $keys);
        }
        $this->assertNotContains('course_placement', $keys);
    }

    public function test_scheduling_data_lists_courses_exam_halls_and_what_invigilators_teach(): void
    {
        DB::table('exam_types')->insert(['id' => 'final', 'exam_name' => 'Final', 'semester' => 'first', 'type' => 'exam', 'program_name' => 'HND', 'semester_id' => 'sem-1']);
        DB::table('school_academic_years')->insert(['id' => 'sy', 'start_date' => '2099-09-01', 'end_date' => '2100-06-30', 'specialty_id' => 'swe', 'system_academic_year_id' => 'y', 'school_branch_id' => 'b']);
        DB::table('exams')->insert(['id' => 'exam', 'school_branch_id' => 'b', 'exam_type_id' => 'final', 'school_year_id' => 'sy', 'start_date' => '2099-12-07', 'end_date' => '2099-12-11']);
        DB::table('courses')->insert([
            ['id' => 'algo', 'course_code' => 'A', 'course_title' => 'Algorithms', 'credit' => 3, 'school_branch_id' => 'b', 'semester_id' => 'sem-1'],
            ['id' => 'other', 'course_code' => 'O', 'course_title' => 'Second semester', 'credit' => 3, 'school_branch_id' => 'b', 'semester_id' => 'sem-2'],
        ]);
        DB::table('course_specialties')->insert([
            ['id' => 'cs1', 'course_id' => 'algo', 'specialty_id' => 'swe', 'school_branch_id' => 'b'],
            ['id' => 'cs2', 'course_id' => 'other', 'specialty_id' => 'swe', 'school_branch_id' => 'b'],
        ]);
        DB::table('hall_types')->insert(['id' => 'ht', 'name' => 'Exam', 'key' => 'exam.hall']);
        DB::table('halls')->insert(['id' => 'h1', 'name' => 'Big', 'capacity' => 100, 'status' => 'available', 'school_branch_id' => 'b']);
        DB::table('school_hall_types')->insert(['id' => 'sht', 'hall_id' => 'h1', 'hall_type_id' => 'ht', 'school_branch_id' => 'b']);
        DB::table('exam_invigilators')->insert(['id' => 'inv', 'school_branch_id' => 'b', 'exam_id' => 'exam', 'actorable_id' => 't1', 'actorable_type' => Teacher::class]);
        DB::table('teacher_course_preferences')->insert(['id' => 'p', 'teacher_id' => 't1', 'course_id' => 'algo', 'school_branch_id' => 'b']);

        $data = app(ExamTimetableSetupService::class)->schedulingData((object) ['id' => 'b'], 'exam');

        $this->assertSame(['algo'], array_column($data['courses'], 'course_id'));
        $this->assertSame(['h1'], array_column($data['halls'], 'hall_id'));
        $this->assertSame(['algo'], $data['invigilators'][0]['courses_taught']);
        $this->assertSame('2099-12-07', $data['exam']['start_date']);
    }
}
