<?php

namespace App\Schedular\ExamTimetable\Builders\ResponseBuilder;

use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\DTO\ResponseDTO;

class ResponseBuilder
{
   public function build(State $state){
        $response = new ResponseDTO();
        $response->status = match (true) {
            !empty($state->violations["hard"]) => "error",
            !empty($state->violations["soft"]) => "partial",
            default => "optimal",
        };
        $response->timetable = $state->dateGrid;
        $response->suggestions = [];
        $response->diagnostics = [];
        return $response;
   }
}
