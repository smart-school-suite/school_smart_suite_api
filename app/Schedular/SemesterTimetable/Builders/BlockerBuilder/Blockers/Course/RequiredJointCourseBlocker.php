<?php

namespace App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Course;

use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;

class RequiredJointCourseBlocker implements BlockerBuilder
{
   public static function type(): string
   {
       return "required_joint_course";
   }

   public function build($blocker): BlockerDTO
   {
       $course = $blocker->getCourse();
       $jointCourse = $course->jointCourse;

       return new BlockerDTO();
   }
}
