<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Graph;

class GraphBuilder
{
    public function build($diagnostics): ConflictGraph
    {
        $graph = new ConflictGraph();
        foreach ($diagnostics as $diag) {

            $source = new Node(
                $diag['id'],
                $diag['type'],
                $diag
            );

            $graph->addNode($source);

            foreach ($diag['blockers'] as $blocker) {

                $target = new Node(
                    $blocker['id'],
                    $blocker['type'],
                    $blocker
                );

                $graph->addNode($target);

                $graph->addEdge($source, $target, $diag['reason']);
            }
        }

        return $graph;
    }
}
