<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCountries();
    }

    private function seedCountries(): void
    {
        $timestamp = now();
        $filePath = public_path("data/country.csv");

        if (!file_exists($filePath) || !is_readable($filePath)) {
            Log::error("CSV file not found or not readable at: " . $filePath);
            return;
        }

        $countries = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header:', $header ?? []);

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($data) >= 5) {
                    $countries[] = [
                        'id' => Str::uuid()->toString(),
                        'country' => $data[1] ?? null,
                        'code' => $data[2] ?? null,
                        'currency' => $data[3] ?? null,
                        'official_language' => $data[4] ?? null,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                } else {
                    Log::warning('Skipping incomplete CSV row:', $data);
                }
            }
            fclose($handle);
        }

        if (!empty($countries)) {
            DB::table('countries')->upsert(
                $countries,
                ['code'],
                ['country', 'currency', 'official_language', 'updated_at']
            );
            Log::info('Upserted ' . count($countries) . ' countries.');
        } else {
            Log::warning('No countries to insert from CSV.');
        }
    }
}
