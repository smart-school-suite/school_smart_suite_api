<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Str;
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

        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);

            $semesters = [];

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                $uuid = Str::uuid()->toString();

                if (count($data) >= 2) {
                    $semesters[] = [
                        'id' => $uuid,
                        'name' => $data[1],
                        'program_name' => $data[2],
                        'count' => $data[3],
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                }
            }

            fclose($handle);


            if (!empty($semesters)) {
                DB::table('semesters')->insert($semesters);
            } else {
                Log::warning('No semesters to insert.');
            }
        }
    }
}
