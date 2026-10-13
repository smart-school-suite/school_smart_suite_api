<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\Semester;
use Illuminate\Support\Str;

class ExamTypeSeeder extends Seeder
{
    public function run(): void
    {
        $this->createExamType();
    }

    private function createExamType(): void
    {
        $timestamp = now();
        $filePath = public_path("data/exam_type.csv");

        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception("CSV file not found or not readable!");
        }

        if (($handle = fopen($filePath, 'r')) === false) {
            return;
        }

        $header = fgetcsv($handle);

        $semestersByCount = Semester::pluck('id', 'count');

        $examTypes = [];

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if (count($data) < 7) {
                Log::warning('Skipping incomplete CSV row:', $data);
                continue;
            }

            $semesterId = $semestersByCount[$data[5]] ?? null;

            if (!$semesterId) {
                Log::warning("Semester with count '{$data[5]}' not found. Skipping row.", $data);
                continue;
            }

            $examTypes[] = [
                'id' => Str::uuid()->toString(),
                'exam_name' => $data[1],
                'program_name' => $data[2],
                'semester' => $data[3],
                'type' => $data[4],
                'description' => $data[6],
                'semester_id' => $semesterId,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        fclose($handle);

        if (empty($examTypes)) {
            Log::warning('No Exam types to insert.');
            return;
        }

        DB::table('exam_types')->upsert(
            $examTypes,
            ['exam_name', 'program_name', 'semester_id', 'type'],
            ['description', 'semester', 'updated_at']
        );

        Log::info('Upserted ' . count($examTypes) . ' exam types.');
    }
}
