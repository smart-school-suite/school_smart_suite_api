<?php

namespace App\Services\Timetable;
use App\Services\Gemini\GeminiService;
class AiGenerateTimetableService
{
    protected GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }
    public function generateTimetable($prompt){
        $promptResponse = $this->geminiService->generate($prompt);
        return $promptResponse;
    }
}
