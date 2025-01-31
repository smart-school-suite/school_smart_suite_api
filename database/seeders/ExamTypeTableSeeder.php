<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ExamTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::info('Exam Type Seeder has started.');
        $timestamp = now();
        $filePath = public_path("data/exam_type.csv");
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception("CSV file not found or not readable!");
        }

        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle); // Read the header
            Log::info('CSV Header: ', $header); // Log the header for debugging

            $exam_type = []; // Initialize an empty array for countries

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data); // Log current row data for debugging
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 15);
                $semester = DB::table('semesters')->pluck('id')->toArray();
                $randomSemesterID = Arr::random($semester);
                // Ensure the row has at least two columns
                if (count($data) >= 2) {
                    $exam_type[] = [
                        'id' => $id, // Assign id from 1st column
                        'exam_name' => $data[1], // Assign name from 2nd column
                        "program_name" => $data[2],
                        "semester" => $data[3],
                        "type" => $data[4],
                        "count" => $data[5],
                        'semester_id' => $randomSemesterID,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                }
            }

            fclose($handle);

            Log::info('Exam Type Array: ', $exam_type); // Log the countries array after completion

            // Insert the countries into the database
            if (!empty($exam_type)) {
                DB::table('exam_type')->insert($exam_type);
                Log::info('Inserted Exam Types: ' . count($exam_type) . ' entries.');
            } else {
                Log::warning('No Exam types to insert.');
            }
        }
    }
}
