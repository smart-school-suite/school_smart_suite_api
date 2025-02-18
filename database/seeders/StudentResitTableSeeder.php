<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;

class StudentResitTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();
        $filePath = public_path('data/student_resit.csv');
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);

            $level_id = DB::table('education_levels')->pluck('id')->toArray();
            $student_resit_list = [];

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                $schoolBranchId = "d34a2c1c-8b64-46a4-b8ec-65ba77d9d620";
                $teacher_id = DB::table('teacher')->where("school_branch_id", $schoolBranchId)->pluck('id')->toArray();
                if(!$teacher_id){
                    Log::warning("teacher not found for school branch id" . $schoolBranchId);
                }
                $specialty = DB::table('specialty')->where('school_branch_id', $schoolBranchId)->pluck('id')->toArray();
                if(!$specialty){
                    Log::warning('No scpecailty found for school branch id' . $schoolBranchId);
                }
                $course = DB::table("courses")->where("school_branch_id", $schoolBranchId)->pluck('id')->toArray();
                if(!$course){
                    Log::warning('No course found for school branch id' . $schoolBranchId);
                }
                $student = DB::table("student")->where("school_branch_id", $schoolBranchId)->pluck('id')->toArray();
                if(!$student){
                    Log::warning('No student found for school branch id' . $schoolBranchId);
                }
                $exam = DB::table("exams")->where("school_branch_id", $schoolBranchId)->pluck('id')->toArray();
                if(!$exam){
                    Log::warning('No student found for school branch id' . $schoolBranchId);
                }
                $studentBatchId = DB::table('student_batch')->where("school_branch_id", $schoolBranchId)->pluck('id')->toArray();
                $randomLevelId = Arr::random($level_id);
                $randomSpecialtyId = Arr::random($specialty);
                $randomCourseID = Arr::random($course);
                $randomStudent = Arr::random($student);
                $randomExam = Arr::random($exam);
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 25);

                if (count($data) >= 2) {
                    $student_resit_list[] = [
                        'id' => $id,
                        'exam_status' => $data[1],
                        'paid_status' => $data[2],
                        'resit_fee' => $data[3],
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                        'school_branch_id' => $schoolBranchId,
                        'specialty_id' => $randomSpecialtyId,
                        'course_id' => $randomCourseID,
                        'exam_id' => $randomExam,
                        'level_id' => $randomLevelId,
                        'student_id' => $randomStudent,
                        'student_batch_id' => Arr::random($studentBatchId)
                    ];
                }
            }

            fclose($handle);

            Log::info('Teacher schedule Array: ', $student_resit_list);
            if (!empty($student_resit_list)) {
                DB::table('student_resit')->insert($student_resit_list);
                Log::info('Inserted Timetable schedule: ' . count($student_resit_list) . ' entries.');
            } else {
                Log::warning('No Schedule  to insert.');
            }
        }
    }
}
