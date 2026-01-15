<?php

namespace App\Services\Analytics\Academic\Aggregates\Resit;

use App\Models\Educationlevels;
use Illuminate\Support\Collection;

class LevelResitAggregate
{
    public static function calculate(Collection $query)
    {
        $levels = Educationlevels::all();
        return  $levels->map(function ($level) use ($query) {
            return [
                "level_id" => $level->id,
                "level_name" => $level->name ?? "unknown",
                "level_number" => $level->level ?? "unknown",
                "total_resits" => $query->where("level_id", $level->id)->sum("value") ?? 0
            ];
        });
    }
}
