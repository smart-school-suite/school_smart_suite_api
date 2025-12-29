<?php

namespace App\Services\Analytics\Academic\Aggregates\Resit;

use Illuminate\Database\Query\Builder;
use App\Models\Examtype;
use App\Models\Educationlevels;

class ResitTotalAggregate
{
    public function calculate(Builder $query, $filters)
    {
        if ($filters['exam_type'] ?? false) {
            return $this->byExamType($query);
        }

        if ($filters['level'] ?? false) {
            return $this->byLevel($query);
        }
    }

    protected function byExamType(Builder $query)
    {
        $examTypes = ExamType::where("type", "!=", "resit")->pluck('id');

        return $examTypes->map(function ($examTypeId) use ($query) {

            $resitCount = (clone $query)
                ->where('exam_type_id', $examTypeId)
                ->sum('value');
            return [
                'exam_type_id' => Examtype::find($examTypeId)->exam_name ?? "unknown",
                "resit_count" => $resitCount
            ];
        });
    }
    protected function byLevel(Builder $query)
    {
        $levels = Educationlevels::all()->pluck('id');
        return $levels->map(function ($levelId) use ($query) {

            $resitCount = (clone $query)
                ->where('level_id', $levelId)
                ->sum('value');

            return [
                'level_id'  => Educationlevels::find($levelId)->name,
                'pass_rate' =>  $resitCount,
            ];
        });
    }
}
