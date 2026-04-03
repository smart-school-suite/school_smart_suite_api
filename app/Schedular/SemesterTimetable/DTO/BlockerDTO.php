<?php

namespace App\Schedular\SemesterTimetable\DTO;

class BlockerDTO
{
    public string $type;
    public array  $entity;
    public array $evidence;
    public array $conflict;
    public function __construct() {}
}
