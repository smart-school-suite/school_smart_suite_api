<?php

namespace App\Services\SemesterTimetableAI;

use App\Constant\Timetable\HardTimetableConstraints;
use App\Constant\Timetable\SoftTimetableConstraints;
use Illuminate\Support\Facades\Log;

class GeminiJsonService
{
    protected GeminiClient $geminiClient;
    public function __construct(GeminiClient $geminiClient)
    {
        $this->geminiClient = $geminiClient;
    }

    public function generateStructuredJson(string $userPrompt, array $scheduleData): array
    {
        $halls = $scheduleData['halls'];
        $courses = $scheduleData['courses'];
        $teachers = $scheduleData['teachers'];
        $hardDefaults = HardTimetableConstraints::getDefaultJson();
        $hardSchema   = HardTimetableConstraints::getJsonSchema();

        $softDefaults = SoftTimetableConstraints::getDefaultJson();
        $softSchema   = SoftTimetableConstraints::getJsonSchema();

        $combinedSchema = json_encode([
            '$schema' => 'https://json-schema.org/draft/2020-12/schema',
            'type' => 'object',
            'required' => ['hard_constraints', 'soft_constraints'],
            'properties' => [
                'hard_constraints' => json_decode($hardSchema, true)['properties']['hard_constraints']
                    ?? json_decode($hardSchema, true),
                'soft_constraints' => json_decode($softSchema, true)['properties']['soft_constraints']
            ],
            'additionalProperties' => false
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

       $prompt = <<<EOT
You are an expert academic timetable constraint compiler.

Your sole task is to convert a user's natural language request into a STRICTLY VALID JSON object that conforms EXACTLY to the provided schema.

──────────────── CORE OPERATING RULES ────────────────

1. DEFAULT-FIRST RULE (CRITICAL)
- You MUST start from the provided default JSON values.
- ONLY override fields when the user EXPLICITLY states or STRONGLY IMPLIES a change.
- If the user does NOT mention a constraint, leave it EXACTLY as the default.
- NEVER restate defaults unless they are changed.

2. NO INVENTION RULE (CRITICAL)
- NEVER invent constraints, values, ranges, IDs, or interpretations.
- NEVER infer numeric values unless the user provides them.
- NEVER add keys that do not exist in the schema.
- NEVER remove required keys.

3. HARD vs SOFT CLASSIFICATION RULE
Classify intent using language strength:

HARD (must go in hard_constraints or hard-like soft fields):
- Keywords: "must", "only", "never", "exactly", "fixed", "no exceptions", "cannot"
- Specific day + time combinations
- Explicit teacher/room/course locking

SOFT (preferences / optimization hints):
- Keywords: "prefer", "try", "if possible", "avoid", "should", "mostly"
- Time-of-day preferences (morning/afternoon/evening)
- Load balancing, spacing, comfort rules

If unsure, ALWAYS choose SOFT.

4. MINIMAL OVERRIDE RULE
- Modify the SMALLEST POSSIBLE FIELD that satisfies the request.
- Do NOT escalate a preference into a fixed assignment.
- Do NOT convert vague language into exact times.

──────────────── ENTITY MATCHING RULES ────────────────

- Use ONLY IDs and names from the provided lists.
- Match by exact name first; if not found, use the closest reasonable match.
- If still ambiguous, prefer a GENERIC constraint rather than a specific ID.

──────────────── CONSTRAINT MAPPING GUIDELINES ────────────────

Use the MOST SPECIFIC matching constraint:

- Exact course + day + time → course_fixed_time_slots OR fixed_assignments
- Teacher availability/unavailability → teacher_time_windows
- Room/hall usage concentration → hall_time_windows
- "Move classes to X time" → fixed_day_time_slots (NOT course-specific)
- "Heavy / high-credit courses in morning" → course_preferred_time_of_day
- Numeric limits (hours, gaps, frequency) → ONLY if numbers are mentioned

DO NOT:
- Convert time-of-day into clock times
- Assign teachers or rooms unless explicitly requested
- Create fixed assignments when a softer constraint exists

──────────────── OUTPUT RULES (ABSOLUTE) ────────────────

- Output ONLY a single JSON object
- JSON must validate against the provided schema
- No markdown
- No comments
- No explanations
- No trailing text

──────────────── AVAILABLE DATA ────────────────

Available halls/rooms:
$halls

Available courses:
$courses

Available teachers:
$teachers

──────────────── DEFAULT CONSTRAINTS ────────────────

Hard defaults:
$hardDefaults

Soft defaults:
$softDefaults

──────────────── STRICT JSON SCHEMA ────────────────

$combinedSchema

──────────────── USER REQUEST ────────────────

"$userPrompt"

──────────────── FINAL INSTRUCTION ────────────────

Return ONLY the JSON object.

EOT;

        try {
            $result = $this->geminiClient->generate($prompt, [
                "responseMimeType" => "application/json",
                "temperature"     => 0.1,
                "maxOutputTokens" => 4096,
            ]);

            $jsonText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            $parsed   = json_decode($jsonText, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('Invalid constraints JSON from Gemini', [
                    'raw' => $jsonText
                ]);

                return [
                    'hard_constraints' => HardTimetableConstraints::getDefaultArray(),
                    'soft_constraints' => SoftTimetableConstraints::getDefaultArray(),
                    'warning' => 'Invalid AI JSON, defaults applied'
                ];
            }

            return $parsed;
        } catch (\Throwable $e) {
            Log::error('Gemini constraint generation failed', [
                'message' => $e->getMessage()
            ]);

            return [
                'hard_constraints' => HardTimetableConstraints::getDefaultArray(),
                'soft_constraints' => SoftTimetableConstraints::getDefaultArray(),
                'error' => 'Gemini API failure'
            ];
        }
    }
}
