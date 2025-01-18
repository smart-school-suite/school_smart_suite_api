<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;

class SchoolBranchTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();
        $filePath = public_path('data/school_branches.csv');
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);
            $schools = DB::table('schools')->pluck('id')->toArray();
            $school_branches = []; 
    
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 25); 
                $randomSchoolId = Arr::random($schools);
                if (count($data) >= 2) {
                    $school_branches[] = [
                        'id' => $id, 
                        'branch_name' => $data[1], 
                        'address' => $data[2], 
                        'city' => $data[3], 
                        'state' => $data[4],  
                        'postal_code' => $data[5], 
                        'phone_one' => $data[6], 
                        'phone_two' => $data[7], 
                        'email' => $data[8], 
                        'website' => $data[9], 
                        'resit_fee' => $data[10], 
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                        'school_id' => $randomSchoolId,
                        
                    ];
                }
            }
    
            fclose($handle);
            
            Log::info('Schools Array: ', $school_branches);
    
            if (!empty($school_branches)) {
                DB::table('school_branches')->insert($school_branches);
                Log::info('Inserted schools branches: ' . count($school_branches) . ' entries.');
            } else {
                Log::warning('No Schools branches  to insert.');
            }
        }

    }
}
