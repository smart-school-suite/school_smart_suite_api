<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;

class SchoolExpensesCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::info('School expenses category seeder has started.');
        $timestamp = now();
        $filePath = public_path("data/school_expenses_category.csv");
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception("CSV file not found or not readable!");
        }
    
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle); // Read the header
            Log::info('CSV Header: ', $header); // Log the header for debugging
            $school_branches = DB::table('school_branches')->pluck('id')->toArray();
            $school_expenses_category = []; // Initialize an empty array for countries
            
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data); // Log current row data for debugging
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 15); 
                $randomSchoolBranchesId = Arr::random($school_branches);
                // Ensure the row has at least two columns
                if (count($data) >= 2) {
                    $school_expenses_category[] = [
                        'id' => $id, // Assign id from 1st column
                        'school_branch_id' => $randomSchoolBranchesId, // Assign name from 2nd column
                        'names' => $data[1], // Assign name from 2nd column
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                }
            }
    
            fclose($handle);
            
            Log::info('School expenses category  Array: ', $school_expenses_category); // Log the countries array after completion
    
            // Insert the countries into the database
            if (!empty($school_expenses_category)) {
                DB::table('school_expenses_category')->insert($school_expenses_category);
                Log::info('Inserted categories: ' . count($school_expenses_category) . ' entries.');
            } else {
                Log::warning('No catergories to insert.');
            }
        }
    }
}
