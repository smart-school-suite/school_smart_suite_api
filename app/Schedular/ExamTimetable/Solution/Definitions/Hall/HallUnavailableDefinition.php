<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Hall;

use App\Constant\Violation\ExamTimetable\Hall\HallUnavailable;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Hall\HallUnavailableBlocker;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

/** Not enough exam hall seats. Halls are school resources, so adding or freeing them is done by hand. */
class HallUnavailableDefinition extends AbstractDefinition
{
    public function violationKey(): ?string
    {
        return HallUnavailable::KEY;
    }

    public function blockerBuilder(): ?string
    {
        return HallUnavailableBlocker::class;
    }

    protected function name(): string
    {
        return 'Exam halls';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        if ($obstacle->get('reason') === 'not_an_exam_hall') {
            return Text::hall($obstacle, $context) . ' is not set up as an exam hall';
        }

        $when = $obstacle->get('dates') && count($obstacle->get('dates')) > 1
            ? 'on any of ' . count($obstacle->get('dates')) . ' dates'
            : 'on ' . Text::date($obstacle->date()) . ($obstacle->get('start_time') ? ' ' . Text::period($obstacle) : '');

        return 'Only ' . (int) $obstacle->get('seats_available') . ' exam hall seat(s) are free ' . $when . ', ' . (int) $obstacle->get('seats_needed') . ' are needed';
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        $missing = max(1, (int) $obstacle->get('seats_needed') - (int) $obstacle->get('seats_available'));

        return [$this->review($obstacle, $obstacle->get('reason') === 'not_an_exam_hall'
            ? 'Mark ' . Text::hall($obstacle, $context) . ' as an exam hall'
            : "Make at least {$missing} more exam hall seat(s) available", ['fix_in' => '/hall'])];
    }
}
