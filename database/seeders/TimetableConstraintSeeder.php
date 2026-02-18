<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintRegistry;
use App\Models\Constraint\ConstraintCategory;
use App\Models\Constraint\ConstraintType;
use App\Models\Constraint\SemesterTimetableConstraint;

class TimetableConstraintSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $constraintTypes = [
             [
                 "name" => "Hard Constraint",
                 "program_name" => "hard",
                 "description" => "Hard constraints are mandatory rules that must always be satisfied during timetable generation. Any timetable that violates a hard constraint is considered invalid and cannot be accepted. Examples include preventing overlapping classes for the same teacher or classroom, ensuring a course is scheduled exactly the required number of times, and respecting institution-defined unavailable periods."
             ],
             [
                 "name" => "Soft Constraint",
                 "program_name" => "soft",
                 "description" => "Soft constraints are preference-based rules that guide the scheduler toward producing higher-quality timetables but may be relaxed when necessary to achieve a feasible solution. Violations of soft constraints do not invalidate the timetable; instead, they may reduce the timetable quality score. Examples include preferred teaching times, minimizing gaps between lessons, or distributing courses evenly across the week."
             ]
         ];

         $constraintCategories = [
             [
                 "name" => "Semester Timetable Constraint",
                 "program_name" => "semester_timetable_constraint",
                 "description" => "Constraints that apply to the scheduling of courses within a semester timetable. These constraints can be either hard or soft and may include rules related to course scheduling, teacher availability, classroom usage, and student preferences."
             ],
             [
                 "name" => "Exam Timetable Constraint",
                 "program_name" => "exam_timetable_constraint",
                 "description" => "Constraints that apply to the scheduling of exams within an exam timetable. These constraints can be either hard or soft and may include rules related to exam scheduling, invigilator availability, exam room usage, and student preferences."
             ]
         ];

         foreach($constraintTypes as $constraintType){
              ConstraintType::create([
                 'name' => $constraintType['name'],
                 'program_name' => $constraintType['program_name'],
                 'description' => $constraintType['description']
              ]);
         }

         foreach($constraintCategories as $constraintCategory){
              ConstraintCategory::create([
                 'name' => $constraintCategory['name'],
                 'program_name' => $constraintCategory['program_name'],
                 'description' => $constraintCategory['description']
              ]);
         }


        $softConstraints = ConstraintRegistry::getSoftConstraintGuides();

        foreach($softConstraints as $softConstraint){
            SemesterTimetableConstraint::create([
                'name' => $softConstraint->name,
                'program_name' => $softConstraint->program_name,
                'code' => $softConstraint->code,
                'description' => $softConstraint->description,
                'constraint_type_id' => ConstraintType::where('program_name', 'soft')->first()->id,
                'constraint_category_id' => ConstraintCategory::where('program_name', 'semester_timetable_constraint')->first()->id,
            ]);
        }

        $hardConstraints = ConstraintRegistry::getHardConstraintGuides();

        foreach($hardConstraints as $hardConstraint){
            SemesterTimetableConstraint::create([
                'name' => $hardConstraint->name,
                'program_name' => $hardConstraint->program_name,
                'code' => $hardConstraint->code,
                'description' => $hardConstraint->description,
                'constraint_type_id' => ConstraintType::where('program_name', 'hard')->first()->id,
                'constraint_category_id' => ConstraintCategory::where('program_name', 'semester_timetable_constraint')->first()->id,
            ]);
        }
    }
}
