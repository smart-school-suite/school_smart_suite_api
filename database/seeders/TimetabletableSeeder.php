<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;

class TimetabletableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();
        $filePath = public_path('data/timetable.csv');
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);
    
            $school_branches = DB::table('school_branches')->pluck('id')->toArray();
            $level_id = DB::table('education_levels')->pluck('id')->toArray();
            $semester_id = DB::table('semesters')->pluck('id')->toArray();
            $timetableschedule = []; 
    
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);                
                $schoolBranchId = Arr::random($school_branches);
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
                $randomLevelId = Arr::random($level_id);
                $randomSpecialtyId = Arr::random($specialty);
                $randomTeacherId = Arr::random($teacher_id);
                $randomSemesterId = Arr::random($semester_id);
                $randomCourseID = Arr::random($course);
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 25);
                
                if (count($data) >= 2) {
                    $timetableschedule[] = [
                        'id' => $id, 
                        'school_branch_id' => $schoolBranchId, 
                        'day_of_week' => $data[1], 
                        'school_year' => $data[2],  
                        'start_time' => $data[3],  
                        'end_time' => $data[4],   
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                        'specialty_id' => $randomSpecialtyId,
                        'course_id' => $randomCourseID,
                        'teacher_id' => $randomTeacherId,
                        'semester_id' => $randomSemesterId,
                        'level_id' => $randomLevelId,
                    ];
                }
            }
    
            fclose($handle);
            
            Log::info('Teacher schedule Array: ', $timetableschedule);
            if (!empty($timetableschedule)) {
                DB::table('timetables')->insert($timetableschedule);
                Log::info('Inserted Timetable schedule: ' . count($timetableschedule) . ' entries.');
            } else {
                Log::warning('No Schedule  to insert.');
            }
        }
    }
}
