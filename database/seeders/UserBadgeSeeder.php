<?php

namespace Database\Seeders;

use App\Models\Badge\BadgeCategory;
use App\Models\Badge\BadgeType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserBadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createUserBadgeCategory();
    }

    private function createUserBadgeCategory()
    {
        $categories = [
            [
                "name" => "Election Badges",
                "key" => "election.badges"
            ],
            [
                "name" => "Performance Badges",
                "key" => "performance.badges",
            ],
            [
                "name" => "Verfication Badges",
                "key" => "verfication.badges"
            ]
        ];
        foreach ($categories as $category) {
            BadgeCategory::create([
                'name' => $category['name'],
                "status" => "active",
                "key" => $category['key']
            ]);
        }
    }

    public function createUserBadgeTypes()
    {
        $badgeTypes = [
            [
                "name"        => "Admin Verification",
                "key"         => "verfication.badge",
                "description" => "Official verification badge granted by administrators. Indicates the account has been manually reviewed and confirmed as authentic (usually for teachers, staff, or special student roles).",
                "color"       => "#1e40af",
                "icon_code"   => "ADMIN_VERFICATION"
            ],
            [
                "name"        => "School Top Performer",
                "key"         => "performance.badge",
                "description" => "Awarded to students who demonstrate exceptional academic performance, highest grades, or outstanding results in exams, projects, or overall ranking during a term or academic year.",
                "color"       => "#d97706", 
                "icon_code"   => "TOP_PERFORMER"
            ],
            [
                "name"        => "Student Election Role Winner",
                "key"         => "election.badge",
                "description" => "Badge earned by winning a student leadership position through school elections (e.g., Class Representative, SRC/Student Council member, Prefect, Club President, etc.). Recognizes democratic participation and leadership.",
                "color"       => "#6d28d9",  
                "icon_code"   => "ELECTION_ROLE_WINNER"
            ]
        ];

        foreach ($badgeTypes as $badgeType) {
            $badgeCategory = BadgeCategory::where("key", $badgeType['key'])->first();

            if (BadgeType::where('badge_category_id', $badgeCategory?->id)
                ->where('key', $badgeType['key'])
                ->exists()
            ) {
                continue;
            }

            BadgeType::create([
                "name"             => $badgeType['name'],
                "badge_category_id" => $badgeCategory?->id,
                "description"      => $badgeType['description'],
                "color"            => $badgeType['color'],
                "icon_code"        => $badgeType['icon_code']
            ]);
        }
    }
}
