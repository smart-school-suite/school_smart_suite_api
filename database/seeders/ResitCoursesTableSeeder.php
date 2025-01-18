<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;

class ResitCoursesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $timestamp = now();
    $filePath = public_path('data/resitable_courses.csv');

    if (($handle = fopen($filePath, 'r')) !== false) {
        $header = fgetcsv($handle);
        Log::info('CSV Header: ', [$header]);

        // Fetch all relevant IDs
        $school_branches = DB::table('school_branches')->pluck('id')->toArray();
        $level = DB::table("education_levels")->pluck('id')->toArray();
        $resit_courses_table = []; 

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            Log::info('Current Row Data: ', [$data]);                
            $schoolBranchId = Arr::random($school_branches);

            $exam_id = DB::table('exams')->where('school_branch_id', $schoolBranchId)->pluck('id')->toArray();
            if (empty($exam_id)) {
                Log::warning('No exams found for school branch: ' . $schoolBranchId);
                continue; 
            }

            $course_id = DB::table('courses')->where('school_branch_id', $schoolBranchId)->pluck('id')->toArray();
            if (empty($course_id)) {
                Log::warning('No courses found for school branch id: ' . $schoolBranchId);
                continue; 
            }

            $specialty = DB::table('specialty')->where('school_branch_id', $schoolBranchId)->pluck('id')->toArray();
            if (empty($specialty)) {
                Log::warning('No specialty found for school branch id: ' . $schoolBranchId);
                continue; 
            }


            $randomExamId = Arr::random($exam_id);
            $randomCourseID = Arr::random($course_id);
            $randomSpecialtyId = Arr::random($specialty);
            $randomLevelID = Arr::random($level);
            $uuid = Str::uuid()->toString();
            $id = substr(md5($uuid), 0, 25);

            if (count($data) >= 2) {
                $resit_courses_table[] = [
                    'id' => $id, 
                    'school_branch_id' => $schoolBranchId, 
                    'school_year' => $data[1],  
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                    'specialty_id' => $randomSpecialtyId,
                    'course_id' => $randomCourseID,
                    'exam_id' => $randomExamId,
                    'level_id' => $randomLevelID,
                ];
            }
        }

        fclose($handle);

        Log::info('Exam timetable Array: ', [$resit_courses_table]);
        if (!empty($resit_courses_table)) {
            DB::table('resitable_courses')->insert($resit_courses_table);
            Log::info('Inserted into Resit courses table: ' . count($resit_courses_table) . ' entries.');
        } else {
            Log::warning('No timetable to insert.');
        }
    } else {
        Log::error('Unable to open the CSV file at ' . $filePath);
    }
}
}
