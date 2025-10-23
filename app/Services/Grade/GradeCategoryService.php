<?php

namespace App\Services\Grade;

use App\Models\GradesCategory;

class GradeCategoryService
{
    public function createGradeCategory($gradeCategoryData)
    {
        $category = GradesCategory::create($gradeCategoryData);
        return $category;
    }

    public function deleteGradeCategory($categoryId)
    {
        $gradeCategory = GradesCategory::findOrFail($categoryId);
        $gradeCategory->delete();
        return $gradeCategory;
    }

    public function getGradesCategory()
    {
        $categories = GradesCategory::all();
        return $categories;
    }

    public function UpdateGradeCategory($gradeCategoryData, $categoryId)
    {
        $gradeCategory = GradesCategory::findOrFail($categoryId);
        $gradeCategory->update($gradeCategoryData);
        return $gradeCategory;
    }
}
