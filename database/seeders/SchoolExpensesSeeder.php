<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;

class SchoolExpensesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::info('School expenses category seeder has started.');
        $timestamp = now();
        $filePath = public_path("data/school_expenses.csv");
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception("CSV file not found or not readable!");
        }
    
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle); // Read the header
            Log::info('CSV Header: ', $header); // Log the header for debugging
            $school_branches = DB::table('school_branches')->pluck('id')->toArray();
            $school_expenses = []; // Initialize an empty array for countries
            
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data); // Log current row data for debugging
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 15); 
                $randomSchoolBranchesId = Arr::random($school_branches);
                $expenses_category = DB::table('school_expenses_category')->where('school_branch_id', $randomSchoolBranchesId)->pluck('id')->toArray();
                if (!$expenses_category) {
                    Log::warning('No expenses found for school_branch_id: ' . $randomSchoolBranchesId);
                    continue; // Skip to the next row if there are no guardians
                }
                $randomExpensesCategory = Arr::random($expenses_category);
                // Ensure the row has at least two columns
                if (count($data) >= 2) {
                    $school_expenses[] = [
                        'id' => $id, // Assign id from 1st column
                        'school_branch_id' => $randomSchoolBranchesId, // Assign name from 2nd column
                        'date' => $data[1], // Assign name from 2nd column
                        'amount' => $data[2], // Assign name from 2nd column
                        'description' => $data[4], // Assign name from 2nd column
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                        'expenses_category_id' => $randomExpensesCategory,
                        'title' => $data[3],
                    ];
                }
            }
    
            fclose($handle);
            
            Log::info('School expenses  Array: ', $school_expenses); // Log the countries array after completion
    
            // Insert the countries into the database
            if (!empty($school_expenses)) {
                DB::table('school_expenses')->insert($school_expenses);
                Log::info('Inserted school expenses: ' . count($school_expenses) . ' entries.');
            } else {
                Log::warning('No school expenses to insert.');
            }
        }
    }
}
