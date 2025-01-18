<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;

class CourseTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();
        $filePath = public_path('data/courses.csv');
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);
            $school_branches = DB::table('school_branches')->pluck('id')->toArray();
            $education_level = DB::table('education_levels')->pluck('id')->toArray();
            $semester = DB::table('semesters')->pluck('id')->toArray();
            $courses = []; 
    
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 25); 
                $code = substr(md5($uuid), 0, 5); 
                $randomSchoolBranchesId = Arr::random($school_branches);
                $randomEducationLevelsId = Arr::random($education_level);
                $randomSemesterId = Arr::random($semester);
                $department = DB::table('department')->where('school_branch_id', $randomSchoolBranchesId)->pluck('id')->toArray();
                if (!$department) {
                    Log::warning('No department found for school_branch_id: ' . $randomSchoolBranchesId);
                    continue; 
                }
                $specialty = DB::table('specialty')->where('school_branch_id', $randomSchoolBranchesId)->pluck('id')->toArray();
                if(!$specialty){
                    Log::warning(("No specialty found for the school branch id" . $randomSchoolBranchesId));
                }
                if (count($data) >= 2) {
                    $courses[] = [
                        'id' => $id, 
                        'school_branch_id' => $randomSchoolBranchesId, 
                        'course_code' => $code, 
                        'course_title' => $data[1], 
                        'credit' => $data[2], 
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                        'specialty_id' =>  Arr::random($specialty),
                        'department_id' => Arr::random($department),
                        'level_id' => $randomEducationLevelsId,
                        'semester_id' => $randomSemesterId,
                    ];
                }
            }
    
            fclose($handle);
            
            Log::info('Courses  Array: ', $courses);
    
            if (!empty($courses)) {
                DB::table('courses')->insert($courses);
                Log::info('Inserted Courses: ' . count($courses) . ' entries.');
            } else {
                Log::warning('No Courses to insert.');
            }
        }

    }
}
