<?php

namespace App\Schedular\ExamTimetable\Core;

class State
{
    public array $dateGrid = [];
    public array $violations = ["hard" => [], "soft" => []];
    public function __construct() {}
}
