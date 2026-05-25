<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Engine;


class ConflictGroupBuilder
{
    public function buildsoft(array $softConstraints): array
    {
        $groups = [];
        $visited = [];

        foreach ($softConstraints as $A) {
            if (isset($visited[$A['id']])) continue;
            $group = [$A];
            $visited[$A['id']] = true;

            foreach ($A['blockers'] as $blocker) {

                $B = $softConstraints[$blocker->id] ?? null;
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
    public function buildHard(array $hardConstraints): array
    {
        $groups = [];
        $processedIds = [];

        foreach ($hardConstraints as $id => $constraint) {
            if (isset($processedIds[$id])) continue;

            $group = [];

            $group[] = array_merge([
                'id' => $constraint['id'],
                'type' => $constraint['type'],
            ], $constraint['details']);

            $processedIds[$id] = true;

            foreach ($constraint['blockers'] as $blocker) {
                $group[] = array_merge(
                    ['id' => $blocker->id],
                    (array) $blocker->entity
                );

                $processedIds[$blocker->id] = true;
            }

            $groups[] = collect($group)->unique('id')->values()->all();
        }

        return $groups;
    }
}
