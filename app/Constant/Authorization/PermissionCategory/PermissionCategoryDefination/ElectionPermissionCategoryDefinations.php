<?php

namespace App\Constant\Authorization\PermissionCategory\PermissionCategoryDefination;

use App\Constant\Authorization\Builder\PermissionCategoryBuilder;
use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ElectionPermissionCategories;

class ElectionPermissionCategoryDefinations
{
    public static function all(): array
    {
        return [
            PermissionCategoryBuilder::make(
                ElectionPermissionCategories::ELECTION_ANALYTICS_MANAGER,
                "Election Analytics Manager",
                "Grants access to real-time voter turnout statistics, participation trends, and demographic data analysis."
            ),
            PermissionCategoryBuilder::make(
                ElectionPermissionCategories::ELECTION_CANDIDATE_MANAGER,
                "Election Candidate Manager",
                "Allows management of candidate registrations, eligibility verification, and profile information for those running for office."
            ),
            PermissionCategoryBuilder::make(
                ElectionPermissionCategories::ELECTION_MANAGER,
                "Election Manager",
                "Provides high-level control over the election lifecycle, including creating election cycles and opening/closing the polls."
            ),
            PermissionCategoryBuilder::make(
                ElectionPermissionCategories::ELECTION_RESULT_MANAGER,
                "Election Result Manager",
                "Enables the viewing, auditing, and official publication of final vote tallies and winner announcements."
            ),
            PermissionCategoryBuilder::make(
                ElectionPermissionCategories::ELECTION_ROLE_MANAGER,
                "Election Role Manager",
                "Allows the definition of contested positions (e.g., President, Secretary) and setting the requirements for each role."
            ),
            PermissionCategoryBuilder::make(
                ElectionPermissionCategories::ELECTION_TYPE_MANAGER,
                "Election Type Manager",
                "Used to configure and categorize different types of polls, such as Departmental, Faculty-wide, or General Student Body elections."
            )
        ];
    }
}
