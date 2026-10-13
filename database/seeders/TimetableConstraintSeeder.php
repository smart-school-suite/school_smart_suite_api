<?php

namespace Database\Seeders;

use App\Constant\Constraint\SemesterTimetable\Builder\ConstraintBuilder;
use App\Constant\Violation\SemesterTimetable\Builder\ViolationBuilder;
use App\Models\Constraint\SemTimetableBlocker;
use App\Models\Constraint\SemTimetableBlockerCategory;
use App\Models\Constraint\SemTimetableConstraint;
use App\Models\Constraint\SemTimetableConstraintCategory;
use App\Models\Constraint\SemTimetableConstraintType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class TimetableConstraintSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedConstraintTypes();
        $this->seedConstraintCategories();
        $this->seedBlockerCategories();
        $this->seedConstraints();
        $this->seedViolations();
    }

    private function seedConstraintTypes(): void
    {
        $types = [
            [
                "name" => "Hard Constraint",
                "key" => "hard",
                "description" => "Hard constraints are mandatory rules that must always be satisfied during timetable generation. Any timetable that violates a hard constraint is considered invalid and cannot be accepted. Examples include preventing overlapping classes for the same teacher or classroom, ensuring a course is scheduled exactly the required number of times, and respecting institution-defined unavailable periods."
            ],
            [
                "name" => "Soft Constraint",
                "key" => "soft",
                "description" => "Soft constraints are preference-based rules that guide the scheduler toward producing higher-quality timetables but may be relaxed when necessary to achieve a feasible solution. Violations of soft constraints do not invalidate the timetable; instead, they may reduce the timetable quality score. Examples include preferred teaching times, minimizing gaps between lessons, or distributing courses evenly across the week."
            ]
        ];

        foreach ($types as $type) {
            SemTimetableConstraintType::updateOrCreate(['key' => $type['key']], $type);
        }
    }

    private function seedConstraintCategories(): void
    {
        $categories = [
            [
                "name" => "Teacher Constraint",
                "key" => "teacher_constraint",
                "description" => "Constraints that apply to the scheduling of teachers within a semester timetable. These constraints can be either hard or soft and may include rules related to teacher availability, teaching load, and preferred teaching times."
            ],
            [
                "name" => "Course Constraint",
                "key" => "course_constraint",
                "description" => "Constraints that apply to the scheduling of courses within a semester timetable. These constraints can be either hard or soft and may include rules related to course scheduling, teacher availability, classroom usage, and student preferences."
            ],
            [
                "name" => "Hall Constraint",
                "key" => "hall_constraint",
                "description" => "Constraints that apply to the scheduling of halls within a semester timetable. These constraints can be either hard or soft and may include rules related to hall availability, capacity, and suitability for specific types of classes or events."
            ],
            [
                "name" => "Schedule Constraint",
                "key" => "schedule_constraint",
                "description" => "Constraints that apply to the overall scheduling within a semester timetable. These constraints can be either hard or soft and may include rules related to the sequencing of classes, avoiding conflicts, and optimizing the timetable for various criteria."
            ],
            [
                "name" => "Assignment Constraint",
                "key" => "assignment_constraint",
                "description" => "Constraints that apply to the assignment of resources (such as teachers, courses, halls) within a semester timetable. These constraints can be either hard or soft and may include rules related to ensuring that specific resources are assigned to certain time slots or that certain combinations of resources are avoided."
            ]
        ];

        foreach ($categories as $cat) {
            SemTimetableConstraintCategory::updateOrCreate(['key' => $cat['key']], $cat);
        }
    }

    private function seedBlockerCategories(): void
    {
        $categories = [
            [
                "name" => "Assignment Violation",
                "key" => "assignment_violation",
                "description" => "Constraints that apply to the assignment of resources (such as teachers, courses, halls) within a semester timetable. These constraints can be either hard or soft and may include rules related to ensuring that specific resources are assigned to certain time slots or that certain combinations of resources are avoided."
            ],
            [
                "name" => "Schedule Violation",
                "key" => "schedule_violation",
                "description" => "Constraints that apply to the overall scheduling within a semester timetable. These constraints can be either hard or soft and may include rules related to the sequencing of classes, avoiding conflicts, and optimizing the timetable for various criteria."
            ],
            [
                "name" => "Hall Violation",
                "key" => "hall_violation",
                "description" => "Constraints that apply to the scheduling of halls within a semester timetable. These constraints can be either hard or soft and may include rules related to hall availability, capacity, and suitability for specific types of classes or events."
            ],
            [
                "name" => "Course Violation",
                "key" => "course_violation",
                "description" => "Constraints that apply to the scheduling of courses within a semester timetable. These constraints can be either hard or soft and may include rules related to course scheduling, teacher availability, classroom usage, and student preferences."
            ],
            [
                "name" => "Teacher Violation",
                "key" => "teacher_violation",
                "description" => "Constraints that apply to the scheduling of teachers within a semester timetable. These constraints can be either hard or soft and may include rules related to teacher availability, teaching load, and preferred teaching times."
            ]
        ];

        foreach ($categories as $cat) {
            SemTimetableBlockerCategory::updateOrCreate(['key' => $cat['key']], $cat);
        }
    }

    private function seedConstraints(): void
    {
        $constraints = ConstraintBuilder::all();

        $categoriesByKey = SemTimetableConstraintCategory::pluck('id', 'key');
        $typesByKey = SemTimetableConstraintType::pluck('id', 'key');

        foreach ($constraints as $constraint) {
            $categoryId = $categoriesByKey[$constraint['category']] ?? null;
            $typeId = $typesByKey[$constraint['type']] ?? null;

            if (!$categoryId) {
                Log::warning("Constraint category not found: {$constraint['category']} (for constraint {$constraint['key']})");
            }

            if (!$typeId) {
                Log::warning("Constraint type not found: {$constraint['type']} (for constraint {$constraint['key']})");
            }

            SemTimetableConstraint::updateOrCreate(
                ['key' => $constraint['key']],
                [
                    "name" => $constraint["title"],
                    "description" => $constraint["description"],
                    "constraint_category_id" => $categoryId,
                    "constraint_type_id" => $typeId,
                    "is_suggestable" => !empty($constraint["suggestion_handler"]),
                    "is_blockable" => !empty($constraint["interpreter_handler"])
                ]
            );
        }
    }

    private function seedViolations(): void
    {
        $violations = ViolationBuilder::all();

        $categoriesByKey = SemTimetableBlockerCategory::pluck('id', 'key');

        foreach ($violations as $violation) {
            $categoryId = $categoriesByKey[$violation['category']] ?? null;

            if (!$categoryId) {
                Log::warning("Blocker category not found: {$violation['category']} (for violation {$violation['key']})");
            }

            SemTimetableBlocker::updateOrCreate(
                ['key' => $violation['key']],
                [
                    "name" => $violation["title"],
                    "description" => $violation["description"],
                    "is_resolvable" => !empty($violation["violation_handler"]),
                    "sem_blocker_category_id" => $categoryId
                ]
            );
        }
    }
}
