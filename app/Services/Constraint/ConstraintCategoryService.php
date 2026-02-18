<?php

namespace App\Services\Constraint;

use App\Models\Constraint\ConstraintCategory;
use App\Exceptions\AppException;
class ConstraintCategoryService
{
   public function getConstraintCategories()
   {
        $constraintCategories = ConstraintCategory::all();
        if($constraintCategories->isEmpty()) {
            throw new AppException(
                "No Constraint Categories Found",
                404,
                "No constraint categories were found in the system.",
                "No Constraint Categories Found Please Ensure that there are constraint categories in the system and try again"
            );
        }
        return $constraintCategories;
   }

   public function getConstraintCategoryById(string $constraintCategoryId)
   {
        $constraintCategory = ConstraintCategory::find($constraintCategoryId);
        if (!$constraintCategory) {
            throw new AppException(
                "Constraint Category Not Found",
                404,
                "The specified constraint category does not exist.",
                "The Constraint Category Was Not Found Please Ensure that the constraint category exists and try again"
            );
        }
        return $constraintCategory;
   }

   public function activateConstraintCategory(string $constraintCategoryId)
   {
        $constraintCategory  = ConstraintCategory::find($constraintCategoryId);
        if (!$constraintCategory) {
            throw new AppException(
                "Constraint Category Not Found",
                404,
                "The specified constraint category does not exist.",
                "The Constraint Category Was Not Found Please Ensure that the constraint category exists and try again"
            );
        }

        if($constraintCategory->status === 'active') {
            throw new AppException(
                "Constraint Category Already Active",
                400,
                "The specified constraint category is already active.",
                "The Constraint Category is Already Active Please Ensure that the constraint category is not already active and try again"
            );
        }

        $constraintCategory->status = 'active';
        $constraintCategory->save();
        return $constraintCategory;
   }

    public function deactivateConstraintCategory(string $constraintCategoryId)
    {
          $constraintCategory  = ConstraintCategory::find($constraintCategoryId);
          if (!$constraintCategory) {
                throw new AppException(
                 "Constraint Category Not Found",
                 404,
                 "The specified constraint category does not exist.",
                 "The Constraint Category Was Not Found Please Ensure that the constraint category exists and try again"
                );
          }

          if($constraintCategory->status === 'inactive') {
                throw new AppException(
                 "Constraint Category Already Inactive",
                 400,
                 "The specified constraint category is already inactive.",
                 "The Constraint Category is Already Inactive Please Ensure that the constraint category is not already inactive and try again"
                );
          }

          $constraintCategory->status = 'inactive';
          $constraintCategory->save();
          return $constraintCategory;
    }

}
