<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;

class DepartmentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();
        $filePath = public_path('data/department.csv');
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);
            $school_branches = "d34a2c1c-8b64-46a4-b8ec-65ba77d9d620";
            $departments = [];

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 25);
                if (count($data) >= 2) {
                    $departments[] = [
                        'id' => $id,
                        'school_branch_id' => $school_branches,
                        'department_name' => $data[1],
                        'HOD' => $data[2],
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,

                    ];
                }
            }

            fclose($handle);

            Log::info('Departments Array: ', $departments);

            if (!empty($departments)) {
                DB::table('department')->insert($departments);
                Log::info('Inserted Departments: ' . count($departments) . ' entries.');
            } else {
                Log::warning('No Departments to insert.');
            }
        }

    }
}
