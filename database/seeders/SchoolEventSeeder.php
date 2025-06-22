<?php

namespace Database\Seeders;

use App\Models\EventTag;
use App\Models\EventCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    protected $schoolBranchId;
    public function __construct($schoolBranchId)
    {
        $this->schoolBranchId = $schoolBranchId;
    }
    public function run(): void
    {
        // List of event categories
        $eventCategories = [
            'Music',
            'Conference',
            'Workshop',
            'Festival',
            'Seminar',
            'Webinar',
            'Networking',
            'Training',
            'Sports',
            'Art & Culture'
        ];

        $eventTags = [
            'Free',
            'VIP',
            'Outdoor',
            'Indoor',
            'Family-friendly',
            'Networking',
            'Technology',
            'Business',
            'Art',
            'Music',
            'Food & Drink',
            'Educational',
            'Live Performance',
            'Virtual',
        ];

        $this->command->info("Creation Of Event Category Started");
        foreach ($eventCategories as $eventCategory) {
            EventCategory::create([
                'name' => $eventCategory,
                'school_branch_id' => $this->schoolBranchId
            ]);
        }
       $this->command->info("Event Category Created Successfully");

       $this->command->info("Creation Of Event Tags Started Successfully");
        foreach ($eventTags as $eventTag) {
            EventTag::create([
                'name' => $eventTag,
                'school_branch_id' => $this->schoolBranchId
            ]);
        }
        $this->command->info("Event Tags Created Successfully");
    }
}
