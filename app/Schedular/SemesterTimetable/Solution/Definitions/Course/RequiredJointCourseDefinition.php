<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions\Course;

use App\Constant\Constraint\SemesterTimetable\Course\RequiredJointCourse as Constraint;
use App\Constant\Violation\SemesterTimetable\Course\RequiredJointCourse as Violation;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Course\RequiredJointCourseBlocker;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Course\RequiredJointCourseDiagnostic;
use App\Schedular\SemesterTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\SemesterTimetable\Solution\DTO\Issue;
use App\Schedular\SemesterTimetable\Solution\DTO\Remedy;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\Support\Cost;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;
use App\Schedular\SemesterTimetable\Solution\Support\Time;

class RequiredJointCourseDefinition extends AbstractDefinition
{
    protected function name(): string
    {
        return 'Joint course';
    }

    public function constraintKey(): ?string
    {
        return Constraint::KEY;
    }

    public function violationKey(): ?string
    {
        return Violation::KEY;
    }

    public function diagnosticBuilder(): ?string
    {
        return RequiredJointCourseDiagnostic::class;
    }

    public function blockerBuilder(): ?string
    {
        return RequiredJointCourseBlocker::class;
    }

    public function adjustRequest(Issue $issue, SolutionContext $context): array
    {
        $remedies = $this->reschedule($issue->subject, $context->periodDuration($issue->day()), [], $context, Cost::MOVE_JOINT_COURSE);
        $remedies[] = $this->remove($issue->subject, Cost::REMOVE_JOINT_COURSE, context: $context);

        return $remedies;
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        $window = $this->window($obstacle);

        if (!$window || !$obstacle->day()) {
            return [];
        }

        return [
            ...$this->reschedule($obstacle, $window[1] - $window[0], $this->issueWindows($issues, $obstacle->day()), $context, Cost::MOVE_JOINT_COURSE + Cost::MOVE_OTHER_REQUEST),
            $this->remove($obstacle, Cost::REMOVE_JOINT_COURSE, context: $context),
        ];
    }

    private function reschedule(Subject $target, int $length, array $avoid, SolutionContext $context, int $baseCost): array
    {
        $window = $this->window($target);
        $day = $target->day();

        if (!$window || !$day) {
            return [];
        }

        $teacherId = $target->get('teacher_id');
        $hallId = $target->get('hall_id');
        $others = $context->jointCourses($day)->reject(fn($course) => $course['start'] === $window[0] && ($course['course_id'] ?? null) === $target->get('course_id'));

        $accepts = fn(string $candidateDay, int $start, int $end) => !$others->contains(fn($course) => Time::overlaps($start, $end, $course['start'], $course['end']))
            && (!$teacherId || $context->teacherFree($teacherId, $candidateDay, $start, $end))
            && (!$hallId || $context->hallFree($hallId, $candidateDay, $start, $end));

        return collect($context->findWindows($day, $length, $window[0], $accepts, $avoid, ['joint']))
            ->reject(fn($candidate) => $candidate['start'] === $window[0] && $candidate['end'] === $window[1])
            ->take(SolutionContext::MAX_ALTERNATIVES)
            ->map(function ($candidate) use ($target, $window, $day, $baseCost, $teacherId, $hallId) {
                $resized = $candidate['start'] === $window[0];
                $times = Time::toTime($candidate['start']) . '-' . Time::toTime($candidate['end']);

                return new Remedy(
                    action: $resized ? Remedy::MODIFY : Remedy::MOVE,
                    target: $target,
                    label: $resized
                        ? 'Change the joint course on ' . ucfirst($day) . " to {$times} so it matches the period length"
                        : 'Move the joint course on ' . ucfirst($day) . ' from ' . Time::toTime($window[0]) . '-' . Time::toTime($window[1]) . " to {$times}",
                    changes: [
                        ['field' => 'start_time', 'from' => Time::toTime($window[0]), 'to' => Time::toTime($candidate['start'])],
                        ['field' => 'end_time', 'from' => Time::toTime($window[1]), 'to' => Time::toTime($candidate['end'])],
                    ],
                    result: [
                        'day' => $day,
                        'course_id' => $target->get('course_id'),
                        'start_time' => Time::toTime($candidate['start']),
                        'end_time' => Time::toTime($candidate['end']),
                    ],
                    cost: ($resized ? intdiv($baseCost, 2) : $baseCost) + Cost::shift($candidate['shift']),
                    occupies: array_filter([
                        'day' => $day,
                        'start' => $candidate['start'],
                        'end' => $candidate['end'],
                        'teacher_id' => $teacherId,
                        'hall_id' => $hallId,
                        'exclusive' => true,
                    ]),
                );
            })
            ->values()
            ->all();
    }
}
