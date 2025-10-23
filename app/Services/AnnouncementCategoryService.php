<?php

namespace App\Services;

use App\Models\AnnouncementCategory;
use Throwable;

class AnnouncementCategoryService
{
    public function createCategory(array $categoryData, $currentSchool){
         try{
            $category = AnnouncementCategory::create([
                'name' => $categoryData['name'],
                'description' => $categoryData['description'],
                'school_branch_id' => $currentSchool->id
            ]);
            return $category;
         }
         catch(Throwable $e){
            throw $e;
         }
    }

    public function updateCategory(array $categoryData, $currentSchool, $categoryId){
         try{
            $announcementCategory = AnnouncementCategory::where("school_branch_id", $currentSchool->id)->findOrFail($categoryId);
            $filterData = array_filter($categoryData);
            $announcementCategory->update($filterData);
            return $announcementCategory;
         }
         catch(Throwable $e){
            throw $e;
         }
    }

    public function getCategories($currentSchool){
         try{
             $announcementCategories = AnnouncementCategory::where("school_branch_id", $currentSchool->id)->get();
             return $announcementCategories;
         }
         catch(Throwable $e){
             throw $e;
         }
    }

    public function deleteCategory($currentSchool, $categoryId){
        try{
           $announcementCategory = AnnouncementCategory::where("school_branch_id", $currentSchool->id)->findOrFail($categoryId);
           $announcementCategory->delete();
           return $announcementCategory;
        }
        catch(Throwable $e){
            throw $e;
        }
    }

}
