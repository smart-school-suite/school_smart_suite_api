<?php

namespace App\Schedular\SemesterTimetable\Core;

class State
{
    public array $grid = [];
    public array $violations = [ "hard" => [], "soft" => [] ];
    public function __construct() {}
}
