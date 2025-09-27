<?php

namespace App\Services;

use App\Models\AdditionalFeesCategory;
use App\Exceptions\AppException;

class AdditionalFeeCategoryService
{
    // Implement your logic here

    public function createAdditionalFeeCategory(array $categoryData, $currentSchool)
    {
        $additionalFeeCategory = AdditionalFeesCategory::where("school_branch_id", $currentSchool->id)
            ->where("title", $categoryData["title"])
            ->first();
        if ($additionalFeeCategory) {
            throw new AppException(
                "It looks like you already have a category with this title",
                400,
                "Duplicate Category",
                "Please choose a different title for this category.",
                '/additional-fee-categories'
            );
        }
        $additionalFeeCategory = new AdditionalFeesCategory();
        $additionalFeeCategory->school_branch_id = $currentSchool->id;
        $additionalFeeCategory->title = $categoryData["title"];
        $additionalFeeCategory->save();
        return $additionalFeeCategory;
    }

    public function updateAdditionalFeeCategory(array $categoryData, $currentSchool, $feeCategoryId)
    {
        $feeCategoryExists = AdditionalFeesCategory::where("school_branch_id", $currentSchool->id)->find($feeCategoryId);
        if (!$feeCategoryExists) {
            return ApiResponseService::error("It looks like this category has been deleted", null, 404);
        }
        $filterData = array_filter($categoryData);
        if($filterData['title']){
            $titleExists = AdditionalFeesCategory::where("school_branch_id", $currentSchool->id)
                ->where("title", $filterData["title"])
                ->where("id", "!=", $feeCategoryId)
                ->first();
            if ($titleExists) {
                throw new AppException(
                    "It looks like you already have a category with this title",
                    400,
                    "Duplicate Category",
                    "Please choose a different title for this category.",
                    '/additional-fee-categories'
                );
            }
        }
        $feeCategoryExists->update($filterData);
        return $feeCategoryExists;
    }

    public function deleteAdditionalFeeCategory($currentSchool, $feeCategoryId)
    {
        $feeCategoryExists = AdditionalFeesCategory::where("school_branch_id", $currentSchool->id)->find($feeCategoryId);
        if (!$feeCategoryExists) {
            throw new AppException(
                "It looks like this category has been deleted",
                404,
                "Category Not Found",
                "The additional fee category you are trying to delete does not exist or has already been removed.",
                '/additional-fee-categories'
            );
        }
        $feeCategoryExists->delete();
    }

    public function getAdditionalFeeCategory($currentSchool)
    {
        $additionalFeeCategory = AdditionalFeesCategory::where("school_branch_id", $currentSchool->id)->get();
        if($additionalFeeCategory->isEmpty()){
            throw new AppException(
                "No Additional Fee Categories Found",
                404,
                "No Categories",
                "There are no additional fee categories available. Please create one to proceed.",
                '/additional-fee-categories'

            );
        }
        return $additionalFeeCategory;
    }
}
