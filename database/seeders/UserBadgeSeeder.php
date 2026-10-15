<?php

namespace Database\Seeders;

use App\Models\Badge\BadgeCategory;
use App\Models\Badge\BadgeType;
use Illuminate\Database\Seeder;

class UserBadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createUserBadgeCategory();
        $this->createUserBadgeTypes();
    }

    private function createUserBadgeCategory(): void
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
            BadgeCategory::updateOrCreate(
                ['key' => $category['key']],
                [
                    'name' => $category['name'],
                    'status' => 'active',
                ]
            );
        }
    }

    public function createUserBadgeTypes(): void
    {
        $badgeTypes = [
            [
                "name"        => "Admin Verification",
                "key"         => "verfication.badge",
                "category"    => "verfication.badges",
                "description" => "Official verification badge granted by administrators. Indicates the account has been manually reviewed and confirmed as authentic (usually for teachers, staff, or special student roles).",
                "color"       => "#1e40af",
                "icon_code"   => "ADMIN_VERFICATION"
            ],
            [
                "name"        => "School Top Performer",
                "key"         => "performance.badge",
                "category"    => "performance.badges",
                "description" => "Awarded to students who demonstrate exceptional academic performance, highest grades, or outstanding results in exams, projects, or overall ranking during a term or academic year.",
                "color"       => "#d97706",
                "icon_code"   => "TOP_PERFORMER"
            ],
            [
                "name"        => "Student Election Role Winner",
                "key"         => "election.badge",
                "category"    => "election.badges",
                "description" => "Badge earned by winning a student leadership position through school elections (e.g., Class Representative, SRC/Student Council member, Prefect, Club President, etc.). Recognizes democratic participation and leadership.",
                "color"       => "#6d28d9",
                "icon_code"   => "ELECTION_ROLE_WINNER"
            ]
        ];

        $categoriesByKey = BadgeCategory::pluck('id', 'key');

        foreach ($badgeTypes as $badgeType) {
            $badgeCategoryId = $categoriesByKey[$badgeType['category']] ?? null;

            BadgeType::updateOrCreate(
                ['key' => $badgeType['key']],
                [
                    "name"              => $badgeType['name'],
                    "badge_category_id" => $badgeCategoryId,
                    "description"       => $badgeType['description'],
                    "color"             => $badgeType['color'],
                    "icon_code"         => $badgeType['icon_code'],
                ]
            );
        }
    }
}
