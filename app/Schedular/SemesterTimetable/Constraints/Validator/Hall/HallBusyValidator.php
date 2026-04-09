<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Hall;

use App\Constant\Violation\SemesterTimetable\Hall\HallBusy;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use Carbon\Carbon;

class HallBusyValidator implements ValidatorInterface
{
    public function check(ConstraintContext $context, array $params): array
    {
        $day = strtolower((string) ($params['day'] ?? ''));
        $hallId = $params['hall_id'] ?? null;
        $startRaw = $params['start_time'] ?? null;
        $endRaw   = $params['end_time'] ?? null;

        if ($day === '' || empty($startRaw) || empty($endRaw)) {
            return [];
        }

        $reqStart = Carbon::parse($startRaw);
        $reqEnd   = Carbon::parse($endRaw);

        if ($reqEnd->lessThanOrEqualTo($reqStart)) {
            return [];
        }

        if ($hallId === null) {
            return $this->checkAllHallsForAvailability($context, $day, $reqStart, $reqEnd, $params);
        }

        return $this->checkSpecificHall($context, $day, $hallId, $reqStart, $reqEnd, $params);
    }

    private function checkAllHallsForAvailability(
        ConstraintContext $context,
        string $day,
        Carbon $reqStart,
        Carbon $reqEnd,
        array $params
    ): array {
        $hallBusySlots = $context->hBusySlotsFor($day);
        $hallsWithConflicts = [];
        $uniqueHallIds = [];

        foreach ($hallBusySlots as $hbp) {
            $hbpStartRaw = $hbp['start_time'] ?? null;
            $hbpEndRaw   = $hbp['end_time'] ?? null;

            if (empty($hbpStartRaw) || empty($hbpEndRaw)) {
                continue;
            }

            $hbpStart = Carbon::parse($hbpStartRaw);
            $hbpEnd   = Carbon::parse($hbpEndRaw);

            if ($hbpEnd->lessThanOrEqualTo($hbpStart)) {
                continue;
            }

            $currentHallId = $hbp['hall_id'] ?? null;
            $uniqueHallIds[$currentHallId] = true;

            $overlaps = $reqStart->lt($hbpEnd) && $hbpStart->lt($reqEnd);

            if ($overlaps) {
                $hallsWithConflicts[$currentHallId] = [
                    'key'        => HallBusy::KEY,
                    'hall_id'    => $currentHallId,
                    'day'        => $hbp['day'] ?? $day,
                    'start_time' => $hbpStart->format('H:i'),
                    'end_time'   => $hbpEnd->format('H:i'),
                    "conflict" => array_filter([
                        "course_id" => $params["course_id"] ?? null,
                        "hall_id" => $params["hall_id"] ?? null,
                        "slot_type" => $params["slot_type"] ?? null,
                        "teacher_id" => $params["teacher_id"] ?? null,
                        "day" => $params["day"] ?? null,
                        "start_time" => $params["start_time"] ?? null,
                        "end_time" => $params["end_time"] ?? null,
                    ])
                ];
            }
        }

        foreach (array_keys($uniqueHallIds) as $hallId) {
            if (!isset($hallsWithConflicts[$hallId])) {
                return [];
            }
        }
        return array_values($hallsWithConflicts);
    }

    private function checkSpecificHall(
        ConstraintContext $context,
        string $day,
        mixed $hallId,
        Carbon $reqStart,
        Carbon $reqEnd,
        array $params
    ): array {
        $blockers = [];

        foreach ($context->hBusySlotsFor($day)->filter(fn($hbp) => ($hbp["hall_id"] ?? null) === $hallId) as $hbp) {
            $hbpStartRaw = $hbp['start_time'] ?? null;
            $hbpEndRaw   = $hbp['end_time'] ?? null;

            if (empty($hbpStartRaw) || empty($hbpEndRaw)) {
                continue;
            }

            $hbpStart = Carbon::parse($hbpStartRaw);
            $hbpEnd   = Carbon::parse($hbpEndRaw);

            if ($hbpEnd->lessThanOrEqualTo($hbpStart)) {
                continue;
            }

            $overlaps = $reqStart->lt($hbpEnd) && $hbpStart->lt($reqEnd);

            if ($overlaps) {
                $blockers[] = [
                    'key'        => HallBusy::KEY,
                    'hall_id'    => $hbp['hall_id'] ?? null,
                    'day'        => $hbp['day'] ?? $day,
                    'start_time' => $hbpStart->format('H:i'),
                    'end_time'   => $hbpEnd->format('H:i'),
                    "conflict" => array_filter([
                        "course_id" => $params["course_id"] ?? null,
                        "hall_id" => $params["hall_id"] ?? null,
                        "slot_type" => $params["slot_type"] ?? null,
                        "teacher_id" => $params["teacher_id"] ?? null,
                        "day" => $params["day"] ?? null,
                        "start_time" => $params["start_time"] ?? null,
                        "end_time" => $params["end_time"] ?? null,
                    ])
                ];
            }
        }

        return $blockers;
    }
}
