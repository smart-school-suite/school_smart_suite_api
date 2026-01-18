<?php

namespace App\Services\Student;

use App\Models\StudentParentRelationship;

class StudentParentRelationshipService
{
    public function getActiveStudentParentRelationship(){
         return StudentParentRelationship::where("status", "active")->get();
    }

    public function getAllStudentParentRelationship(){
         return StudentParentRelationship::all();
    }
}
