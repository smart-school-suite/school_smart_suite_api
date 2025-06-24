<?php

namespace Database\Seeders;

use App\Jobs\StatisticalJobs\OperationalJobs\ElectionWinnerStatJob;
use App\Models\RegistrationFee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
class TestJob extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $registrationFees = RegistrationFee::all();
       foreach($registrationFees as $fee){
          $fee->update([
              'status' => 'unpaid'
          ]);
       }
    }
    public function randomMoney($max, $min){
      $faker = Faker::create();
      $randomMoney = $faker->numberBetween($min, $max);
      return $randomMoney;
    }
}
