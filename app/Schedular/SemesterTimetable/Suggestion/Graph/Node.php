<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Graph;

class Node
{
    public string $id;
    public string $type;
    public array $meta;
    public function __construct(string $id, string $type, array $meta = [])
    {
        $this->id = $id;
        $this->type = $type;
        $this->meta = $meta;
    }
}
