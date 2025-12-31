<?php

namespace App\Services\Analytics\Operational\Aggregates\Retension;

use Illuminate\Database\Query\Builder;
use App\Models\Educationlevels;
use App\Models\Gender;

class StudentLevelGenderRentionRate
{
    public function calculate(Builder $query, $filter)
    {
        if ($filter['level']) {
             return $this->byLevel($query);
        }

        if($filter['gender']){
             return $this->byGender($query);
        }
    }
    protected function byGender(Builder $query)
    {
        $genders = Gender::all();
        $genders->map(function ($gender) use ($query) {
            $enrolledStudent = $query->where("gender_id", $gender->id)->sum("value");
            $dropoutStudent = $query->where("gender_id", $gender->id)->sum("value");
            return [
                "gender" => $gender->name ?? "unknown",
                "gender_id" => $gender->id,
                "enrolled_student" => $enrolledStudent,
                "dropout_student" => $dropoutStudent,
                "retained_student" => $enrolledStudent - $dropoutStudent,
                "retension_rate" => round($enrolledStudent - $dropoutStudent / $dropoutStudent * 100, 2)
            ];
        });
    }
    protected function byLevel(Builder $query)
    {
        $levels = Educationlevels::all();
        $levels->map(function ($level) use ($query) {
            $enrolledStudent = $query->where("level_id", $level->id)->sum("value");
            $dropoutStudent = $query->where("level_id", $level->id)->sum("value");
            return [
                "level_name" => $level->name ?? "unknown",
                "level_number" => $level->level ?? "unknown",
                "enrolled_student" => $enrolledStudent,
                "dropout_student" => $dropoutStudent,
                "retained_student" => $enrolledStudent - $dropoutStudent,
                "retension_rate" => round($enrolledStudent - $dropoutStudent / $dropoutStudent * 100, 2)
            ];
        });
    }
}
