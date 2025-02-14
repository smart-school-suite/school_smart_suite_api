<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;

class SpecialityTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();
        $filePath = public_path('data/specialty.csv');
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);
            $school_branches = "d34a2c1c-8b64-46a4-b8ec-65ba77d9d620";
            $department = DB::table('department')->pluck('id')->toArray();
            $education_level = DB::table('education_levels')->pluck('id')->toArray();
            $specialties = [];

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 25);
                $randomDepartmentsId = Arr::random($department);
                $randomEducationLevelsId = Arr::random($education_level);
                if (count($data) >= 2) {
                    $specialties[] = [
                        'id' => $id,
                        'school_branch_id' => $school_branches,
                        'specialty_name' => $data[1],
                        'registration_fee' => $data[2],
                        'school_fee' => $data[3],
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                        'department_id' => $randomDepartmentsId,
                        'level_id' => $randomEducationLevelsId,
                    ];
                }
            }

            fclose($handle);

            Log::info('Specialty Array: ', $specialties);

            if (!empty($specialties)) {
                DB::table('specialty')->insert($specialties);
                Log::info('Inserted Specailties: ' . count($specialties) . ' entries.');
            } else {
                Log::warning('No Specialties to insert.');
            }
        }

    }
}
