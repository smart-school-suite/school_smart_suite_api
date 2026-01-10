<?php

namespace App\Services\Analytics\Operational\Aggregates\Student;

use App\Models\Educationlevels;
use Illuminate\Support\Collection;

class StudentLevelRegistrationAggregate
{
    public static function calculate(Collection $query)
    {
        $levels = Educationlevels::all();
        return $levels->map(function ($level) use ($query) {
            $registeredStudents = $query->where("level_id", $level->id)->sum("value");
            return [
                "level_id" => $level->id,
                "level_name" => $level->name,
                "level" => $level->level,
                "registered_students" => $registeredStudents
            ];
        });
    }
}
