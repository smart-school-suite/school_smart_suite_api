<?php

namespace App\Services\SemesterTimetableAI;

use Illuminate\Support\Facades\Log;
use Throwable;

class GeminiIntentService
{
    protected GeminiClient $geminiClient;

    protected array $allowedIntents = [
        'create_timetable',
        'modify_timetable',
        'add_constraint',
        'remove_constraint',
        'optimize_timetable',
        'ask_about_timetable',
        'unrelated',
    ];

    public function __construct(GeminiClient $geminiClient)
    {
        $this->geminiClient = $geminiClient;
    }

    public function classify(string $userPrompt): array
    {
        $prompt = <<<EOT
You are an intent classifier for a school timetable / class schedule assistant.

Return **only** one of these exact strings — nothing else, no quotes, no explanation, no prefix, no suffix:

create_timetable
modify_timetable
add_constraint
remove_constraint
optimize_timetable
ask_about_timetable
unrelated

Classification rules:
- create_timetable     → wants to generate, build, start, plan a new timetable/schedule
- modify_timetable     → wants to edit, update, change, adjust existing timetable
- add_constraint       → wants to add rules/limitations (no class Friday, max 5 hours/day, teacher unavailable mornings, etc.)
- remove_constraint    → wants to delete or relax existing rules/constraints
- optimize_timetable   → wants to improve, balance, fix conflicts, make timetable better
- ask_about_timetable  → asking questions about current/existing schedule ("what time is physics?", "am I free Wednesday?", "show my timetable")
- unrelated            → everything else (grades, payments, exams scores, weather, jokes, general questions, etc.)

User message:
"$userPrompt"
EOT;

        Log::debug('GeminiIntentService: Starting classification', ['user_prompt' => $userPrompt]);
        Log::debug('GeminiIntentService: Prompt sent to model', ['prompt_length' => strlen($prompt), 'prompt' => $prompt]);

        try {
            $result = $this->geminiClient->generate($prompt, [
                'temperature'     => 0.0,
                'maxOutputTokens' => 1024,   // ← Increased — most important fix
            ]);

            Log::debug('GeminiIntentService: Raw API response', [
                'response' => $result,
            ]);

            $rawText = '';
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $rawText = $result['candidates'][0]['content']['parts'][0]['text'];
            } else {
                // More defensive extraction + logging
                Log::warning('GeminiIntentService: No text found in candidates.content.parts', [
                    'candidates' => $result['candidates'] ?? null,
                    'finishReason' => $result['candidates'][0]['finishReason'] ?? 'unknown',
                ]);
            }

            $rawIntent = strtolower(trim($rawText));

            Log::debug('GeminiIntentService: Extracted raw intent', [
                'raw_text' => $rawText,
                'cleaned'  => $rawIntent,
                'finish_reason' => $result['candidates'][0]['finishReason'] ?? 'unknown',
            ]);

            $intent = 'unrelated';

            foreach ($this->allowedIntents as $allowed) {
                if ($rawIntent === $allowed) {
                    $intent = $allowed;
                    Log::info("GeminiIntentService: Exact match found", ['intent' => $intent]);
                    break;
                }
                // Optional: allow minor variations (extra space, period, etc.)
                if (str_starts_with($rawIntent, $allowed) || str_contains($rawIntent, $allowed)) {
                    $intent = $allowed;
                    Log::info("GeminiIntentService: Loose match found", [
                        'intent' => $intent,
                        'raw'    => $rawIntent,
                    ]);
                    break;
                }
            }

            if ($intent === 'unrelated' && $rawIntent !== '') {
                Log::warning('GeminiIntentService: Raw intent did not match any allowed value', [
                    'raw_intent' => $rawIntent,
                ]);
            }
        } catch (Throwable $e) {
            Log::error('GeminiIntentService: Classification failed', [
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
                'user_prompt' => $userPrompt,
            ]);
            $intent = 'unrelated';
        }

        Log::debug('GeminiIntentService: Classification finished', [
            'final_intent' => $intent,
        ]);

        return [
            'intent'       => $intent,
            'is_unrelated' => $intent === 'unrelated',
        ];
    }
}
