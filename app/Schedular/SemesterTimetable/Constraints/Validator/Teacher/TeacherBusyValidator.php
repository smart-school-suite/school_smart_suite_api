<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Teacher;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherBusy;
use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use Carbon\Carbon;
class TeacherBusyValidator implements ValidatorInterface
{
  public function check(ConstraintContext $context, array $params): array
  {
      $day   = strtolower($params['day']);
      $start = Carbon::createFromFormat('H:i', $params['start_time']);
      $end   = Carbon::createFromFormat('H:i', $params['end_time']);
      $blockers = [];

      foreach ($context->tBusySlotsFor($params['teacher_id'], $day) as $tbw) {
          $tbwStart = Carbon::createFromFormat('H:i', $tbw['start_time']);
          $tbwEnd   = Carbon::createFromFormat('H:i', $tbw['end_time']);

          if ($start->lessThan($tbwEnd) && $end->greaterThan($tbwStart)) {
              $blockers[] = [
                  'key'        => TeacherBusy::KEY,
                  'day'        => $day,
                  'teacher_id' => $tbw['teacher_id'] ?? null,
                  'start_time' => $tbw['start_time'],
                  'end_time'   => $tbw['end_time'],
              ];
          }
      }

      return $blockers;
  }
}
