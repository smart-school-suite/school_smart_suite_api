<?php

namespace Tests\Feature\Resit;

use App\Exceptions\AppException;
use App\Services\Resit\ResitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentResitDetailsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('system_academic_years')->insert(['id' => 'sys', 'name' => '2026/2027', 'year_start' => 2026, 'year_end' => 2027]);
        DB::table('levels')->insert(['id' => 'lvl-2', 'name' => 'Level 200', 'level' => 200, 'program_name' => 'l2', 'level_type_id' => 't']);
        DB::table('specialties')->insert(['id' => 'swe', 'specialty_name' => 'Software Engineering', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => 'lvl-2']);
        DB::table('school_academic_years')->insert(['id' => 'sy', 'start_date' => '2026-09-01', 'end_date' => '2027-07-31', 'specialty_id' => 'swe', 'system_academic_year_id' => 'sys', 'school_branch_id' => 'branch-1']);
        DB::table('semesters')->insert(['id' => 'first', 'name' => 'First Semester', 'count' => 1, 'program_name' => 'HND']);
        DB::table('exam_types')->insert(['id' => 'exam-1', 'exam_name' => 'First Semester Exam', 'semester' => 'first', 'type' => 'exam', 'program_name' => 'e1', 'semester_id' => 'first']);
        DB::table('exams')->insert(['id' => 'exam', 'exam_type_id' => 'exam-1', 'school_year_id' => 'sy', 'start_date' => '2027-01-10', 'end_date' => '2027-01-20', 'max_score' => 100, 'school_branch_id' => 'branch-1']);
        DB::table('students')->insert(['id' => 'stu', 'name' => 'Ada Lovelace', 'first_name' => 'Ada', 'last_name' => 'Lovelace', 'email' => 'ada@example.com', 'password' => 'x', 'username' => 'ada', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe', 'guardian_id' => 'g', 'student_batch_id' => 'b', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);
        DB::table('courses')->insert(['id' => 'course', 'course_code' => 'SWE201', 'course_title' => 'Data Structures', 'credit' => 4, 'school_branch_id' => 'branch-1', 'semester_id' => 'first']);
        DB::table('student_resits')->insert(['id' => 'resit', 'payment_status' => 'unpaid', 'fee' => 5000, 'attempts' => 1, 'iscarry_over' => 1, 'school_branch_id' => 'branch-1', 'course_id' => 'course', 'exam_id' => 'exam', 'student_id' => 'stu']);
        DB::table('resit_fee_transactions')->insert(['id' => 't1', 'transaction_id' => 'TX1', 'amount' => 2000, 'payment_method' => 'cash', 'resitfee_id' => 'resit', 'school_branch_id' => 'branch-1', 'created_at' => '2026-11-10 09:00:00']);
    }

    public function test_the_details_describe_the_student_course_exam_and_fee(): void
    {
        $details = app(ResitService::class)->getStudentResitDetails((object) ['id' => 'branch-1'], 'resit');

        $this->assertSame(['Ada Lovelace', 'ada'], [$details['student_name'], $details['student_username']]);
        $this->assertSame(['Data Structures', 'SWE201', 4.0], [$details['course_title'], $details['course_code'], $details['course_credit']]);
        $this->assertSame(['First Semester Exam', 'First Semester', '2026/2027'], [$details['exam_name'], $details['semester'], $details['academic_year']]);
        $this->assertSame(['Software Engineering', 'Level 200', 200], [$details['specialty_name'], $details['level_name'], (int) $details['level_number']]);
        $this->assertSame([5000.0, 2000.0, 3000.0, 1], [$details['resit_fee'], $details['amount_paid'], $details['balance'], $details['transactions']]);
        $this->assertTrue($details['is_carry_over']);
        $this->assertNull($details['resit_score'], 'Not evaluated yet.');
    }

    public function test_a_resit_from_another_branch_is_not_found(): void
    {
        $this->expectException(AppException::class);
        app(ResitService::class)->getStudentResitDetails((object) ['id' => 'branch-2'], 'resit');
    }
}
