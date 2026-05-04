<?php

namespace App\Schedular\ExamTimetable\Core;

class State
{
    public array $grid = [];
    public array $violations = ["hard" => [], "soft" => []];
    public function __construct() {}
}
