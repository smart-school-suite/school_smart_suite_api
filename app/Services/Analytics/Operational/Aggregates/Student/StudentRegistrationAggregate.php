<?php

namespace App\Services\Analytics\Operational\Aggregates\Student;

use App\Models\Gender;
use Illuminate\Support\Collection;

class StudentRegistrationAggregate
{
    public static function calculate(Collection $query)
    {
        $genders = Gender::all();
        $totalRegisteredStudents = $query->sum("value");
        $breakDown =  $genders->map(function ($gender) use ($query) {
            return [
                "total" => $query->where("gender_id", $gender->id)->sum("value"),
                "gender_id" => $gender->id,
                "gender" => $gender->name
            ];
        });

        return [
            "total_registered_students" => $totalRegisteredStudents,
            "breakdown" => $breakDown
        ];
    }
}
