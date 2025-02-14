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

        try {
            if (($handle = fopen($filePath, 'r')) !== false) {
                $header = fgetcsv($handle);
                Log::info('CSV Header: ', $header ?? []);

                $schoolBranchId = "d34a2c1c-8b64-46a4-b8ec-65ba77d9d620";
                $level_id = DB::table('education_levels')->pluck('id')->toArray();
                $teacher_avialaibility = [];

                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    try {
                        Log::info('Current Row Data: ', $data ?? []);
                        $teacher_id = DB::table('teacher')->where("school_branch_id", $schoolBranchId)->pluck('id')->toArray();
                        if (empty($teacher_id)) {
                            Log::error('No teachers found for school branch ID: ' . $schoolBranchId);
                            continue; // Skip this row if no teachers are found
                        }
                        $randomTeacherId = Arr::random($teacher_id);
                        $teacherPref = DB::table('teacher_specailty_preference')->where("teacher_id", $randomTeacherId)->where("school_branch_id", $schoolBranchId)->pluck("specialty_id")->toArray();

                        if (empty($teacherPref)) {
                            Log::warning('No specialty preferences found for teacher ID: ' . $randomTeacherId . ' and school branch ID: ' . $schoolBranchId);
                            continue; // Skip if no specialty preferences are found for this teacher
                        }
                        $randomSpecialtyId = Arr::random($teacherPref);
                        $semester_id = DB::table('school_semesters')->where("specialty_id", $randomSpecialtyId)->pluck('id')->toArray(); //semester schould depend on the instructor preference

                        if (empty($semester_id)) {
                            Log::warning('No semesters found for specialty ID: ' . $randomSpecialtyId);
                            continue; // Skip if no semesters are found for the specialty
                        }

                        $randomLevelId = Arr::random($level_id);
                        $randomSemesterId = Arr::random($semester_id);
                        $uuid = Str::uuid()->toString();

                        if (count($data) >= 2) {
                            $teacher_avialaibility[] = [
                                'id' => $uuid,
                                'school_branch_id' => $schoolBranchId,
                                'day_of_week' => $data[1] ?? null,
                                'start_time' => $data[2] ?? null,
                                'end_time' => $data[3] ?? null,
                                'created_at' => $timestamp,
                                'updated_at' => $timestamp,
                                'level_id' => $randomLevelId,
                                'semester_id' => $randomSemesterId,
                                'teacher_id' => $randomTeacherId,
                                'specialty_id' => $randomSpecialtyId,
                            ];
                        } else {
                            Log::warning('Skipping row due to insufficient data: ' . json_encode($data));
                        }
                    } catch (\Exception $e) {
                        Log::error('Error processing row: ' . json_encode($data) . ' - ' . $e->getMessage());
                    }
                }

                fclose($handle);

                Log::info('Teacher schedule Array: ', $teacher_avialaibility ?? []);
                if (!empty($teacher_avialaibility)) {
                    try {
                        DB::table('instructor_availabilities')->insert($teacher_avialaibility);
                        Log::info('Inserted Teacher schedule: ' . count($teacher_avialaibility) . ' entries.');
                    } catch (\Exception $e) {
                        Log::error('Error inserting data into instructor_availabilities table: ' . $e->getMessage());
                    }
                } else {
                    Log::warning('No Schedule to insert.');
                }
            } else {
                Log::error('Failed to open the CSV file: ' . $filePath);
            }
        } catch (\Exception $e) {
            Log::critical('Critical error occurred during the seeding process: ' . $e->getMessage());
        }
    }
}
