<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
class CountryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::info('CountryTableSeeder has started.');
        $timestamp = now();
        $filePath = public_path("data/country.csv");
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception("CSV file not found or not readable!");
        }
    
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle); // Read the header
            Log::info('CSV Header: ', $header); // Log the header for debugging
    
            $countries = []; // Initialize an empty array for countries
    
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data); // Log current row data for debugging
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 10); 
                // Ensure the row has at least two columns
                if (count($data) >= 2) {
                    $countries[] = [
                        'id' => $id, // Assign id from 1st column
                        'country' => $data[1], // Assign name from 2nd column
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                }
            }
    
            fclose($handle);
            
            Log::info('Countries Array: ', $countries); // Log the countries array after completion
    
            // Insert the countries into the database
            if (!empty($countries)) {
                DB::table('country')->insert($countries);
                Log::info('Inserted countries: ' . count($countries) . ' entries.');
            } else {
                Log::warning('No countries to insert.');
            }
        }
    }
}
