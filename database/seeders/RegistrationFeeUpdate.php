<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\RegistrationFee;
use Illuminate\Database\Seeder;

class RegistrationFeeUpdate extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $registrationFees = RegistrationFee::all();
        foreach($registrationFees as $registrationFee){
            $registrationFee->status = "unpaid";

            $registrationFee->save();
        }
    }
}
