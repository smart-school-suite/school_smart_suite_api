<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Hall;

use App\Constant\Violation\ExamTimetable\Hall\HallCapacityExceeded;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\ExamTimetable\Context\RequestContext;
use Override;

class HallCapacityValidator implements ValidatorInterface
{
    #[Override]
    public function check(array $params): ?array
    {
        $context = RequestContext::fromPayload();
        $hallGroup = $params["hall_group"];
        $hallId = $params["hall_id"];
        $candidateCount =  $context->candidateCount();
        if (!$hallGroup && $hallId) {
            return [];
        }

        if ($hallGroup) {
            $totalCapacity = 0;
            foreach ($hallGroup as $hallId) {
                $hallCapacity = $context->hallCapacity($hallId);
                $totalCapacity += $hallCapacity;
            }
            if ($totalCapacity > $candidateCount) {
                return [
                    'key'                => HallCapacityExceeded::KEY,
                    'hall_group'            => collect($hallGroup)->map(fn($hallId) => [
                        'hall_id' => $hallId,
                        "hall_capacity" => $context->hallCapacity($hallId)
                    ]),
                    "total_capacity" => $totalCapacity,
                    "total_candidates" => $candidateCount
                ];
            }
        }

        if ($hallId) {
            $hallCapacity = $context->hallCapacity($hallId);
            if ($candidateCount > $hallCapacity) {
                return [
                    'key'                => HallCapacityExceeded::KEY,
                    "hall_id" => $hallId,
                    'total_capacity' => $hallCapacity,
                    "total_candidate" => $candidateCount
                ];
            }
        }

        return [];
    }
}
