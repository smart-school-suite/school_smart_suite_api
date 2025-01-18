<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;

class StudentMarksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $timestamp = now();
         $filePath = public_path('data/exam_scores.csv');
         if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);
            $school_branches = DB::table('school_branches')->pluck('id')->toArray();
            $level_id = DB::table("education_levels")->pluck('id')->toArray();
            $exam_scores = []; 
            
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 25); 
                $schoolBranchId = Arr::random($school_branches);
                $specialty = DB::table('specialty')->where('school_branch_id', $schoolBranchId)->pluck('id')->toArray();
                if(!$specialty){
                    Log::warning('No scpecailty found for school branch id' . $schoolBranchId);
                }
                $student_id = DB::table('student')->where("school_branch_id", $schoolBranchId)->pluck('id')->toArray();
                if(!$student_id){
                    Log::warning("No student found for this school branch id" . $schoolBranchId);
                }
                $courses_id = DB::table('courses')->where("school_branch_id", $schoolBranchId)->pluck('id')->toArray();
                if(!$courses_id){
                     Log::warning("No courses found in this department" . $schoolBranchId);
                }
                $exam_id = DB::table('exams')->where("school_branch_id", $school_branches)->pluck('id')->toArray();
                if($exam_id){
                     Log::warning("No exam found in this school branch id" . $schoolBranchId);
                }
                $student_batch_id = DB::table('student_batch')->where("school_branch_id", $schoolBranchId)->pluck('id')->toArray();
                if(!$student_batch_id){
                    Log::warning("No student batch found in this school branch id", $schoolBranchId);
                }
               
                if (count($data) >= 2) {
                    $exam_scores[] = [
                        'id' => $id, 
                        'school_branch_id' => $schoolBranchId,
                        'score' => $data[3], 
                        'grade' => $data[1], 
                        'school_year' => $data[2], 
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                        'student_id' => Arr::random($student_id), 
                        'courses_id' => Arr::random($courses_id), 
                        'exam_id' => Arr::random($exam_id), 
                        'level_id' => Arr::random($level_id), 
                        'specialty_id' => Arr::random($specialty), 
                        'student_batch_id' => Arr::random($student_batch_id), 
                    ];
                }
            }
    
            fclose($handle);
            
            Log::info('Studdent scores Array: ', $exam_scores);
    
            if (!empty($exam_scores)) {
                DB::table('marks')->insert($exam_scores);
                Log::info('Inserted student scores: ' . count($exam_scores) . ' entries.');
            } else {
                Log::warning('No Student scores to insert.');
            }
        }
    }
}
