<?php

namespace App\Services\Analytics\Academic\Aggregates\Resit;

use App\Models\Examtype;
use Illuminate\Support\Collection;

class ExamTypeResitAggregate
{
    public static function calculate(Collection $query){
          $examTypes = Examtype::all();
          return $examTypes->map(function ($examType) use ($query) {
               return [
                 "exam_type_id" => $examType->id,
                 "exam_name" => $examType->exam_name ?? "unknown",
                 "semester_id" => $examType->semester ?? "unknown",
                 "total_resits" => $query->where("exam_type_id", $examType->id)->sum("value")
               ];
          });
    }
}
