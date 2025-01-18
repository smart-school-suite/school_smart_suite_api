<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class SemesterTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::info('Semester seeder has started.');
        $timestamp = now();
        $filePath = public_path("data/semesters.csv");
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception("CSV file not found or not readable!");
        }
    
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header); 
    
            $semesters = []; 
    
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data); 
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 10); 

                if (count($data) >= 2) {
                    $semesters[] = [
                        'id' => $id, 
                        'name' => $data[1], 
                        'program_name' => $data[2], 
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                }
            }
    
            fclose($handle);
            
            Log::info('Semester Array: ', $semesters);
    
  
            if (!empty($semesters)) {
                DB::table('semesters')->insert($semesters);
                Log::info('Inserted semesters: ' . count($semesters) . ' entries.');
            } else {
                Log::warning('No semesters to insert.');
            }
        }
    }
}
