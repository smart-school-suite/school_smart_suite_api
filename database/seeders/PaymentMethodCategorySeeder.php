<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethodCategory;

class PaymentMethodCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Mobile Money Payments',
                'status' => 'active',
                'description' => 'Payments made via mobile wallets and mobile banking services, allowing users to pay using their mobile devices.',
            ],
            [
                'name' => 'Cryptocurrency Payments',
                'status' => 'active',
                'description' => 'Payments made using cryptocurrencies such as Bitcoin, Ethereum, and other digital currencies',
            ],
            [
                'name' => 'Bank Transfers',
                'status' => 'active',
                'description' => 'Direct transfers of funds from one bank account to another, often used for larger transactions or business payments',
            ],
            [
                'name' => 'Card Payments',
                'status' => 'active',
                'description' => 'Payments processed through credit or debit cards issued by banks, supporting online and in-person transactions',
            ],
            [
                'name' => 'E-wallets',
                'status' => 'active',
                'description' => 'Electronic wallets like PayPal, Apple Pay, Google Pay that store payment information for quick transactions.',
            ],
        ];

        foreach ($categories as $category) {
            PaymentMethodCategory::updateOrCreate(
                ['name' => $category['name']],
                [
                    'status' => $category['status'],
                    'description' => $category['description'],
                ]
            );
        }
    }
}
