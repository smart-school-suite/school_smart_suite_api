<?php

namespace App\Services\SemesterTimetableAI;

class GeminiResponseIntepreterService
{
    protected GeminiClient $geminiClient;

    public function __construct(GeminiClient $geminiClient)
    {
        $this->geminiClient = $geminiClient;
    }

    public function interpretResponse($schedulerRequestInput, $schedulerResponseOutput)
    {
        $responseJson = is_string($schedulerResponseOutput)
            ? $schedulerResponseOutput
            : json_encode($schedulerResponseOutput, JSON_PRETTY_PRINT);

        $requestJson = is_string($schedulerRequestInput)
            ? $schedulerRequestInput
            : json_encode($schedulerRequestInput, JSON_PRETTY_PRINT);

        $prompt = $this->buildPrompt($requestJson, $responseJson);

        $result = $this->geminiClient->generate($prompt, [
            "temperature" => 0.3,
            "maxOutputTokens" => 2048,
        ]);

        return $this->extractTextFromResponse($result);
    }

    protected function buildPrompt(string $requestJson, string $responseJson): string
    {
        $basePrompt = <<<EOT
You are an expert AI academic timetable scheduling assistant. Your role is to analyze scheduling API responses and provide clear, actionable insights to users.

# Context
The scheduling system has processed a timetable request and returned a response with scheduling results, constraint validations, and diagnostics.

# Your Task
Analyze the scheduling response and provide a user-friendly interpretation that includes:

1. **Status Summary**: A clear, concise overview of whether the schedule was successfully generated
2. **Schedule Overview**: A brief summary of what was scheduled (days, courses, teachers)
3. **Issues Identification**: Clearly explain any constraint violations or problems
4. **Root Cause Analysis**: Explain WHY issues occurred based on the blockers provided
5. **Actionable Recommendations**: Provide specific, prioritized suggestions to resolve issues

# Response Format Guidelines
- Use clear, professional language suitable for academic administrators
- Structure your response with clear sections
- Highlight critical issues prominently
- Make recommendations specific and actionable
- Include relevant details (teacher names, course names, time slots) from the data
- If there are no issues, acknowledge the successful scheduling positively

# Scheduling Request Input
```json
{$requestJson}
```

# Scheduling API Response
```json
{$responseJson}
```

EOT;

<<<EOT


# Your Interpretation
Provide a comprehensive, user-friendly interpretation of the scheduling results:
EOT;

        return $basePrompt;
    }

    protected function extractTextFromResponse(array $response): string
    {
        if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            return $response['candidates'][0]['content']['parts'][0]['text'];
        }

        if (isset($response['candidates'][0]['content']['parts'])) {
            $texts = [];
            foreach ($response['candidates'][0]['content']['parts'] as $part) {
                if (isset($part['text'])) {
                    $texts[] = $part['text'];
                }
            }
            if (!empty($texts)) {
                return implode("\n", $texts);
            }
        }

        return "Unable to interpret the scheduling response. Please contact system administrator.";
    }

    public function getQuickStatus($schedulerResponseOutput): array
    {
        $response = is_array($schedulerResponseOutput)
            ? $schedulerResponseOutput
            : json_decode($schedulerResponseOutput, true);

        $status = $response['status'] ?? 'UNKNOWN';
        $hardConstraintsMet = $response['diagnostics']['summary']['hard_constraints_met'] ?? false;
        $softConstraintsMet = $response['diagnostics']['summary']['soft_constraints_met'] ?? false;

        $failedSoftCount = $response['diagnostics']['summary']['failed_soft_constraints_count'] ?? 0;
        $failedHardCount = $response['diagnostics']['summary']['failed_hard_constraints_count'] ?? 0;

        return [
            'status' => $status,
            'success' => $hardConstraintsMet,
            'optimal' => $softConstraintsMet,
            'issues_count' => $failedSoftCount + $failedHardCount,
            'message' => $response['diagnostics']['summary']['message'] ?? 'No summary available',
            'solve_time' => $response['metadata']['solve_time_seconds'] ?? null,
        ];
    }

    public function extractConstraintFailures($schedulerResponseOutput): array
    {
        $response = is_array($schedulerResponseOutput)
            ? $schedulerResponseOutput
            : json_decode($schedulerResponseOutput, true);

        $failures = [];

        $hardFailed = $response['diagnostics']['constraints']['hard']['failed'] ?? [];
        foreach ($hardFailed as $failure) {
            $failures[] = [
                'severity' => 'CRITICAL',
                'type' => $failure['constraint_failed']['type'] ?? 'UNKNOWN',
                'details' => $failure['constraint_failed']['details'] ?? [],
                'blockers' => $failure['blockers'] ?? [],
                'suggestions' => $failure['suggestions'] ?? [],
            ];
        }

        $softFailed = $response['diagnostics']['constraints']['soft']['failed'] ?? [];
        foreach ($softFailed as $failure) {
            $failures[] = [
                'severity' => 'WARNING',
                'type' => $failure['constraint_failed']['type'] ?? 'UNKNOWN',
                'details' => $failure['constraint_failed']['details'] ?? [],
                'blockers' => $failure['blockers'] ?? [],
                'suggestions' => $failure['suggestions'] ?? [],
            ];
        }

        return $failures;
    }
}
