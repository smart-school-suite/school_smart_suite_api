<?php

namespace Database\Seeders;

use App\Models\RegistrationFee;
use App\Models\Schooladmin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class test extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $registrationFee = RegistrationFee::all();
        foreach($registrationFee as $fee){
            $fee->status = "not paid";
            $fee->save();
        }
    }
}
