<?php

namespace App\Services\SchoolEvent;

use App\Exceptions\AppException;
use App\Models\EventCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EventCategoryService
{
    public function createEventCategory($currentSchool, $data)
    {
        $categoryExist = EventCategory::where("school_branch_id", $currentSchool->id)
            ->where("name", $data['name'])
            ->exists();
        if ($categoryExist) {
            throw new AppException(
                "Category already exists",
                409,
                "Duplicate Category",
                "An event category with the name '{$data['name']}' already exists in this school branch.",
                "/event-categories"
            );
        }

        $category = EventCategory::create([
            'school_branch_id' => $currentSchool->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => true
        ]);

        return $category;
    }

    public function updateEventCategory($currentSchool, $updateData, $eventCategoryId)
    {
        try {
            $category = EventCategory::where("school_branch_id", $currentSchool->id)
                ->findOrFail($eventCategoryId);
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Event Category not found",
                404,
                "Category Missing",
                "The event category with ID $eventCategoryId could not be found in this school branch for updating.",
                "/event-categories"
            );
        }

        $cleanData = array_filter($updateData);

        if (isset($cleanData['name'])) {
            $categoryExist = EventCategory::where("school_branch_id", $currentSchool->id)
                ->where("name", $cleanData['name'])
                ->where("id", "!=", $eventCategoryId)
                ->exists();

            if ($categoryExist) {
                throw new AppException(
                    "Category name already exists",
                    409,
                    "Duplicate Category Name",
                    "An event category with the name '{$cleanData['name']}' already exists in this school branch.",
                    "/event-categories/" . $eventCategoryId . "/edit"
                );
            }
        }

        $category->update($cleanData);

        return $category;
    }

    public function deleteEventCategory($currentSchool, $eventCategoryId)
    {
        try {
            $eventCategory = EventCategory::where("school_branch_id", $currentSchool->id)
                ->findOrFail($eventCategoryId);
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Event Category not found",
                404,
                "Category Missing",
                "The event category with ID $eventCategoryId could not be found in this school branch for deletion.",
                "/event-categories"
            );
        }

        if ($eventCategory->events()->exists()) {
            throw new AppException(
                "Category is in use",
                409,
                "Category In Use",
                "The event category '{$eventCategory->name}' cannot be deleted because there are events currently assigned to it.",
                "/event-categories"
            );
        }

        $eventCategory->delete();
    }
    public function getEventCategory($currentSchool)
    {
        $eventCategories = EventCategory::where("school_branch_id", $currentSchool->id)
            ->get();

        if ($eventCategories->isEmpty()) {
            throw new AppException(
                "There are no event categories available for this school branch yet.",
                404,
                "No Event Categories Found",
                "We couldn't find any defined event categories for your school branch. Please ensure that event types (like 'Sports Day' or 'Parent-Teacher Meeting') have been created.", // Detailed User Message
                null
            );
        }

        return $eventCategories;
    }

    public function activateEventCategory($currentSchool, $eventCategoryId)
    {
        $eventCategory = EventCategory::where("school_branch_id", $currentSchool->id)
            ->find($eventCategoryId);

        if (!$eventCategory) {
            throw new AppException(
                "Event category ID '{$eventCategoryId}' not found for this school branch.",
                404,
                "Event Category Not Found",
                "We couldn't locate the event category you tried to activate. Please check the ID and ensure it belongs to your school branch.",
                null
            );
        }

        if ($eventCategory->status == 'active') {
            throw new AppException(
                "Event category '{$eventCategory->name}' is already active.",
                409,
                "Category Already Active",
                "This event category is already active and doesn't need to be activated again. No changes were made.",
                null
            );
        }

        $eventCategory->status = 'active';
        $eventCategory->save();
    }

    public function deactivateEventCategory($currentSchool, $eventCategoryId)
    {
        $eventCategory = EventCategory::where("school_branch_id", $currentSchool->id)
            ->find($eventCategoryId);

        if (!$eventCategory) {
            throw new AppException(
                "Event category ID '{$eventCategoryId}' not found for this school branch.",
                404,
                "Event Category Not Found ⚠️",
                "We couldn't locate the event category you tried to deactivate. Please check the ID and ensure it belongs to your current school branch.",
                null
            );
        }

        if ($eventCategory->status == "inactive") {
            throw new AppException(
                "Event category '{$eventCategory->name}' is already deactivated.",
                409,
                "Category Already Deactivated 💤",
                "This event category is already inactive and doesn't need to be deactivated again. No changes were applied.",
                null
            );
        }

        $eventCategory->status = 'inactive';
        $eventCategory->save();
    }

    public function getActiveEventCategory($currentSchool)
    {
        $eventCategories = EventCategory::where("school_branch_id", $currentSchool->id)
            ->where("status", "active")
            ->get();

        if ($eventCategories->isEmpty()) {
            throw new AppException(
                "There are no event categories available for this school branch yet.",
                404,
                "No Event Categories Found",
                "We couldn't find any defined event categories for your school branch. Please ensure that event types (like 'Sports Day' or 'Parent-Teacher Meeting') have been created.", // Detailed User Message
                null
            );
        }

        return $eventCategories;
    }

public function getEventCategoryDetails($currentSchool, $eventCategoryId)
{
    $eventCategory = EventCategory::where("school_branch_id", $currentSchool->id)
        ->find($eventCategoryId);

    if (!$eventCategory) {
        throw new AppException(
            "Event category ID '{$eventCategoryId}' not found for school branch ID '{$currentSchool->id}'.",
            404,
            "Event Category Not Found 🧐",
            "We couldn't find the details for the specific event category you requested. Please verify the Category ID is correct and belongs to your school branch.",
            null
        );
    }

    return $eventCategory;
}
}
