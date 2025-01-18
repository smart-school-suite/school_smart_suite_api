<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SchoolTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $timestamp = now();
         $filePath = public_path('data/schools.csv');
         if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);
            $countries = DB::table('country')->pluck('id')->toArray();
            $schools = []; 
    
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 20); 
                $randomCountryId = Arr::random($countries);
                if (count($data) >= 2) {
                    $schools[] = [
                        'id' => $id, 
                        'country_id' => $randomCountryId,
                        'name' => $data[1], 
                        'address' => $data[2], 
                        'city' => $data[3], 
                        'state' => $data[4],  
                        'type' => $data[5], 
                        'established_year' => $data[6], 
                        'director_name' => $data[7], 
                        'MAX_GPA' => $data[8], 
                        'motor' => $data[9], 
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                }
            }
    
            fclose($handle);
            
            Log::info('Schools Array: ', $countries);
    
            if (!empty($schools)) {
                DB::table('schools')->insert($schools);
                Log::info('Inserted schools: ' . count($schools) . ' entries.');
            } else {
                Log::warning('No Schools to insert.');
            }
        }
    }
}
