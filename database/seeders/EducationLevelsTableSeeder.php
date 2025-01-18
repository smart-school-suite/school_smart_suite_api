<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class EducationLevelsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::info('Education levels seeder has started.');
        $timestamp = now();
        $filePath = public_path("data/education_levels.csv");
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception("CSV file not found or not readable!");
        }
    
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header); 
    
            $education_levels = []; 
    
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data); 
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 10); 

                if (count($data) >= 2) {
                    $education_levels[] = [
                        'id' => $id, 
                        'name' => $data[1], 
                        'level' => $data[2], 
                        'program_name' => $data[3], 
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                }
            }
    
            fclose($handle);
            
            Log::info('Education Levels Array: ', $education_levels);
    
  
            if (!empty($education_levels)) {
                DB::table('education_levels')->insert($education_levels);
                Log::info('Inserted Education levels: ' . count($education_levels) . ' entries.');
            } else {
                Log::warning('No Education levels to insert.');
            }
        }
    }
}
