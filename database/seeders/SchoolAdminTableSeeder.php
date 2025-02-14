<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class SchoolAdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();
        $filePath = public_path('data/school_admin.csv');
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);
            $schoolBranchId = "d34a2c1c-8b64-46a4-b8ec-65ba77d9d620";
            $school_admin = [];

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 25);
                if (count($data) >= 2) {
                    $school_admin[] = [
                        'id' => $id,
                        'school_branch_id' => $schoolBranchId,
                        'name' => $data[1],
                        'email' => $data[2],
                        'password' => Hash::make("Keron484$"),
                        'role' => $data[4],
                        'date_of_birth' => $data[5],
                        'address' => $data[6],
                        'employment_status' => $data[7],
                        'hire_date' => $data[8],
                        'emergency_contact_name' => $data[9],
                        'emergency_contact_phone' => $data[10],
                        'last_performance_review' => $data[11],
                        'work_location' => $data[12],
                        'position' => $data[13],
                        'highest_qualification' => $data[14],
                        'field_of_study' => $data[15],
                        'last_login_at' => $data[16],
                        'cultural_background' => $data[17],
                        'religion' => $data[18],
                        'years_experience' => $data[19],
                        'salary' => $data[20],
                        'city' => $data[21],
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                }
            }

            fclose($handle);

            Log::info('School admin  Array: ', $school_admin);

            if (!empty($school_admin)) {
                DB::table('school_admin')->insert($school_admin);
                Log::info('Inserted School Admin: ' . count($school_admin) . ' entries.');
            } else {
                Log::warning('No School admin to insert.');
            }
        }

    }
}
