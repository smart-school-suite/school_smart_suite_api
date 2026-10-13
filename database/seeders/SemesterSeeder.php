<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class SemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createSemesters();
    }

    private function createSemesters(): void
    {
        $timestamp = now();
        $filePath = public_path("data/semesters.csv");

        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception("CSV file not found or not readable!");
        }

        if (($handle = fopen($filePath, 'r')) === false) {
            return;
        }

        $header = fgetcsv($handle);
        $semesters = [];

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if (count($data) < 4) {
                Log::warning('Skipping incomplete CSV row:', $data);
                continue;
            }

            $semesters[] = [
                'id' => Str::uuid()->toString(),
                'name' => $data[1],
                'program_name' => $data[2],
                'count' => $data[3],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        fclose($handle);

        if (empty($semesters)) {
            Log::warning('No semesters to insert.');
            return;
        }

        DB::table('semesters')->upsert(
            $semesters,
            ['program_name'],
            ['name', 'count', 'updated_at']
        );

        Log::info('Upserted ' . count($semesters) . ' semesters.');
    }
}
