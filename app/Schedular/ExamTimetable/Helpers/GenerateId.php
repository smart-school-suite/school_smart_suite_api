<?php

namespace App\Schedular\ExamTimetable\Helpers;

class GenerateId
{
    /** A short stable hash: the same data always gives the same id. */
    public function generateId(array $data): string
    {
        $parameters = [...$data];
        ksort($parameters);

        return substr(hash('sha256', json_encode($parameters)), 0, 16);
    }
}
