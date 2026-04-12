<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Graph;

class Edge
{
    public Node $to;
    public Node $from;
    public string $reason;
    public function __construct(Node $to, Node $from, string $reason)
    {
        $this->to = $to;
        $this->from = $from;
        $this->reason = $reason;
    }
}
