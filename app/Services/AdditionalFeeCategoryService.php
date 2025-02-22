<?php

namespace App\Services;
use App\Models\AdditionalFeesCategory;
class AdditionalFeeCategoryService
{
    // Implement your logic here

    public function createAdditionalFeeCategory(array $categoryData, $currentSchool){
          $additionalFeeCategory = new AdditionalFeesCategory();
          $additionalFeeCategory->school_branch_id = $currentSchool->id;
          $additionalFeeCategory->title = $categoryData["title"];
          $additionalFeeCategory->save();
          return $additionalFeeCategory;
    }

    public function updateAdditionalFeeCategory(array $categoryData, $currentSchool, $feeCategoryId){
        $feeCategoryExists = AdditionalFeesCategory::where("school_branch_id", $currentSchool->id)->find($feeCategoryId);
        if(!$feeCategoryExists){
            return ApiResponseService::error("It looks like this category has been deleted", null, 404);
        }
        $filterData = array_filter($categoryData);
        $feeCategoryExists->update($filterData);
        return $feeCategoryExists;
    }

    public function deleteAdditionalFeeCategory($currentSchool, $feeCategoryId){
        $feeCategoryExists = AdditionalFeesCategory::where("school_branch_id", $currentSchool->id)->find($feeCategoryId);
        if(!$feeCategoryExists){
            return ApiResponseService::error("It looks like this category has been deleted", null, 404);
        }
        $feeCategoryExists->delete();
    }

    public function getAdditionalFeeCategory($currentSchool){
        $additionalFeeCategory = AdditionalFeesCategory::where("school_branch_id", $currentSchool->id)->get();
        return $additionalFeeCategory;
    }

}
