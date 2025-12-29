<?php

namespace App\Services\Analytics\Academic\Filters;

use Illuminate\Database\Query\Builder;

class SchoolFailRateFilter
{
    public function apply(
        Builder $query,
        array $filter,
    ): Builder {
        if ($filter['level_id'] && $filter['exam_type_id']) {
            $query->where("level_id", $filter['level_id'])
                ->where("exam_type_id", $filter['exam_type_id']);
        }
        if ($filter['level_id'] && !$filter['exam_type_id']) {
            $query->where("level_id", $filter['level_id']);
        }
        if ($filter['exam_type_id'] && !$filter['level_id']) {
            $query->where("exam_type_id", $filter['exam_type_id']);
        }

        return $query;
    }
}
