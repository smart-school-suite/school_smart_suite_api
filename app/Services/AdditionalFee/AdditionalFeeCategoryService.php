<?php

namespace App\Services\AdditionalFee;

use App\Models\AdditionalFeesCategory;
use App\Exceptions\AppException;
use App\Events\Actions\AdminActionEvent;

class AdditionalFeeCategoryService
{
    public function createAdditionalFeeCategory(array $categoryData, $currentSchool, $authAdmin)
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
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.additionalFeeCategory.create"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "additionalCategoryManagement",
                "authAdmin" => $authAdmin,
                "data" => $additionalFeeCategory,
                "message" => "Additional Fee Category Deleted",
            ]
        );
        return $additionalFeeCategory;
    }

    public function updateAdditionalFeeCategory(array $categoryData, $currentSchool, $feeCategoryId, $authAdmin)
    {
        $feeCategoryExists = AdditionalFeesCategory::where("school_branch_id", $currentSchool->id)->find($feeCategoryId);

        if (!$feeCategoryExists) {
            throw new AppException(
                "Additional Fee Category ID '{$feeCategoryId}' not found for school branch ID '{$currentSchool->id}'.",
                404,
                "Category Not Found or Deleted 🗑️",
                "We couldn't find the fee category you are trying to update. It may have already been deleted or the ID provided is incorrect.",
                null
            );
        }

        $filterData = array_filter($categoryData);

        if (isset($filterData['title'])) {
            $titleExists = AdditionalFeesCategory::where("school_branch_id", $currentSchool->id)
                ->where("title", $filterData["title"])
                ->where("id", "!=", $feeCategoryId)
                ->first();

            if ($titleExists) {
                throw new AppException(
                    "A category with the title '{$filterData['title']}' already exists at this school branch.",
                    409,
                    "Duplicate Category Title 📛",
                    "The title '{$filterData['title']}' is already in use by another additional fee category. Please choose a unique title for this category.",
                    '/additional-fee-categories'
                );
            }
        }

        try {
            $feeCategoryExists->update($filterData);
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.additionalFeeCategory.update"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "additionalCategoryManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $feeCategoryExists,
                    "message" => "Additional Fee Category Updated",
                ]
            );
            return $feeCategoryExists;
        } catch (\Exception $e) {
            throw new AppException(
                "Failed to update Additional Fee Category ID '{$feeCategoryId}'. Error: " . $e->getMessage(),
                500,
                "Update Failed 🛑",
                "We were unable to save the changes to the fee category due to a system error. Please try again or contact support.",
                null
            );
        }
    }

    public function deleteAdditionalFeeCategory($currentSchool, $feeCategoryId, $authAdmin)
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
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.additionalFeeCategory.delete"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "additionalCategoryManagement",
                "authAdmin" => $authAdmin,
                "data" => $feeCategoryExists,
                "message" => "Additional Fee Category Deleted",
            ]
        );
        return $feeCategoryExists;
    }

    public function getAdditionalFeeCategory($currentSchool)
    {
        $additionalFeeCategory = AdditionalFeesCategory::where("school_branch_id", $currentSchool->id)->get();
        if ($additionalFeeCategory->isEmpty()) {
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

    public function getAdditionalFeeCategoryDetails($currentSchool, $feeCategoryId)
    {
        $additionalFeeCategory = AdditionalFeesCategory::where("school_branch_id", $currentSchool->id)
            ->find($feeCategoryId);

        if (!$additionalFeeCategory) {
            throw new AppException(
                "Additional Fee Category ID '{$feeCategoryId}' not found for school branch ID '{$currentSchool->id}'.",
                404,
                "Fee Category Not Found 🔎",
                "We couldn't find the details for the specific fee category you requested. Please verify the ID and ensure it exists at your school branch.",
                null
            );
        }

        return $additionalFeeCategory;
    }
}
