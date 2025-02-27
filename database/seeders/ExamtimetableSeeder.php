<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;

class ExamtimetableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();
        $filePath = public_path('data/examtimetable.csv');
        $faker = Faker::create(); // Initialize Faker

        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);

            // Fetch all relevant IDs
            $schoolBranchId = "d34a2c1c-8b64-46a4-b8ec-65ba77d9d620";
            $exam_timetable = [];

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                $exam_id = DB::table('exams')->where('school_branch_id', $schoolBranchId)->pluck('id')->toArray();
                if (!$exam_id) {
                    Log::warning('No exams found for this school: ' . $schoolBranchId);
                    continue;
                }
                $course_id = DB::table('courses')->where('school_branch_id', $schoolBranchId)->pluck('id')->toArray();
                if (!$course_id) {
                    Log::warning('No courses found for school branch id' . $schoolBranchId);
                }
                $specialty = DB::table('specialty')->where('school_branch_id', $schoolBranchId)->pluck('id')->toArray();
                if (!$specialty) {
                    Log::warning('No specialty found for school branch id' . $schoolBranchId);
                }

                $educationLevel = DB::table('education_levels')->pluck('id')->toArray();
                $studentBatch = DB::table('student_batch')->where("school_branch_id", $schoolBranchId)->pluck('id')->toArray();

                $randomExamid = Arr::random($exam_id);
                $randomcourseID = Arr::random($course_id);
                $randomSpecialtyId = Arr::random($specialty);
                $randomEducationLevel = Arr::random($educationLevel);
                $randomStudentBatch = Arr::random($studentBatch);
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 25);

                // Use Faker to generate a random date for the "day" field
                $randomDay = $faker->dateTimeBetween('-1 year', '+1 year')->format('Y-m-d');

                if (count($data) >= 2) {
                    $exam_timetable[] = [
                        'id' => $id,
                        'school_branch_id' => $schoolBranchId,
                        'day' => $randomDay, // Use Faker-generated date
                        'start_time' => $data[2],
                        'end_time' => $data[3],
                        'duration' => $data[4],
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                        'exam_id' => $randomExamid,
                        'level_id' => $randomEducationLevel,
                        'course_id' => $randomcourseID,
                        'student_batch_id' => $randomStudentBatch,
                        'specialty_id' => $randomSpecialtyId,
                    ];
                }
            }

            fclose($handle);

            Log::info('Exam timetable Array: ', $exam_timetable);
            if (!empty($exam_timetable)) {
                DB::table('examtimetable')->insert($exam_timetable);
                Log::info('Inserted Examtimetable: ' . count($exam_timetable) . ' entries.');
            } else {
                Log::warning('No Timetable to insert.');
            }
        }
    }
}
