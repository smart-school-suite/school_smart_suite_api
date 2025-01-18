<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class ParentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();
        $filePath = public_path('data/parents.csv');
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);
            $school_branches = DB::table('school_branches')->pluck('id')->toArray();
            $parents = []; 
            
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 25);
                $password = substr(md5($uuid), 0, 8);
                $randomSchoolBranchesId = Arr::random($school_branches);
                if (count($data) >= 2) {
                    $parents[] = [
                        'id' => $id, 
                        'school_branch_id' => $randomSchoolBranchesId, 
                        'name' => $data[1], 
                        'address' => $data[2], 
                        'password' => Hash::make($password), 
                        'language_preference' => $data[4], 
                        'phone_one' => $data[5], 
                        'phone_two' => $data[6], 
                        'email' => $data[7], 
                        'occupation' => $data[8], 
                        'relationship_to_student' => $data[9], 
                        'preferred_contact_method' => $data[10], 
                        'marital_status' => $data[11], 
                        'preferred_language_of_communication' => $data[12], 
                        'cultural_background' => $data[13], 
                        'religion' => $data[14], 
                        'referral_source' => $data[15],  
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                }
            }
    
            fclose($handle);
            
            Log::info('Parents  Array: ', $parents);
    
            if (!empty($parents)) {
                DB::table('parents')->insert($parents);
                Log::info('Inserted Parents: ' . count($parents) . ' entries.');
            } else {
                Log::warning('No Parents to insert.');
            }
        }

    }
}
