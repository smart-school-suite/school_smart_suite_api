<?php

namespace App\Services\Analytics\Operational\Aggregates\Student;
use Illuminate\Support\Collection;
use App\Models\StudentSource as Source;
class StudentSource
{
    public static function calculate(Collection $query){
         $studentSources = Source::all();
       return  $studentSources->map(function ($source) use ($query) {
                $studentEnrollmentSource = $query->where("source_id", $source->id)->sum("value");
                return [
                      "source_id" => $source->id,
                      "value" => $studentEnrollmentSource,
                      "source_name" => $source->name
                 ];
         });

    }
}
