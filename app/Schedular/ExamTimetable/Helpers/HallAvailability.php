<?php

namespace App\Schedular\ExamTimetable\Helpers;

use App\Schedular\ExamTimetable\Context\RequestContext;
use Illuminate\Support\Collection;

class HallAvailability
{

    /**
     * Hall Availability Service
     *
     * Priority levels:
     *  1 — Free hall, fits candidates alone               → flat hall entry
     *  2 — Free halls only, grouped                       → hall_groups entry
     *  3 — Free hall + busy halls grouped                 → hall_groups entry
     *  4 — Excluded (busy, startTime mismatch)            → never appears in output
     *  5 — Busy hall, remaining space fits alone          → flat hall entry
     *  6 — Busy halls only, grouped                       → hall_groups entry
     *
     * Output shape
     * ─────────────────────────────────────────────────────────────────────────────
     * Solo entry (priority 1 or 5):
     * {
     *   "hall_id":          "...",
     *   "hall_name":        "...",
     *   "capacity":    200,
     *   "candidate_groups": [...],   // empty array for free halls (P1)
     *   "invigilators":     [...],   // empty array for free halls (P1)
     *   "priority":         1
     * }
     *
     * Group entry (priority 2, 3, or 6):
     * {
     *   "hall_groups": [
     *     {
     *       "hall_id":          "...",
     *       "hall_name":        "...",
     *       "capacity":    200,
     *       "candidate_groups": [...],   // populated only if hall is busy in this slot
     *       "invigilators":     [...]    // populated only if hall is busy in this slot
     *     },
     *     ...
     *   ],
     *   "priority": 2
     * }
     */
    // =========================================================================
    // Public entry point
    // =========================================================================

    public function availableHalls(string $date, string $startTime, string $endTime): Collection
    {
        $context = RequestContext::fromPayload();
        $candidateCount = $context->candidateCount();
        $halls          = $context->halls();

        [$freeHalls, $busyHalls] = $this->classifyHalls($halls, $date, $startTime, $context);

        $results = collect();

        $this->applyPriority1($freeHalls, $candidateCount, $results);
        $this->applyPriority2($freeHalls, $candidateCount, $results);
        $this->applyPriority3($freeHalls, $busyHalls, $candidateCount, $results);
        $this->applyPriority5($busyHalls, $candidateCount, $results);
        $this->applyPriority6($busyHalls, $candidateCount, $results);

        return $results->sortBy('priority')->values();
    }

    // =========================================================================
    // Classification
    // =========================================================================

    /**
     * Splits halls into two buckets.
     *
     * $freeHalls — no slot on $date, or no slot matching $startTime.
     *   Added keys:
     *     'available_capacity' => int   (= capacity, nothing is used yet)
     *     'candidate_groups'   => []    (always empty — nothing booked here)
     *     'invigilators'       => []    (always empty)
     *
     * $busyHalls — busy exactly at $startTime AND still has remaining space.
     *   Added keys:
     *     'existing_candidates' => int
     *     'remaining_capacity'  => int
     *     'candidate_groups'    => array   (from the matched slot)
     *     'invigilators'        => array   (from the matched slot)
     *
     * Halls that are completely full are silently dropped.
     * Halls busy at a DIFFERENT time are treated as free (Priority 4 exclusion
     * applies only when the start_time matches).
     */
    private function classifyHalls(
        Collection $halls,
        string $date,
        string $startTime,
        object $context
    ): array {
        $freeHalls = collect();
        $busyHalls = collect();

        foreach ($halls as $hall) {
            $busySlots = $context->hallBusySlots($hall['hall_id'])
                ->where('date', $date)
                ->first();

            if (!$busySlots) {
                $freeHalls->push($this->asFreeHall($hall));
                continue;
            }

            $matchedSlot = collect($busySlots['slots'])
                ->firstWhere('start_time', $startTime);

            if (!$matchedSlot) {
                $freeHalls->push($this->asFreeHall($hall));
                continue;
            }

            $existingCandidates = collect($matchedSlot['candidate_groups'])
                ->sum('candidate_count');

            $remainingCapacity = $hall['capacity'] - $existingCandidates;

            if ($remainingCapacity <= 0) {
                continue;
            }

            $hall['existing_candidates'] = $existingCandidates;
            $hall['remaining_capacity']  = $remainingCapacity;
            $hall['candidate_groups']    = $matchedSlot['candidate_groups'] ?? [];
            $hall['invigilators']        = $matchedSlot['invigilators']     ?? [];

            $busyHalls->push($hall);
        }

        return [$freeHalls, $busyHalls];
    }

