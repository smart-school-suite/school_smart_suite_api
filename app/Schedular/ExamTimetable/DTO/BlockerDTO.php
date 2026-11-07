<?php

namespace App\Schedular\ExamTimetable\DTO;

class BlockerDTO
{
    public string $id;
    public string $type;
    /** What is in the way (a hall, an invigilator, a rule...). */
    public array $entity = [];
    /** The request it is in the way of. */
    public array $conflict = [];
    public function __construct() {}
}
