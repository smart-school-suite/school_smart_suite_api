<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Graph;

class ConflictGraph
{
    protected array $edges = [];
    protected array $nodes = [];
    /**
     * Fast lookup maps
     */
    protected array $adjacency = [];
    protected array $reverseAdjacency = [];

    public function addNode(Node $node): void
    {
        $this->nodes[$node->id] = $node;
    }

    public function addEdge(Node $from, Node $to): void
    {
        $this->edges[] = new Edge($from, $to);

        $this->adjacency[$from->id][] = $to;
        $this->reverseAdjacency[$to->id][] = $from;
    }

    /**
     * 🔥 Conflict Groups
     * - Only structural nodes
     * - Only mutual edges
     * - Uses DFS to group connected conflicts
     */
    public function getConflictGroups(): array
    {
        $visited = [];
        $groups = [];

        foreach ($this->nodes as $node) {

            if ($node->category !== 'structural') {
                continue;
            }

            if (isset($visited[$node->id])) {
                continue;
            }

            $group = $this->dfsConflictGroup($node, $visited);

            if (count($group) > 1) {
                $groups[] = $group;
            }
        }

        return $groups;
    }

    /**
     * DFS traversal for mutual structural conflicts
     */
    protected function dfsConflictGroup(Node $start, array &$visited): array
    {
        $stack = [$start];
        $group = [];

        while (!empty($stack)) {
            $node = array_pop($stack);

            if (isset($visited[$node->id])) {
                continue;
            }

            $visited[$node->id] = true;
            $group[] = $node;

            $neighbors = $this->adjacency[$node->id] ?? [];

            foreach ($neighbors as $neighbor) {

                if ($neighbor->category !== 'structural') {
                    continue;
                }

                // 🔥 Only include if mutual
                if ($this->hasMutualEdge($node, $neighbor)) {
                    $stack[] = $neighbor;
                }
            }
        }

        return $group;
    }

    /**
     * Check if A → B and B → A exist
     */
    protected function hasMutualEdge(Node $a, Node $b): bool
    {
        $reverse = $this->adjacency[$b->id] ?? [];

        foreach ($reverse as $node) {
            if ($node->id === $a->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * 🔥 Dependencies
     * Return ALL edges (no filtering)
     */
    public function getDependencies(): array
    {
        return $this->edges;
    }

    /**
     * Optional helper: get blockers of a node
     */
    public function getBlockers(string $nodeId): array
    {
        return $this->adjacency[$nodeId] ?? [];
    }
}
