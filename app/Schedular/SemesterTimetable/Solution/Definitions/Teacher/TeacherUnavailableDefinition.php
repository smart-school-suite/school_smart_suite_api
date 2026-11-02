<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions\Teacher;

use App\Schedular\SemesterTimetable\Solution\Exceptions\CannotApplyRemedy;
use App\Schedular\SemesterTimetable\Solution\Support\RequestEdit;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherUnavailable as Violation;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Teacher\TeacherUnavailableBlocker;
use App\Schedular\SemesterTimetable\Solution\DTO\Remedy;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\Support\Cost;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;
use App\Schedular\SemesterTimetable\Solution\Support\Text;

class TeacherUnavailableDefinition extends TeacherBusyDefinition
{
    protected function name(): string
    {
        return 'Teacher outside preferred hours';
    }

    public function violationKey(): ?string
    {
        return Violation::KEY;
    }

    public function blockerBuilder(): ?string
    {
        return TeacherUnavailableBlocker::class;
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        $teacherId = $obstacle->get('teacher_id') ?? ($issues[0] ?? null)?->subject->get('teacher_id');
        $issue = $issues[0] ?? null;

        $relax = $teacherId && $issue ? [new Remedy(
            action: Remedy::RELAX,
            target: $obstacle,
            label: 'Allow ' . ($context->constraints->teacher($teacherId)['name'] ?? 'the teacher') . ' to teach outside their preferred hours on ' . Text::when($issue->subject),
            changes: [['field' => 'allow_outside_preference', 'from' => false, 'to' => true]],
            result: [
                'teacher_id' => $teacherId,
                'day' => $issue->day(),
                'start_time' => $issue->subject->get('start_time'),
                'end_time' => $issue->subject->get('end_time'),
                'allow_outside_preference' => true,
            ],
            cost: Cost::RELAX_PREFERENCE,
        )] : [];

        return [...parent::clearObstacle($obstacle, $issues, $context), ...$relax];
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return Text::teacher($obstacle, $context) . ' prefers not to teach at ' . Text::period($obstacle);
    }

    public function applyToRequest(array $request, array $step, RequestEdit $edit): array
    {
        if ($step['action'] === Remedy::RELAX) {
            throw new CannotApplyRemedy("A teacher's preferred hours are changed in their availability settings, so this step has to be done there first.");
        }

        return parent::applyToRequest($request, $step, $edit);
    }
}
