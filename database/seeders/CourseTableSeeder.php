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

        try {
            if (($handle = fopen($filePath, 'r')) !== false) {
                $header = fgetcsv($handle);
                Log::info('CSV Header: ', $header ?? []);

                $schoolBranchId = "d34a2c1c-8b64-46a4-b8ec-65ba77d9d620";

                // Fetch all IDs at once for efficiency
                $educationLevels = DB::table('education_levels')->pluck('id')->toArray();
                $specialties = DB::table('specialty')->where('school_branch_id', $schoolBranchId)->pluck('id')->toArray();
                $departments = DB::table('department')->where('school_branch_id', $schoolBranchId)->pluck('id')->toArray();

                // Early check for required data. Prevents looping if data doesn't exist
                if (empty($educationLevels)) {
                    Log::error('No education levels found. Seeding aborted.');
                    fclose($handle);
                    return;
                }

                if (empty($departments)) {
                    Log::warning('No departments found for school_branch_id: ' . $schoolBranchId . '. Seeding will continue without department assignment.');
                }

                if (empty($specialties)) {
                    Log::warning("No specialties found for the school branch id: " . $schoolBranchId . ". Seeding will continue without specialty assignment.");
                }
                $courses = [];

                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    try {
                        Log::info('Current Row Data: ', $data ?? []);
                        $uuid = Str::uuid()->toString();
                        $id = substr(md5($uuid), 0, 25);
                        $code = substr(md5($uuid), 0, 5);

                        $randomEducationLevelsId = Arr::random($educationLevels);

                        $randomSpecialty = !empty($specialties) ? Arr::random($specialties) : null;

                        // Use specialty id to get school semesters if specialty exist
                        $semester = $randomSpecialty ? DB::table('school_semesters')->where("school_branch_id", $schoolBranchId)->where("specialty_id", $randomSpecialty)->pluck('id')->toArray() : [];
                        $randomSemesterId = !empty($semester) ? Arr::random($semester) : null;

                        $randomDepartmentId = !empty($departments) ? Arr::random($departments) : null;

                        if (count($data) >= 3) { //Ensuring sufficient data for course title and credit.
                            $courses[] = [
                                'id' => $id,
                                'school_branch_id' => $schoolBranchId,
                                'course_code' => $code,
                                'course_title' => $data[1] ?? null, //Sanitize each fields with null coalescing operator
                                'credit' => $data[2] ?? null, //Sanitize each fields with null coalescing operator
                                'created_at' => $timestamp,
                                'updated_at' => $timestamp,
                                'specialty_id' => $randomSpecialty,
                                'department_id' => $randomDepartmentId,
                                'level_id' => $randomEducationLevelsId,
                                'semester_id' => $randomSemesterId,
                            ];
                        } else {
                            Log::warning('Skipping row due to insufficient data: ' . json_encode($data));
                        }
                    } catch (\Exception $e) {
                        Log::error('Error processing row: ' . json_encode($data) . ' - ' . $e->getMessage());
                    }
                }

                fclose($handle);

                Log::info('Courses Array: ', $courses ?? []);

                if (!empty($courses)) {
                    try {
                        DB::table('courses')->insert($courses);
                        Log::info('Inserted Courses: ' . count($courses) . ' entries.');
                    } catch (\Exception $e) {
                        Log::error('Error inserting courses into database: ' . $e->getMessage());
                    }
                } else {
                    Log::warning('No Courses to insert.');
                }
            } else {
                Log::error('Failed to open the CSV file: ' . $filePath);
            }
        } catch (\Exception $e) {
            Log::critical('Critical error occurred during the seeding process: ' . $e->getMessage());
        }
    }
}
