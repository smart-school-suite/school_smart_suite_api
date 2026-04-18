<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Engine;

use App\Schedular\SemesterTimetable\Suggestion\Graph\GraphBuilder;

class DayProcessor
{
   public function process($diagnostic): array {
         $graph = (new GraphBuilder())->build($diagnostic);
         $groups = $graph->getConflictGroups();
         $dependencies = $graph->getDependencies();
        //  $builder = new ScenarioBuilder();
        //  return $builder->build($groups, $dependencies);
        return [
             "groups" => $groups,
             "dependencies" => $dependencies
        ];
   }
}
