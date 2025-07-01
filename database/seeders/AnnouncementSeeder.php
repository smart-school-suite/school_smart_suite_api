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
             'info',
             'urgent',
             "important",
             'All'
        ];
        $this->command->info("Creating Announcement Labels.......................0%");
         foreach($announcementLabels as $announcementLabel){
             AnnouncementLabel::create([
                 'name' => $announcementLabel
             ]);
         }
         $this->command->info("Announcement Labels Created Successfully...................................100%");
    }
}