    // =========================================================================
    // Priority 1 — Free hall, fits alone
    // =========================================================================

    private function applyPriority1(
        Collection $freeHalls,
        int $candidateCount,
        Collection &$results
    ): void {
        foreach ($freeHalls as $hall) {
            if ($hall['capacity'] >= $candidateCount) {
                $results->push($this->buildSoloEntry($hall, 1));
            }
        }
    }

    // =========================================================================
    // Priority 2 — Free halls only, grouped
    // =========================================================================

    private function applyPriority2(
        Collection $freeHalls,
        int $candidateCount,
        Collection &$results
    ): void {
        $undersized = $freeHalls->filter(
            fn($h) => $h['capacity'] < $candidateCount
        )->values();

        if ($undersized->count() < 2) {
            return;
        }

        $combinations   = $this->findCombinations($undersized, $candidateCount, fn($h) => $h['capacity']);
        $seenSignatures = collect();

        foreach ($combinations as $combo) {
            $signature = $this->comboSignature($combo);
            if ($seenSignatures->contains($signature)) {
                continue;
            }
            $seenSignatures->push($signature);

            // Longer combos → slightly higher sub-priority number
            $subPriority = 2 + (count($combo) - 2) * 0.1;

            $results->push($this->buildGroupEntry($combo, $subPriority));
        }
    }

    // =========================================================================
    // Priority 3 — Free hall + busy halls grouped
    // =========================================================================

    private function applyPriority3(
        Collection $freeHalls,
        Collection $busyHalls,
        int $candidateCount,
        Collection &$results
    ): void {
        $undersizedFree = $freeHalls->filter(
            fn($h) => $h['capacity'] < $candidateCount
        )->values();

        if ($undersizedFree->isEmpty() || $busyHalls->isEmpty()) {
            return;
        }

        $seenSignatures = collect();

        foreach ($undersizedFree as $freeHall) {
            $needed = $candidateCount - $freeHall['capacity'];

            $busyCombos = $this->findCombinations(
                $busyHalls,
                $needed,
                fn($h) => $h['remaining_capacity'],
                minSize: 1
            );

            foreach ($busyCombos as $busyCombo) {
                $fullCombo = array_merge([$freeHall], $busyCombo);
                $signature = $this->comboSignature($fullCombo);

                if ($seenSignatures->contains($signature)) {
                    continue;
                }
                $seenSignatures->push($signature);

                $subPriority = 3 + (count($busyCombo) - 1) * 0.1;

                $results->push($this->buildGroupEntry($fullCombo, $subPriority));
            }
        }
    }

    // =========================================================================
    // Priority 5 — Busy hall, remaining space fits alone
    // =========================================================================

    private function applyPriority5(
        Collection $busyHalls,
        int $candidateCount,
        Collection &$results
    ): void {
        foreach ($busyHalls as $hall) {
            if ($hall['remaining_capacity'] >= $candidateCount) {
                $results->push($this->buildSoloEntry($hall, 5));
            }
        }
    }

    // =========================================================================
    // Priority 6 — Busy halls only, grouped
    // =========================================================================

    private function applyPriority6(
        Collection $busyHalls,
        int $candidateCount,
        Collection &$results
    ): void {
        $insufficient = $busyHalls->filter(
            fn($h) => $h['remaining_capacity'] < $candidateCount
        )->values();

        if ($insufficient->count() < 2) {
            return;
        }

        $combinations   = $this->findCombinations($insufficient, $candidateCount, fn($h) => $h['remaining_capacity']);
        $seenSignatures = collect();

        foreach ($combinations as $combo) {
            $signature = $this->comboSignature($combo);
            if ($seenSignatures->contains($signature)) {
                continue;
            }
            $seenSignatures->push($signature);

            $subPriority = 6 + (count($combo) - 2) * 0.1;

            $results->push($this->buildGroupEntry($combo, $subPriority));
        }
    }

