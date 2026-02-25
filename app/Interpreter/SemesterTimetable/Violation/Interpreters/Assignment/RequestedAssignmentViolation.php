<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Assignment;

use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Models\Courses;
use App\Models\Hall;
use App\Models\Teacher;

class RequestedAssignmentViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return 'requested_assignment_violation';
    }

    public function explain(array $blocker): string
    {
        $conflict = $blocker['conflict']['attempted_assignment'];
        $existingRequestedAssignment = $blocker['entity']['fixed_assignment'];
        $existingHall = Hall::find($existingRequestedAssignment['hall_id']);
        $existingCourse = Courses::find($existingRequestedAssignment['course_id']);
        $existingTeacher = Teacher::find($existingRequestedAssignment['teacher_id']);
        $conflictHall = Hall::find($conflict['hall_id']);
        $conflictCourse = Courses::find($conflict['course_id']);
        $conflictTeacher = Teacher::find($conflict['teacher_id']);
        return "Existing Requested Assignment,
        A session is already locked in for {$existingRequestedAssignment['day']} from {$existingRequestedAssignment['start_time']}
        to {$existingRequestedAssignment['end_time']} by {$existingTeacher->name} in {$existingHall->name} for course {$existingCourse->course_title}.
        Which conglicts with the attempted assigment of {$conflict['day']} from {$conflict['start_time']}
        to {$conflict['end_time']} by {$conflictTeacher->name} in {$conflictHall->name} for course {$conflictCourse->course_title}.";
    }
}
