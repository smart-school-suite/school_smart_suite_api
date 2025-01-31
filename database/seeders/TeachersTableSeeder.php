<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeachersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();
        $filePath = public_path('data/teacher.csv');
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);
            $school_branches = "c3f466af-a21d-4682-9df0-6d9eff5732cc";
            $Teachers = [];

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 25);
                $password = substr(md5($uuid), 0, 15);
                if (count($data) >= 2) {
                    $Teachers[] = [
                        'id' => $id,
                        'school_branch_id' => $school_branches,
                        'name' => $data[1],
                        'password' => Hash::make($password),
                        'phone_one' => $data[2],
                        'phone_two' => $data[3],
                        'email' => $data[4],
                        'date_of_birth' => $data[5],
                        'address' => $data[6],
                        'employment_status' => $data[7],
                        'hire_date' => $data[8],
                        'emergency_contact_name' => $data[9],
                        'emergency_contact_phone' => $data[10],
                        'last_performance_review' => $data[11],
                        'highest_qualification' => $data[12],
                        'field_of_study' => $data[13],
                        'city' => $data[14],
                        'cultural_background' => $data[15],
                        'religion' => $data[16],
                        'years_experience' => $data[17],
                        'salary' => $data[18],
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }
            }

            fclose($handle);

            Log::info('Teacher Array: ', $Teachers);

            if (!empty($Teachers)) {
                DB::table('teacher')->insert($Teachers);
                Log::info('Inserted teachers: ' . count($Teachers) . ' entries.');
            } else {
                Log::warning('No teachers to insert.');
            }
        }

    }
}
