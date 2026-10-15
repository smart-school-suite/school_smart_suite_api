<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodCategory;
use App\Models\Country;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            [
                'key' => 'orange_mobile_money',
                'name' => 'Orange Mobile Money',
                'max_deposit' => 500000.00,
                'max_withdraw' => 500000.00,
                'operator_img' => 'PaymentOperators/uoK4apobaFlVAGc4xSRVgwsnKKeC9QQtrHQMWqEW.jpg',
                'description' => 'Orange Mobile Money is a digital wallet for sending, receiving, and paying bills easily via mobile phone',
                'status' => 'active',
                'country_code' => 'CMR',
                'category_key' => 'mobile_money',
            ],
            [
                'key' => 'mtn_mobile_money',
                'name' => 'Mtn Mobile Money',
                'max_deposit' => 500000.00,
                'max_withdraw' => 500000.00,
                'operator_img' => 'PaymentOperators/c8fQsN695Mx09RZkGac1CnZEUBtpRXY4YB4Vu9mk.jpg',
                'description' => 'Mtn Mobile Money is a digital wallet for sending, receiving, and paying bills easily via mobile phone',
                'status' => 'active',
                'country_code' => 'CMR',
                'category_key' => 'mobile_money',
            ],
        ];

        $country = Country::where('code', 'CMR')->first();
        $category = PaymentMethodCategory::where('name', 'Mobile Money Payments')->first();

        if (!$country) {
            $this->command?->warn('Country with code CMR not found. Skipping PaymentMethodSeeder.');
            return;
        }

        if (!$category) {
            $this->command?->warn('PaymentMethodCategory "Mobile Money Payments" not found. Skipping PaymentMethodSeeder.');
            return;
        }

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                [
                    'key' => $method['key'],
                    'country_id' => $country->id,
                ],
                [
                    'name' => $method['name'],
                    'max_deposit' => $method['max_deposit'],
                    'max_withdraw' => $method['max_withdraw'],
                    'operator_img' => $method['operator_img'],
                    'description' => $method['description'],
                    'status' => $method['status'],
                    'category_id' => $category->id,
                ]
            );
        }
    }
}
