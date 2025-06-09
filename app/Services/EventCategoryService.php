<?php

namespace App\Services;

use App\Models\EventCategory;
use Exception;
use Throwable;

class EventCategoryService
{
    public function createCategory($categoryData, $currentSchool) {
         try{
            $createCategory = EventCategory::create([
                 'name' => $categoryData['name'],
                 'school_branch_id' => $currentSchool->id,
                 'description' => $categoryData['description'] ?? null
            ]);
            return $createCategory;
         }
         catch(Throwable $e){
            throw $e;
         }
    }

    public function updateCategory($categoryData, $currentSchool, $categoryId){
         try{
             $category = EventCategory::where("school_branch_id", $currentSchool->id)
             ->findOrFail($categoryId);
             $cleanData = array_filter($categoryData);
             $category->update($cleanData);
             return $category;
         }
         catch(Throwable $e){
            throw $e;
         }
    }

    public function deleteCategory($categoryId, $currentSchool){
        try{
           $category = EventCategory::where("school_branch_id", $currentSchool->id)
             ->findOrFail($categoryId);
            $category->delete();
            return $category;
        }
        catch(Throwable $e){
            throw $e;
        }
    }

    public function getCategories($currentSchool){
        try{
           $categories = EventCategory::where("school_branch_id", $currentSchool->id)->all();
           return $categories;
        }
        catch(Throwable $e){
            throw $e;
        }
    }

    public function getCategoryByStatus($currentSchool, $status){
         try{
            $categories = EventCategory::where("school_branch_id", $currentSchool->id)
                          ->where("status", $status)
                          ->get();
            return $categories;
         }
        catch(Throwable $e){
            throw $e;
        }
    }

    public function deactivateCategory($categoryId){
        try{
             $category = EventCategory::findOrFail($categoryId);
             $category->status = "inactive";
             $category->save();
             return $category;
        }
        catch(Throwable $e){
            throw $e;
        }
    }

    public function activateCategory($categoryId){
        try{
             $category = EventCategory::findOrFail($categoryId);
             $category->status = "inactive";
             $category->save();
             return $category;
        }
        catch(Throwable $e){
            throw $e;
        }
    }
}
