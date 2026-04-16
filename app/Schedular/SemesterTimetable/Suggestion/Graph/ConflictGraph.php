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
            if (
                $edge->from->category === 'structural' &&
                $edge->to->category === 'structural'
            ) {
                $groups[] = [$edge->from, $edge->to];
            }
        }

        return $groups;
    }

    public function getDependencies(): array
    {
        $dependencies = [];

        foreach ($this->edges as $edge) {

            $isMutual = false;

            foreach ($this->edges as $other) {
                if (
                    $other->from->id === $edge->to->id &&
                    $other->to->id === $edge->from->id
                ) {
                    $isMutual = true;
                    break;
                }
            }

            if (!$isMutual) {
                $dependencies[] = $edge;
            }
        }

        return $dependencies;
    }
}