    // =========================================================================
    // Output builders
    // =========================================================================

    /**
     * Flat entry for a solo hall (Priority 1 or 5).
     *
     * Free halls (P1) always have empty candidate_groups and invigilators
     * because nothing is booked there yet.
     *
     * Busy halls (P5) carry the groups and invigilators already attached
     * during classification so the caller can see who is already in the hall.
     */
    private function buildSoloEntry(array $hall, int|float $priority): array
    {
        return [
            'hall_id'          => $hall['hall_id'],
            'hall_name'        => $hall['hall_name'],
            'capacity'    => $hall['capacity'],
            'candidate_groups' => $hall['candidate_groups'] ?? [],
            'invigilators'     => $hall['invigilators']     ?? [],
            'priority'         => $priority,
        ];
    }

    /**
     * Group entry for a combination of halls (Priority 2, 3, or 6).
     *
     * Each hall inside the group carries:
     *   - candidate_groups and invigilators when it is a busy hall
     *     (so the caller sees exactly who is already seated there)
     *   - empty arrays when it is a free hall
     *     (nothing is booked there yet)
     */
    private function buildGroupEntry(array $combo, int|float $priority): array
    {
        $hallGroups = array_map(fn(array $hall): array => [
            'hall_id'          => $hall['hall_id'],
            'hall_name'        => $hall['hall_name'],
            'capacity'    => $hall['capacity'],
            'candidate_groups' => $hall['candidate_groups'] ?? [],
            'invigilators'     => $hall['invigilators']     ?? [],
        ], $combo);

        return [
            'hall_groups' => $hallGroups,
            'priority'    => $priority,
        ];
    }

    // =========================================================================
    // Helper — Free hall decorator
    // =========================================================================

    /** Attach empty booking fields to a hall that has no active slot. */
    private function asFreeHall(array $hall): array
    {
        $hall['available_capacity'] = $hall['capacity'];
        $hall['candidate_groups']   = [];
        $hall['invigilators']       = [];
        return $hall;
    }

    // =========================================================================
    // Helper — Generic combination finder
    // =========================================================================

    /**
     * Returns all subsets of $items (size >= $minSize) whose combined value
     * from $capacityFn is >= $needed, sorted shortest-first.
     *
     * @param  Collection $items
     * @param  int        $needed
     * @param  callable   $capacityFn
     * @param  int        $minSize
     * @return array[]
     */
    private function findCombinations(
        Collection $items,
        int $needed,
        callable $capacityFn,
        int $minSize = 2
    ): array {
        $itemsArray  = $items->values()->all();
        $count       = count($itemsArray);
        $validCombos = [];

        for ($mask = 1; $mask < (1 << $count); $mask++) {
            $subset   = [];
            $capacity = 0;

            for ($i = 0; $i < $count; $i++) {
                if ($mask & (1 << $i)) {
                    $subset[]  = $itemsArray[$i];
                    $capacity += $capacityFn($itemsArray[$i]);
                }
            }

            if (count($subset) >= $minSize && $capacity >= $needed) {
                $validCombos[] = $subset;
            }
        }

        usort($validCombos, fn($a, $b) => count($a) <=> count($b));

        return $validCombos;
    }

    // =========================================================================
    // Helper — Combination deduplication
    // =========================================================================

    /**
     * Produces a stable string key for a combination so we never emit
     * the same set of halls twice.
     *
     * ["hall-b", "hall-a", "hall-c"] → "hall-a|hall-b|hall-c"
     */
    private function comboSignature(array $combo): string
    {
        $ids = array_map(fn($h) => $h['hall_id'], $combo);
        sort($ids);
        return implode('|', $ids);
    }
}
