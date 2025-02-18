<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;


class ExamTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();
        $filePath = public_path('data/exams.csv');
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);
            $exam_type = DB::table('exam_type')->pluck('id')->toArray();
            $levels = DB::table("education_levels")->pluck('id')->toArray();
            $semesters = DB::table("school_semesters")->pluck('id')->toArray();
            $exams = [];

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 25);
                $randomSchoolBranchesId = "d34a2c1c-8b64-46a4-b8ec-65ba77d9d620";
                $randomExamtypeID = Arr::random($exam_type);
                $randomLevelsID = Arr::random($levels);
                $randomSemesterID = Arr::random($semesters);
                $specialty = DB::table("specialty")->where("school_branch_id", $randomSchoolBranchesId)->pluck("id")->toArray();
                $studentBatchId = DB::table('student_batch')->where("school_branch_id", $randomSchoolBranchesId)->pluck('id')->toArray();
                if (count($data) >= 2) {
                    $exams[] = [
                        'id' => $id,
                        'school_branch_id' => $randomSchoolBranchesId,
                        'start_date' => $data[1],
                        'end_date' => $data[2],
                        'weighted_mark' => $data[3],
                        'school_year' => $data[4],
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                        'exam_type_id' => $randomExamtypeID,
                        'level_id' => $randomLevelsID,
                        'semester_id' => $randomSemesterID,
                        'student_batch_id' => Arr::random($studentBatchId),
                        'specialty_id' => Arr::random($specialty),
                    ];
                }
            }

            fclose($handle);

            Log::info('Exam Array: ', $exams);

            if (!empty($exams)) {
                DB::table('exams')->insert($exams);
                Log::info('Inserted exams: ' . count($exams) . ' entries.');
            } else {
                Log::warning('No exams to insert.');
            }
        }

    }
}
