<?php

namespace Database\Seeders;

use App\Models\Installment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InstallmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'First Installment',
                'program_name' => 'first_installment',
                'count' => 1,
                'code' => 'FIRINS'
            ],
            [
                'name' => 'Second Installment',
                'program_name' => 'second_installment',
                'count' => 2,
                'code' => 'SECINS'
            ],
            [
                'name' => 'Third Installment',
                'program_name' => 'third_installment',
                'count' => 3,
                'code' => 'THIINS'
            ],
            [
                'name' => 'Fourth Installment',
                'program_name' => 'fourth_installment',
                'count' => 4,
                'code' => 'FOUINS'
            ],
            [
                'name' => 'Fifth Installment',
                'program_name' => 'fifth_installment',
                'count' => 5,
                'code' => 'FIFINS'
            ]
        ];
        $this->command->info("Creating Fee Installments..............................................1%");
        foreach($data as $feeInstallment){
            Installment::create(
                $feeInstallment
            );
        }
        $this->command->info("Fee Installments Created Successfully..............................................1%");
    }
}
