<?php

namespace App\Exceptions\ExamTimetable;

use App\Exceptions\AppException;

/**
 * Generation is refused while a course this exam shares with other
 * specialties has no joint exam time: the timetable has to place it there.
 */
class JointCoursesWithoutTime extends AppException
{
    public const PATH = '/exam/joint-course-timetable';

    /** @param array<int, array{exam_joint_course_id: string, course_id: string, course_title: ?string}> $courses */
    public function __construct(private array $courses)
    {
        $count = count($courses);
        $titles = implode(', ', array_filter(array_column($courses, 'course_title')));

        parent::__construct(
            "{$count} joint course(s) have no exam time.",
            422,
            'Joint Courses Need A Time',
            ($titles ?: 'Some courses') . ($count === 1 ? ' is' : ' are')
                . ' shared with other specialties but ' . ($count === 1 ? 'has' : 'have')
                . ' no exam time yet. Set ' . ($count === 1 ? 'its' : 'their')
                . ' time on the Joint Course Timetable page, then generate again.',
            self::PATH
        );
    }

    public function courses(): array
    {
        return $this->courses;
    }
}
