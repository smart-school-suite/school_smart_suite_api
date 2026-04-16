<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Registry;

use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;

class HandlerRegistry
{
    protected array $handlers;

    public function __construct()
    {
        $this->handlers = [
            // new AssignmentHandler(),
            // new FreePeriodHandler(),
            // new OperationalPeriodHandler(),
        ];
    }

    public function get(string $type): ?SuggestionHandler
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($type)) {
                return $handler;
            }
        }

        return null;
    }
}
