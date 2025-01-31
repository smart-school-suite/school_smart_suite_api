<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class StudentBatchTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $timestamp = now();
         $filePath = public_path('data/student_batch.csv');
         if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);
            $school_branches = "c3f466af-a21d-4682-9df0-6d9eff5732cc";
            $student_batches = [];

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 25);
                if (count($data) >= 2) {
                    $student_batches[] = [
                        'id' => $id,
                        'school_branch_id' => $school_branches,
                        'name' => $data[1],
                        'graduation_date' => $data[2],
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                }
            }

            fclose($handle);

            Log::info('Student batches Array: ', $student_batches);

            if (!empty($student_batches)) {
                DB::table('student_batch')->insert($student_batches);
                Log::info('Inserted student batches: ' . count($student_batches) . ' entries.');
            } else {
                Log::warning('No Student batches to insert.');
            }
        }
    }
}
