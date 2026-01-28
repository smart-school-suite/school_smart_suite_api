<?php

namespace App\Services\SemesterTimetableAI;

class GeminiSchedulerResponseInterpreterService
{
    protected GeminiClient $geminiClient;
    public function __construct(GeminiClient $geminiClient)
    {
        $this->geminiClient = $geminiClient;
    }

    public function intepretResponse(string $response) {

    }
}
