<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;
class LetterGradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $this->createLetterGrades();
    }

    private function createLetterGrades()
    {
        $timestamp = now();
        $filePath = public_path("data/letter_grade.csv");
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception("CSV file not found or not readable!");
        }

        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle); // Read the header

            $letter_grade = []; // Initialize an empty array for countries

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 10);
                // Ensure the row has at least two columns
                if (count($data) >= 2) {
                    $letter_grade[] = [
                        'id' => $id, // Assign id from 1st column
                        'letter_grade' => $data[1], // Assign name from 2nd column
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                }
            }

            fclose($handle);

            // Insert the countries into the database
            if (!empty($letter_grade)) {
                DB::table('letter_grades')->insert($letter_grade);
            } else {
                Log::warning('No Grades to insert.');
            }
        }
    }
}
