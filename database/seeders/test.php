<?php

namespace Database\Seeders;

use App\Jobs\StatisticalJobs\OperationalJobs\AnnouncementStatJob;
use App\Models\AnnouncementLabel;
use Illuminate\Database\Seeder;
use function GuzzleHttp\json_encode; // This is not needed if you use PHP's native json_encode

// Note: I've removed all unused 'use' statements for clarity.
// If you need them elsewhere in your actual file, keep them.

class test extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $announcementId = "c61c72e3-4681-4c2a-93de-9dbee4109175";
       $schoolBranchId = "63b4ddd0-bf0d-46b5-b333-0c61a57b8b3c";
       AnnouncementStatJob::dispatch( $schoolBranchId, $announcementId);
    }

    public function seedLabelData(){
                 $iconDatas = [
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

        foreach ($iconDatas as $iconData) {

            AnnouncementLabel::where('name', $iconData['label'])
                ->update([
                    'color' => json_encode($iconData['color']),
                    'icon' => $iconData['icon']
                ]);
        }
    }
}
