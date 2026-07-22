<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Contracts;

interface ValidatorInterface
{
    public function check(array $params): ?array;
}
