<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Gender;
use App\Models\StudentSource;

class StudentCredentials extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genders = [
            'Male',
            'Female'
        ];

        $studentSources = [
            [
                "name" => "Social Media",
                "description" => "Leads generated from platforms like Facebook, Instagram, LinkedIn, or TikTok through organic posts, community engagement, or targeted ad campaigns."
            ],
            [
                "name" => "Flyers",
                "description" => "Physical promotional materials distributed in the local neighborhood, at community centers, or handed out during door-to-door marketing drives."
            ],
            [
                "name" => "Word of Mouth",
                "description" => "Referrals from current parents, students, alumni, or staff members. This represents the school's reputation and community trust."
            ],
            [
                "name" => "Search Engines",
                "description" => "Prospective families finding the school via Google or Bing searches for local education providers or specific academic programs."
            ],
            [
                "name" => "Billboards",
                "description" => "Large-scale outdoor advertising positioned in high-traffic areas or near major transit routes to increase general brand visibility."
            ],
            [
                "name" => "School Website",
                "description" => "Direct visits to the official school portal, usually by users who are already familiar with the school name and are seeking enrollment forms."
            ],
            [
                "name" => "Educational Fairs",
                "description" => "Inquiries captured during school expos, open days, or career fairs where the school has a physical presence."
            ],
            [
                "name" => "Radio & TV",
                "description" => "Traditional broadcast advertisements aimed at reaching a wide local or regional audience during peak enrollment seasons."
            ],
            [
                "name" => "Newspapers",
                "description" => "Advertisements or featured articles in local or national print media and community newsletters."
            ],
            [
                "name" => "Email Marketing",
                "description" => "Direct outreach via newsletters or enrollment campaigns sent to a database of interested prospects or previous inquiries."
            ]
        ];

        foreach ($genders as $gender) {
            Gender::create([
                'name' => $gender
            ]);
        }

        foreach ($studentSources as $studentSource) {
             StudentSource::create([
                 'name' => $studentSource['name'],
                 'description' => $studentSource['description']
             ]);
        }
    }
}
