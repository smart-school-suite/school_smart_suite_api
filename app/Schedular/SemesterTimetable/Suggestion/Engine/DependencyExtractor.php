<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Engine;

class DependencyExtractor
{
    public function get(array $constraint, array $group): array
    {
        $groupIds = collect($group)->pluck('id');

        return collect($constraint['blockers'])
            ->filter(fn($b) => !$groupIds->contains($b->id))
            ->values()
            ->all();
    }
}
