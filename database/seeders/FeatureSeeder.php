<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Feature;
use App\Models\Country;
class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            [
                "name" => "Timetable Generations",
                "key" => "timetable.generation",
                "description" => "Specifies how many times your school can automatically generate timetables under this plan.",
                "limit_type" => "integer",
                "default" => 0
            ],
            [
                "name" => "School Admins",
                "key" => "school.admins",
                "description" => "Defines the maximum number of school admin accounts you can create.",
                "limit_type" => "integer",
                "default" => 0
            ],
            [
                "name" => "Rate Limit",
                "key" => "rate.limit",
                "description" => "Indicates the number of API requests your school is allowed to make per minute.",
                "limit_type" => "integer",
                "default" => 0
            ],
            [
                "name" => "School Announcements",
                "key" => "school.announcements",
                "description" => "Determines the maximum number of announcements your school can publish.",
                "limit_type" => "integer",
                "default" => 0
            ],
            [
                "name" => "School Events",
                "key" => "school.events",
                "description" => "Sets the maximum number of school events your institution can create.",
                "limit_type" => "integer",
                "default" => 0
            ],
            [
                "name" => "School Elections",
                "key" => "school.elections",
                "description" => "Limits how many elections your school can manage within this plan.",
                "limit_type" => "integer",
                "default" => 0
            ],
            [
                "name" => "Departments",
                "key" => "school.departments",
                "description" => "Specifies the maximum number of departments your school can add.",
                "limit_type" => "integer",
                "default" => 0
            ],
            [
                "name" => "Specialties",
                "key" => "school.specialties",
                "description" => "Defines how many specialties your school can create under this plan.",
                "limit_type" => "integer",
                "default" => 0
            ],
            [
                "name" => "Courses",
                "key" => "school.courses",
                "description" => "Indicates the maximum number of courses your school is allowed to offer.",
                "limit_type" => "integer",
                "default" => 0
            ]
        ];

        $countries = Country::all();
        foreach($countries as $country){
             foreach($features as $feature){
             Feature::create([
                 "name" => $feature["name"],
                 "key" => $feature["key"],
                 "description" =>  $feature["description"],
                 "country_id" => $country->id,
                 "limit_type" => $feature["limit_type"],
                 "default" => $feature["default"]
             ]);
        }
        }
    }
}
