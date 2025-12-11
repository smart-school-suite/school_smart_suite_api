<?php

namespace Database\Seeders;

use App\Models\ActivationCodeType;
use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActivationCodeTypeSeeder extends Seeder
{
    public function run(): void
    {
        $countries = Country::all();
        $activationCodeTypes = [
             [
                 "name" => "Teacher Account Activation",
                 "type" => "teacher",
                 "description" => "Code Used to activate teacher account"
             ],
             [
                 "name" => "Student Account Activation",
                 "type" => "student",
                 "description" => "Code Used to activate Student account"
             ]
        ];

        foreach ($countries as $country) {
            foreach ($activationCodeTypes as $type) {
                $randomPrice = rand(10000, 15000);

                ActivationCodeType::create([
                       'name' => $type['name'],
                       "description" => $type['description'],
                       "type" => $type['type'],
                       "price" => $randomPrice,
                       "country_id" => $country->id
                 ]);
            }
        }
    }
}
