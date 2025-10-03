<?php

namespace Database\Seeders;

use App\Models\AnnouncementLabel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $announcementLabels = [
            [
                'label' => 'important',
                'icon' => 'fluent:important-20-filled',
                'color' => [
                    'color_light' => '#f6d091',
                    'color_thick' => '#e6751a'
                ],
            ],
            [
                'label' => 'urgent',
                'icon' => 'fluent:alert-urgent-24-filled',
                'color' => [
                    'color_light' => '#f8d1d0',
                    'color_thick' => '#d9534f'
                ],
            ],
            [
                'label' => 'info',
                'icon' => "material-symbols:info-rounded",
                'color' => [
                    'color_light' => '#d0d7ff',
                    'color_thick' => '#4345ff'
                ],
            ],
            [
                'label' => 'all',
                'icon' => "mage:dashboard-fill",
                'color' => [
                    'color_light' => '#bae7fd',
                    'color_thick' => '#0ea7e9'
                ]
            ]
        ];
         foreach($announcementLabels as $announcementLabel){
             AnnouncementLabel::create([
                 'name' => $announcementLabel['label'],
                 'icon' => $announcementLabel['icon'],
                 'color' => json_encode($announcementLabel['color'])
             ]);
         }
    }
}
