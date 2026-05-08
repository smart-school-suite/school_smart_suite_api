<?php

namespace App\Schedular\ExamTimetable\Helpers;

use App\Schedular\ExamTimetable\Context\RequestContext;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InvigilatorAvailability
{
    public function getAvailableInvigilators(array $params): array
    {
        $startTime = $params['start_time'] ?? null;
        $endTime   = $params['end_time'] ?? null;
        $date      = $params['date'] ?? null;
        $courseId  = $params['course_id'] ?? null;

        if (!$date || !$startTime || !$endTime) {
            return [];
        }

        $context = RequestContext::fromPayload();
        $allInvigilators = $context->invigilators();

        $available = [];

        foreach ($allInvigilators as $inv) {
            $invId = $inv['invigilator_id'];

            $busyData = $context->invigilatorBusySlotDate($invId, $date);

            $busySlotsToday = [];
            if ($busyData instanceof Collection) {
                $busyData = $busyData->toArray();
            }
            if (isset($busyData['slots']) && is_array($busyData['slots'])) {
                $busySlotsToday = $busyData['slots'];
            }
            if (!$this->isFreeDuring($busySlotsToday, $startTime, $endTime)) {
                continue;
            }

            if ($courseId && in_array($courseId, $inv['course_taught'] ?? [])) {
                continue;
            }

            $busyMinutesToday = $this->getBusyMinutesOnDate($busySlotsToday);

            $available[] = [
                'invigilator_id'     => $invId,
                'name'               => $inv['name'],
                'course_taught'      => $inv['course_taught'] ?? [],
                'busy_minutes'       => $busyMinutesToday
            ];
        }

        usort($available, fn($a, $b) => $a['busy_minutes'] <=> $b['busy_minutes']);

        return $available;
    }

    private function isFreeDuring(array $busySlots, string $startTime, string $endTime): bool
    {
        foreach ($busySlots as $slot) {
            $slotStart = $slot['start_time'] ?? '';
            $slotEnd   = $slot['end_time'] ?? '';

            if (empty($slotStart) || empty($slotEnd)) {
                continue;
            }

            if (max($startTime, $slotStart) < min($endTime, $slotEnd)) {
                return false;
            }
        }
        return true;
    }
    private function getBusyMinutesOnDate(array $slots): int
    {
        $totalMinutes = 0;

        foreach ($slots as $slot) {
            $start = Carbon::parse($slot['start_time'] ?? null);
            $end   = Carbon::parse($slot['end_time'] ?? null);

            if ($start && $end) {
                $totalMinutes += $start->diffInMinutes($end);
            }
        }

        return $totalMinutes;
    }
}
