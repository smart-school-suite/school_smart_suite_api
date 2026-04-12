<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Graph;

class ConflictGraph
{
    public array $edges;
    public array $nodes;

    public function addNode(Node $node)
    {
        $this->nodes[$node->id] = $node;
    }

    public function addEdge(Node $from, Node $to, string $reason)
    {
        $this->edges[] = new Edge($from, $to, $reason);
    }

    public function getConflictGroups(): array
    {
        $groups = [];

        foreach ($this->edges as $edge) {
            if ($edge->to->id === $edge->from->id) continue;

            $groups[] = [$edge->from, $edge->to];
        }

        return $groups;
    }
}
