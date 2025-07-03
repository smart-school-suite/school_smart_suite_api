<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
             [
                'title' => 'Authomatic Student Promotion',
                'allowed_value' => 'boolean',
                'default' => true
             ],
             [
                'title' => 'Bill Student Registration Fee When they Get Promoted',
                'allowed_value' => 'boolean',
                'default' => true
             ],
             [
                'title' => 'Resit Fee',
                'allowed_value' => 'decimal',
                 'default' => 3000.00
             ],
             [
                'title' => 'Resit Feature',
                'allowed_value' => 'boolean',
                'default' => true
             ],
             [
                'title' => 'Election Feature',
                'allowed_value' => 'boolean',
                'default' => true
             ],
             [
                'title' => 'Additional Fee Feature',
                'allowed_value' => 'boolean',
                'default' => true
             ],
             [
                'title' => 'School Events Feature',
                'allowed_value' => 'boolean',
                'default' => true
             ],
             [
                'title' => 'Announcement Feature',
                'allowed_value' => 'boolean',
                'default' => true
             ],
             [
                'title' => 'Tuition Fee Feature',
                'allowed_value' => 'boolean'
             ]
        ];
    }
}
