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

    private function createLetterGrades(): void
    {
        $timestamp = now();
        $filePath = public_path("data/letter_grade.csv");

        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception("CSV file not found or not readable!");
        }

        if (($handle = fopen($filePath, 'r')) === false) {
            return;
        }

        $header = fgetcsv($handle);

        $letterGrades = [];

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if (count($data) < 2) {
                Log::warning('Skipping incomplete CSV row:', $data);
                continue;
            }

            $letterGrades[] = [
                'id' => Str::uuid()->toString(),
                'letter_grade' => $data[1],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        fclose($handle);

        if (empty($letterGrades)) {
            Log::warning('No Grades to insert.');
            return;
        }

        DB::table('letter_grades')->upsert(
            $letterGrades,
            ['letter_grade'],
            ['updated_at']
        );

        Log::info('Upserted ' . count($letterGrades) . ' letter grades.');
    }
}
