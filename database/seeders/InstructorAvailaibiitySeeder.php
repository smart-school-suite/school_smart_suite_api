<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;
class InstructorAvailaibiitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();
        $filePath = public_path('data/instructor_availabilities.csv');
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);

            $schoolBranchId = "c3f466af-a21d-4682-9df0-6d9eff5732cc";
            $level_id = DB::table('education_levels')->pluck('id')->toArray();
            $semester_id = DB::table('semesters')->pluck('id')->toArray();
            $teacher_avialaibility = [];

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                $teacher_id = DB::table('teacher')->where("school_branch_id", $schoolBranchId)->pluck('id')->toArray();
                if(!$teacher_id){
                    Log::warning("teacher not found for school branch id" . $schoolBranchId);
                }
                $specialty = DB::table('specialty')->where('school_branch_id', $schoolBranchId)->pluck('id')->toArray();
                if(!$specialty){
                    Log::warning('No scpecailty found for school branch id' . $schoolBranchId);
                }
                $randomLevelId = Arr::random($level_id);
                $randomSpecialtyId = Arr::random($specialty);
                $randomTeacherId = Arr::random($teacher_id);
                $randomSemesterId = Arr::random($semester_id);
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 25);

                if (count($data) >= 2) {
                    $teacher_avialaibility[] = [
                        'id' => $id,
                        'school_branch_id' => $schoolBranchId,
                        'day_of_week' => $data[1],
                        'start_time' => $data[2],
                        'end_time' => $data[3],
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                        'level_id' => $randomLevelId,
                        'semester_id' => $randomSemesterId,
                        'teacher_id' => $randomTeacherId,
                        'specialty_id' => $randomSpecialtyId,
                    ];
                }
            }

            fclose($handle);

            Log::info('Teacher schedule Array: ', $teacher_avialaibility);
            if (!empty($teacher_avialaibility)) {
                DB::table('instructor_availabilities')->insert($teacher_avialaibility);
                Log::info('Inserted Teacher schedule: ' . count($teacher_avialaibility) . ' entries.');
            } else {
                Log::warning('No Schedule  to insert.');
            }
        }
    }
}
