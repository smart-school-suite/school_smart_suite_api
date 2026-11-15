<?php

namespace App\Exceptions\ResitTimetable;

use App\Exceptions\AppException;

/**
 * Resit timetable generation is refused while a course this resit exam shares
 * with other specialties has no joint resit time: the timetable has to place it there.
 */
class ResitJointCoursesWithoutTime extends AppException
{
    public const PATH = '/resit/joint-course-timetable';

    /** @param array<int, array{resit_joint_course_id: string, course_id: string, course_title: ?string}> $courses */
    public function __construct(private array $courses)
    {
        $count = count($courses);
        $titles = implode(', ', array_filter(array_column($courses, 'course_title')));

        parent::__construct(
            "{$count} joint resit course(s) have no time.",
            422,
            'Joint Resits Need A Time',
            ($titles ?: 'Some courses') . ($count === 1 ? ' is' : ' are')
                . ' resat by other specialties too but ' . ($count === 1 ? 'has' : 'have')
                . ' no resit time yet. Set ' . ($count === 1 ? 'its' : 'their')
                . ' time on the Resit Joint Course Timetable page, then generate again.',
            self::PATH
        );
    }

    public function courses(): array
    {
        return $this->courses;
    }
}
