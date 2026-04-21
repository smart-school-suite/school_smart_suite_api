<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Engine;

class ConflictGroupBuilder
{
    public function build(array $constraints): array
    {
        $groups = [];
        $visited = [];

        foreach ($constraints as $A) {

            if (isset($visited[$A['id']])) continue;

            $group = [$A];
            $visited[$A['id']] = true;

            foreach ($A['blockers'] as $blocker) {

                $B = $constraints[$blocker->id] ?? null;
                if (!$B) continue;

                $isMutual = collect($B['blockers'])
                    ->contains(fn($b) => $b->id === $A['id']);

                if ($isMutual) {
                    $group[] = $B;
                    $visited[$B['id']] = true;
                }
            }

            $groups[] = $group;
        }

        return $groups;
    }
}
