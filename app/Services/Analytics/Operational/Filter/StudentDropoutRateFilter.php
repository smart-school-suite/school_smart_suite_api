<?php

namespace App\Services\Analytics\Operational\Filter;
use Illuminate\Support\Collection;

class StudentDropoutRateFilter
{
    public function apply(Collection $query, $filter)
    {
        if ($filter['level_id']  && $filter['gender_id']) {
            $query->where("level_id", $filter['level_id'])
                ->where("gender_id", $filter['gender_id']);
        }
        if ($filter['level_id'] && !$filter['gender_id']) {
            $query->where("level_id", $filter['level_id']);
        }
        if ($filter['level_id'] && !$filter['gender_id']) {
            $query->where("level_id", $filter['level_id']);
        }

        return $query;
    }
}
