<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Graph;

class Edge
{
    public Node $to;
    public Node $from;
    public function __construct(Node $to, Node $from)
    {
        $this->to = $to;
        $this->from = $from;
    }
}
