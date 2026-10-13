<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $timestamp = now();

        $countriesData = [
            [
                'country'           => 'Burkina Faso',
                'code'              => 'BFA',
                'currency'          => '₣',
                'official_language' => 'French',
            ],
            [
                'country'           => 'Cabo Verde',
                'code'              => 'CPV',
                'currency'          => 'CVE',
                'official_language' => 'Portuguese',
            ],
            [
                'country'           => 'Cameroon',
                'code'              => 'CMR',
                'currency'          => '₣',
                'official_language' => 'French, English',
            ],
            [
                'country'           => 'Central African Republic',
                'code'              => 'CAF',
                'currency'          => '₣',
                'official_language' => 'Sango, French',
            ],
            [
                'country'           => 'Algeria',
                'code'              => 'DZA',
                'currency'          => 'DZD',
                'official_language' => 'Arabic',
            ],
            [
                'country'           => 'Burundi',
                'code'              => 'BDI',
                'currency'          => 'BIF',
                'official_language' => 'Kirundi, French',
            ],
            [
                'country'           => 'Benin',
                'code'              => 'BEN',
                'currency'          => 'XOF',
                'official_language' => 'French',
            ],
            [
                'country'           => 'Botswana',
                'code'              => 'BWA',
                'currency'          => 'BWP',
                'official_language' => 'English, Tswana',
            ],
            [
                'country'           => 'Angola',
                'code'              => 'AGO',
                'currency'          => 'AOA',
                'official_language' => 'Portuguese',
            ],
        ];

        $countries = array_map(function ($row) use ($timestamp) {
            return array_merge($row, [
                'id'         => Str::uuid()->toString(),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }, $countriesData);

        DB::table('countries')->upsert(
            $countries,
            ['code'],
            ['country', 'currency', 'official_language', 'updated_at']
        );
    }
}
