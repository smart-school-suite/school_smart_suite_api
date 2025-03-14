<?php

namespace Database\Seeders;

use App\Models\TuitionFees;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class TuitionFeeBalncSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $tuitionFees = TuitionFees::all();

        foreach ($tuitionFees as $tuitionFee) {
            $tuitionFee->amount_paid = 0;

            $tuitionFee->amount_left = $tuitionFee->tution_fee_total;

            $tuitionFee->save();
        }

        $this->command->info('TuitionFee records updated successfully!');
    }
}
