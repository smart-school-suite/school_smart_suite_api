<?php

namespace App\Services\Course;
use App\Models\Course\CourseType;
use App\Exceptions\AppException;
class CourseTypeService
{
    public function createCourseType($data)
    {
        $existingType = CourseType::Where("name", $data["name"])
            ->first();
        if ($existingType) {
            throw new AppException(
                "A Course Type with this name already exists",
                409,
                "Duplicate Course Type",
                "{$data['name']} already exists. Please try another name."
            );
        }

        $courseType = CourseType::create([
            "name" => $data["name"],
            "description" => $data["description"] ?? null,
            "key" => $data["key"],
            "background_color" => $data["background_color"],
            "text_color" => $data["text_color"]
        ]);

        return $courseType;
    }

    public function updateCourseType(array $data, $id)
    {
        $courseType = CourseType::find($id);

        if (!$courseType) {
            throw new AppException(
                "Course Type not found",
                404,
                "Not Found",
                "The Course Type with ID {$id} does not exist."
            );
        }

        $existingType = CourseType::where("name", $data["name"])
            ->where("id", "!=", $courseType->id)
            ->first();

        if ($existingType) {
            throw new AppException(
                "A Course Type with this name already exists",
                409,
                "Duplicate Course Type",
                "The name '{$data['name']}' is already used by another Course Type."
            );
        }

        $courseType->update([
            "name"            => $data["name"],
            "description"     => $data["description"] ?? null,
            "key"             => $data["key"] ?? $courseType->key,
            "background_color" => $data["background_color"] ?? $courseType->background_color,
            "text_color"      => $data["text_color"] ?? $courseType->text_color,
        ]);

        return $courseType->fresh();
    }

    public function deactivateCourseType($courseTypeId)
    {
        $courseType = CourseType::find($courseTypeId);
        if (!$courseType) {
            throw new AppException(
                "Course Type Not Found",
                400,
                "Course Type Not Found",
                "Course Type Not Found Please ensure that the Course Type has not been deleted and try again"
            );
        }

        if ($courseType->status == "inactive") {
            throw new AppException(
                "Course Type Already Deactivated",
                409,
                "Course Type Already Deactivated",
                "Course Type Has Already been deactivated you can now only activate the hall"
            );
        }

        $courseType->status = "inactive";
        $courseType->save();
        return $courseType;
    }

    public function activateCourseType($courseTypeId)
    {
        $courseType = CourseType::find($courseTypeId);
        if (!$courseType) {
            throw new AppException(
                "Course Type Not Found",
                400,
                "Course Type Not Found",
                "Course Type Not Found Please ensure that the Course Type has not been deleted and try again"
            );
        }

        if ($courseType->status == "active") {
            throw new AppException(
                "Course Type Already Activated",
                409,
                "Course Type Already Activated",
                "Course Type Has Already been Activated you can now only deactivate the hall"
            );
        }

        $courseType->status = "active";
        $courseType->save();
        return $courseType;
    }

    public function getAllCourseTypes()
    {
        $courseTypes = CourseType::all();
        return $courseTypes;
    }

    public function getActiveCourseTypes()
    {
        $activeCourseTypes = CourseType::where("status", "active")->get();
        return $activeCourseTypes;
    }

    public function deleteCourseType($courseTypeId)
    {
        $courseType = CourseType::find($courseTypeId);
        if (!$courseType) {
            throw new AppException(
                "Course Type Not Found",
                400,
                "Course Type Not Found",
                "Course Type Not Found Please ensure that the Course Type has not been deleted and try again"
            );
        }

        $courseType->delete();
         return $courseType;
    }
}
