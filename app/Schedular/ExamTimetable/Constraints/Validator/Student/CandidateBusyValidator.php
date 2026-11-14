<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Student;

use App\Constant\Violation\ExamTimetable\Student\CandidateBusy;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\BaseValidator;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Placement\Support\Time;

/**
 * With candidate groups: the course's candidates must not be sitting
 * another exam at the requested time, nor another exam of this timetable
 * they also sit.
 */
class CandidateBusyValidator extends BaseValidator
{
    public function check(array $params, State $state): array
    {
        $context = RequestContext::fromPayload();
        $courseId = $params['course_id'] ?? null;

        if (!$courseId || !$context->hasCandidateGroups() || !$this->hasWindow($params)) {
            return [];
        }

        $start = Time::toMinutes($params['start_time']);
        $end = Time::toMinutes($params['end_time']);
        $blockers = [];

        foreach ($context->candidateBusyWindows($courseId, $params['date']) as $window) {
            if (!Time::overlaps($start, $end, $window['start'], $window['end'])) {
                continue;
            }
            $blockers[] = array_filter([
                'key' => CandidateBusy::KEY,
                'course_id' => $courseId,
                'date' => $params['date'],
                'start_time' => Time::toTime($window['start']),
                'end_time' => Time::toTime($window['end']),
                'student_count' => $window['students'],
                'busy_course_id' => $window['course_id'],
                'source' => $window['source'],
                'conflict' => $this->conflict($params),
            ], fn($value) => $value !== null);
        }

        if (isset($state->dateGrid[$params['date']])) {
            foreach ($state->sessionsOverlapping($params['date'], $start, $end, $courseId) as $session) {
                $blockers[] = [
                    'key' => CandidateBusy::KEY,
                    'course_id' => $courseId,
                    'date' => $params['date'],
                    'start_time' => $session['start_time'],
                    'end_time' => $session['end_time'],
                    'student_count' => count(array_intersect_key($context->candidatesOf($courseId), $context->candidatesOf($session['course_id']))),
                    'busy_course_id' => $session['course_id'],
                    'source' => 'this_timetable',
                    'conflict' => $this->conflict($params),
                ];
            }
        }

        return $blockers;
    }
}
