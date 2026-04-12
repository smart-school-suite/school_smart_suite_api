<?php

namespace App\Schedular\SemesterTimetable\DTO;

class BlockerDTO
{
    public string $id;
    public string $type;
    public array  $entity;
    public array $evidence;
    public array $conflict;
    public function __construct() {}
}
