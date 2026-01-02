<?php

namespace App\Constant\Authorization\PermissionCategory\PermissionCategoryDefination;
use App\Constant\Authorization\Builder\PermissionCategoryBuilder;
use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\AcademicPermissionCategories;
use App\Constant\System\Guards;
class AcademicPermissionCategoryDefinations
{
   public static function all(): array {
      return [
          PermissionCategoryBuilder::make(
              AcademicPermissionCategories::CA_EVALUATION_MANAGER,
              "CA Evaluation Manager",
              Guards::SCHOOL_ADMIN,
              ""
          ),
      ];
   }
}
