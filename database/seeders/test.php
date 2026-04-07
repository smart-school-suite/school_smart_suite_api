<?php

namespace Database\Seeders;

use App\Models\InstructorAvailabilitySlot;
use Illuminate\Database\Seeder;
use App\Models\Teacher;
use Illuminate\Support\Facades\Log;

class test extends Seeder
{
    public function run(): void
    {

        $teacherId = Teacher::take(2)->pluck('id')->toArray();
        $availability = InstructorAvailabilitySlot::whereIn("teacher_id", $teacherId)->get();
        Log::debug("Instructor Avaliability",  [
            "teacher_ids" => $teacherId,
            "availability" => $availability
        ]);
        // $type = SemTimetableConstraintType::where("key", "soft")->first();
        // $constraints = SemTimetableConstraint::all();
        // foreach($constraints as $constraint) {
        //     $constraint->where("constraint_type_id", $type->id)->update(["status" => "inactive"]);
        //     $constraint->where("constraint_type_id", "!=", $type->id)->update(["status" => "active"]);
        // }
    }
}
