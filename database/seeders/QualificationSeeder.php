<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Qualification;
class QualificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
              $qualifications = [
            [
                "name" => "Higher National Diploma",
                "abbreviation" => "HND",
                "level" => "Undergraduate",
                "obtained_from" => "Colleges of Technology, Polytechnics, or Universities"
            ],
            [
                "name" => "Bachelor of Science",
                "abbreviation" => "BSc",
                "level" => "Undergraduate",
                "obtained_from" => "Universities"
            ],
            [
                "name" => "Master of Science",
                "abbreviation" => "MSc",
                "level" => "Postgraduate",
                "obtained_from" => "Universities"
            ],
            [
                "name" => "Doctor of Philosophy",
                "abbreviation" => "PhD",
                "level" => "Postgraduate (Doctoral)",
                "obtained_from" => "Universities"
            ]
        ];

        foreach ($qualifications as $q) {
            Qualification::create([
                "name" => $q['name'],
                "level" => $q['level'],
                'abbreviation' => $q['abbreviation']
            ]);
        }
    }
}
