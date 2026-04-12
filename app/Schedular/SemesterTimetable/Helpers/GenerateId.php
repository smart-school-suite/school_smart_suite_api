<?php

namespace App\Schedular\SemesterTimetable\Helpers;

class GenerateId
{
    public function generateId($data)
    {
        $parameters = [...$data];

        $hash = hash('sha256', json_encode($parameters));

        return substr($hash, 0, 16);
    }
}
