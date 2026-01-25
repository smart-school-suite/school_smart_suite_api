<?php

namespace App\Services\SemesterTimetableAI;

class GeminiIntentInterpreterService
{
    protected GeminiClient $geminiClient;

    public function __construct(GeminiClient $geminiClient)
    {
        $this->geminiClient = $geminiClient;
    }

    public function unrelatedResponse(string $userPrompt): string
    {
        $prompt = <<<EOT
You are a polite and helpful academic timetable assistant.

The user's question is NOT related to timetable scheduling.

Write a friendly, complete response (2–3 full sentences) that:
- briefly explains you cannot help with that topic
- politely redirects the user to timetable-related tasks
- suggests examples like creating a timetable, modifying schedules, or adding constraints

DO NOT:
- answer the user's original question
- use one-word or incomplete responses
- end abruptly

User message:
"$userPrompt"
EOT;

        $result = $this->geminiClient->generate($prompt, [
            "temperature" => 0.3,
            "maxOutputTokens" => 150,
        ]);

        $text = trim(
            $result['candidates'][0]['content']['parts'][0]['text'] ?? ''
        );

        if (strlen($text) < 40) {
            return "I’m here to help specifically with semester timetables. You can ask me to create a timetable, adjust class schedules, or add scheduling constraints to fit your academic needs.";
        }

        return $text;
    }
}
