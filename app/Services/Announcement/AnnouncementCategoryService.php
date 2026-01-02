<?php

namespace App\Services\Announcement;

use App\Exceptions\AppException;
use Throwable;
use App\Models\AnnouncementCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Events\Actions\AdminActionEvent;

class AnnouncementCategoryService
{
    public function createCategory(array $categoryData, $currentSchool, $authAdmin)
    {
        $existingCategory = AnnouncementCategory::where('school_branch_id', $currentSchool->id)
            ->where('name', $categoryData['name'])
            ->first();

        if ($existingCategory) {
            throw new AppException(
                "An announcement category with the name '{$categoryData['name']}' already exists for school branch ID '{$currentSchool->id}'.",
                409,
                "Duplicate Category Name 📛",
                "An announcement category with this exact name already exists. Please choose a unique name.",
                null
            );
        }

        try {
            $category = AnnouncementCategory::create([
                'name' => $categoryData['name'],
                'description' => $categoryData['description'],
                'school_branch_id' => $currentSchool->id
            ]);

            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.announcementCategory.create"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "announcementCategoryManagement",
                    "action" => "announcementCategory.created",
                    "authAdmin" => $authAdmin,
                    "data" => $category,
                    "message" => "Announcement Category Created",
                ]
            );
            return $category;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to create announcement category '{$categoryData['name']}'. Error: " . $e->getMessage(),
                500,
                "Category Creation Failed 🛑",
                "A system error occurred while trying to save the new category. Please try again or contact support.",
                null
            );
        }
    }
    public function updateCategory(array $categoryData, $currentSchool, $categoryId, $authAdmin)
    {
        try {
            $announcementCategory = AnnouncementCategory::where("school_branch_id", $currentSchool->id)
                ->findOrFail($categoryId);

            $filterData = array_filter($categoryData);

            if (isset($filterData['name'])) {
                $existingCategory = AnnouncementCategory::where('school_branch_id', $currentSchool->id)
                    ->where('name', $filterData['name'])
                    ->where('id', '!=', $categoryId)
                    ->first();

                if ($existingCategory) {
                    throw new AppException(
                        "An announcement category with the name '{$filterData['name']}' already exists at this school branch.",
                        409,
                        "Duplicate Category Name 📛",
                        "The name you entered is already in use by another announcement category. Please choose a unique name.",
                        null
                    );
                }
            }

            $announcementCategory->update($filterData);
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.announcementCategory.create"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "announcementCategoryManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $announcementCategory,
                    "message" => "Announcement Category Updated",
                    "action" => "announcementCategory.updated"
                ]
            );
            return $announcementCategory;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Announcement Category ID '{$categoryId}' not found for school branch ID '{$currentSchool->id}'.",
                404,
                "Category Not Found 🔎",
                "The announcement category you are trying to update could not be found. It may have been deleted.",
                null
            );
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to update announcement category ID '{$categoryId}'. Error: " . $e->getMessage(),
                500,
                "Category Update Failed 🛑",
                "A system error occurred while trying to save the category changes. Please try again or contact support.",
                null
            );
        }
    }
    public function getCategories($currentSchool)
    {
        try {
            $announcementCategories = AnnouncementCategory::where("school_branch_id", $currentSchool->id)->get();

            if ($announcementCategories->isEmpty()) {
                throw new AppException(
                    "No announcement categories found for school branch ID '{$currentSchool->id}'.",
                    404,
                    "No Categories Found 📂",
                    "There are currently no announcement categories created for your school. Please create a new category to get started.",
                    null
                );
            }

            return $announcementCategories;
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to retrieve announcement categories. Error: " . $e->getMessage(),
                500,
                "Data Retrieval Failed 🛑",
                "A system error occurred while trying to load the announcement categories. Please try again or contact support.",
                null
            );
        }
    }
    public function deleteCategory($currentSchool, $categoryId, $authAdmin)
    {
        $categoryName = 'Unknown Category';

        try {
            $announcementCategory = AnnouncementCategory::where("school_branch_id", $currentSchool->id)
                ->findOrFail($categoryId);

            $categoryName = $announcementCategory->name;

            $announcementCategory->delete();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.announcementCategory.delete"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "announcementCategoryManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $announcementCategory,
                    "action" => "announcementCategory.deleted",
                    "message" => "Announcement Category Deleted",
                ]
            );
            return $announcementCategory;
        } catch (ModelNotFoundException $e) {

            throw new AppException(
                "Announcement Category ID '{$categoryId}' not found for school branch ID '{$currentSchool->id}'.",
                404,
                "Category Not Found 🗑️",
                "The announcement category you are trying to delete could not be found. It may have already been deleted or the ID is incorrect.",
                null
            );
        } catch (Throwable $e) {
            $message = "Failed to delete announcement category '{$categoryName}' (ID: {$categoryId}). Error: " . $e->getMessage();

            if (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                throw new AppException(
                    $message,
                    409,
                    "Deletion Blocked 🔒",
                    "Cannot delete the category '{$categoryName}' because it is currently linked to one or more announcements. Please delete the associated announcements first.",
                    null
                );
            }

            throw new AppException(
                $message,
                500,
                "Deletion Failed 🛑",
                "A system error occurred while trying to delete the category. Please try again or contact support.",
                null
            );
        }
    }
    public function getCategoryDetails($currentSchool, $categoryid)
    {
        try {
            $category = AnnouncementCategory::where("school_branch_id", $currentSchool->id)
                ->find($categoryid);

            if (!$category) {
                throw new AppException(
                    "Announcement Category ID '{$categoryid}' not found for school branch ID '{$currentSchool->id}'.",
                    404,
                    "Category Not Found 🔎",
                    "The announcement category details you requested could not be found. Please verify the ID and ensure it exists at your school.",
                    null
                );
            }

            return $category;
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to retrieve announcement category details. Error: " . $e->getMessage(),
                500,
                "Data Retrieval Failed 🛑",
                "A system error occurred while trying to load the category details. Please try again or contact support.",
                null
            );
        }
    }
}
