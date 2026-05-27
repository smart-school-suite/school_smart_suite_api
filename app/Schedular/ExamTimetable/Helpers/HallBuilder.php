<?php

namespace App\Schedular\ExamTimetable\Helpers;

class HallBuilder
{
    public function buildGroupedHall(
        string $courseId,
        array $hall,
        array $invigs,
        int $candidates,
        string $specialtyId
    ): array {
        $result = [];
        $allocatedThisCall = 0;
        $hallGroups = collect($hall['hall_groups'] ?? []);

        foreach ($hallGroups as $hallGroup) {
            $hallId      = $hallGroup['hall_id'];
            $hallName    = $hallGroup['hall_name'];
            $capacity    = (int) $hallGroup['capacity'];
            $existingGroups = collect($hallGroup['candidate_groups'] ?? []);

            // Calculate available space
            $usedSpace = $existingGroups->sum('candidate_count');
            $availableSpace = $capacity - $usedSpace;

            // If no space left, skip this hall group
            if ($availableSpace <= 0) {
                continue;
            }

            // How many candidates we can put here
            $toAllocate = min($candidates, $availableSpace);

            if ($toAllocate <= 0) {
                break; // No more candidates to allocate
            }

            // Build new candidate group
            $newGroup = [
                'course_id'     => $courseId,
                'specialty_id'  => $specialtyId,
                'candidate_count' => $toAllocate,
            ];

            // Merge with existing groups
            $finalCandidateGroups = $existingGroups->isEmpty()
                ? [$newGroup]
                : [...$existingGroups, $newGroup];

            // Handle invigilators (safely)
            $invigilators = $hallGroup['invigilators'] ?? [];
            if (empty($invigilators) && !empty($invigs['invigilator_id'])) {
                $invigilators = [$invigs['invigilator_id']];
            }

            $result[] = [
                'hall_id'          => $hallId,
                'hall_name'        => $hallName,
                'candidate_groups' => $finalCandidateGroups,
                'invigilators'     => $invigilators,
            ];

            $allocatedThisCall += $toAllocate;
            $candidates -= $toAllocate;

            // Stop if we filled all remaining candidates
            if ($candidates <= 0) {
                break;
            }
        }

        return $result;
    }
    public function buildSingleHall(
        string $courseId,
        array $hall,
        array $invigs,
        int $candidateCount,
        string $specialtyId
    ): array {
        return [
            "hall_id" => $hall["hall_id"],
            "hall_name" => $hall["hall_name"],
            "candidate_groups" => [
                [
                    "course_id" => $courseId,
                    "specialty_id" => $specialtyId,
                    "candidate_count" => $candidateCount
                ]
            ],
            "invigilators" => [
                $invigs["invigilator_id"]
            ]
        ];
    }
}
