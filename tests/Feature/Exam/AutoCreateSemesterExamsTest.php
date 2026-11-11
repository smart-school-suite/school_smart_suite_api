<?php

namespace Tests\Feature\Exam;

use App\Jobs\Exam\AutoCreateSemesterExamsJob;
use App\Jobs\Exam\CreateExamCandidateJob;
use App\Models\Exam\Exam;
use App\Models\SchoolBranchSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AutoCreateSemesterExamsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake([CreateExamCandidateJob::class, \App\Jobs\ExamJointCourse\CreateExamJointCourseJob::class]);

        DB::table('semesters')->insert([
            ['id' => 'sem-1', 'name' => 'First Semester', 'count' => 1, 'program_name' => 'FIS'],
            ['id' => 'sem-2', 'name' => 'Second Semester', 'count' => 2, 'program_name' => 'SES'],
        ]);
        foreach ([
            ['ca-1', 'sem-1', 'first', 'ca'], ['exam-1', 'sem-1', 'first', 'exam'], ['resit-1', 'sem-1', 'first', 'resit'],
            ['ca-2', 'sem-2', 'second', 'ca'], ['exam-2', 'sem-2', 'second', 'exam'],
        ] as [$id, $semesterId, $semester, $type]) {
            DB::table('exam_types')->insert(['id' => $id, 'exam_name' => $id, 'semester' => $semester, 'type' => $type, 'program_name' => $id, 'semester_id' => $semesterId]);
        }
        DB::table('specialties')->insert(['id' => 'swe-1', 'specialty_name' => 'Software Engineering', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'dep', 'level_id' => 'lvl-1']);
        DB::table('school_academic_years')->insert(['id' => 'sy-1', 'start_date' => '2099-09-01', 'end_date' => '2100-06-30', 'specialty_id' => 'swe-1', 'system_academic_year_id' => 'year', 'school_branch_id' => 'branch-1']);
        DB::table('school_semesters')->insert(['id' => 'ss-1', 'start_date' => '2099-09-15', 'end_date' => '2100-01-31', 'school_year_id' => 'sy-1', 'semester_id' => 'sem-1', 'school_branch_id' => 'branch-1', 'timetable_published' => false]);
        DB::table('students')->insert(['id' => 'stu-1', 'name' => 'S', 'first_name' => 'S', 'last_name' => 'S', 'email' => 's@example.com', 'password' => 'x', 'username' => 's', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe-1', 'guardian_id' => 'g', 'student_batch_id' => 'b', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);

        DB::table('setting_categories')->insert(['id' => 'cat', 'name' => 'Exam Settings', 'key' => 'setting.category.exam']);
        foreach (['exam.auto_create' => ['boolean', true], 'exam.ca_default_max_score' => ['decimal', 40], 'exam.exam_default_max_score' => ['decimal', 60]] as $key => [$type, $value]) {
            DB::table('setting_definations')->insert(['id' => "def-$key", 'name' => $key, 'key' => $key, 'data_type' => $type, 'setting_category_id' => 'cat']);
            SchoolBranchSetting::create(['school_branch_id' => 'branch-1', 'setting_defination_id' => "def-$key", 'value' => $value]);
        }
    }

    private function run_job(): void
    {
        (new AutoCreateSemesterExamsJob('branch-1', 'ss-1'))->handle();
    }

    private function set(string $key, mixed $value): void
    {
        SchoolBranchSetting::where('setting_defination_id', "def-$key")->first()->update(['value' => $value]);
    }

    public function test_it_creates_the_semesters_ca_and_exam_with_the_default_scores(): void
    {
        $this->run_job();

        $exams = Exam::orderBy('max_score')->get();
        $this->assertSame(['ca-1', 'exam-1'], $exams->pluck('exam_type_id')->all());
        $this->assertSame([40.0, 100.0], $exams->pluck('max_score')->all(), 'The exam maximum includes the CA maximum.');
        $this->assertSame('2099-09-15', $exams[0]->start_date->toDateString());
        $this->assertSame('2100-01-31', $exams[0]->end_date->toDateString());
        Bus::assertDispatchedTimes(CreateExamCandidateJob::class, 2);
    }

    public function test_nothing_is_created_when_disabled_or_without_students(): void
    {
        $this->set('exam.auto_create', false);
        $this->run_job();
        $this->assertSame(0, Exam::count());

        $this->set('exam.auto_create', true);
        DB::table('students')->delete();
        $this->run_job();
        $this->assertSame(0, Exam::count());
    }

    public function test_existing_exams_are_not_duplicated_and_the_existing_ca_is_used(): void
    {
        DB::table('exams')->insert(['id' => 'existing-ca', 'school_branch_id' => 'branch-1', 'exam_type_id' => 'ca-1', 'school_year_id' => 'sy-1', 'max_score' => 25]);

        $this->run_job();
        $this->run_job();

        $this->assertSame(1, Exam::where('exam_type_id', 'ca-1')->count());
        $this->assertSame(85.0, Exam::where('exam_type_id', 'exam-1')->value('max_score'));
        Bus::assertDispatchedTimes(CreateExamCandidateJob::class, 1);
    }
}
