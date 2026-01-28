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

    public function generateStructuredJson(
        string $userPrompt,
        array $scheduleData,
        $contextSchema = null
    ): array {
        $halls   = $scheduleData['halls']   ?? [];
        $courses = $scheduleData['courses'] ?? [];
        $teachers = $scheduleData['teachers'] ?? [];

        $hardDefaults = HardTimetableConstraints::getDefaultJson();
        $softDefaults = SoftTimetableConstraints::getDefaultJson();

        $hardSchema = HardTimetableConstraints::getJsonSchema();
        $softSchema = SoftTimetableConstraints::getJsonSchema();

        $prompt = $contextSchema
            ? $this->buildEvolvePrompt($userPrompt, $halls, $courses, $teachers, $contextSchema, $hardDefaults, $softDefaults)
            : $this->buildDefaultPrompt($userPrompt, $halls, $courses, $teachers, $hardDefaults, $softDefaults, $hardSchema, $softSchema);

        try {
            $result = $this->geminiClient->generate($prompt, [
                "responseMimeType" => "application/json",
                "temperature"      => 0.1,
                "maxOutputTokens"  => 5000,
            ]);

            $jsonText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            $parsed   = json_decode($jsonText, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('Invalid constraints JSON from Gemini', ['raw' => $jsonText]);
                return $this->fallbackDefaultsWithWarning();
            }

            return $parsed;
        } catch (\Throwable $e) {
            Log::error('Gemini constraint generation failed', ['message' => $e->getMessage()]);
            return $this->fallbackDefaultsWithError();
        }
    }

    private static function buildDefaultPrompt(
        string $userPrompt,
         $halls,
         $courses,
         $teachers,
        string $hardDefaults,
        string $softDefaults,
        string $hardSchema,
        string $softSchema
    ): string {
        $combinedSchema = self::generateCombinedSchema($hardSchema, $softSchema);

        return <<<EOT
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

HARD: "must", "only", "never", "exactly", "fixed", "no exceptions", "cannot", specific day+time locks
SOFT: "prefer", "try", "if possible", "avoid", "should", "mostly", general time-of-day preferences

If unsure → ALWAYS SOFT.

4. MINIMAL OVERRIDE RULE
- Change the smallest possible field.
- Never escalate preference → fixed rule.

──────────────── ENTITY MATCHING RULES ────────────────

- Use ONLY IDs/names from the lists below.
- Exact name match first → closest reasonable match if needed.
- Ambiguous? → prefer generic constraint.

──────────────── CONSTRAINT MAPPING GUIDELINES ────────────────

Most specific match wins:
- Exact course + day + time     → course_fixed_time_slots or fixed_assignments
- Teacher availability          → teacher_time_windows
- Room concentration            → hall_time_windows
- "Heavy courses in morning"    → course_preferred_time_of_day
- Numeric limits                → only if numbers given

──────────────── AVAILABLE DATA ────────────────

Halls/rooms:   $halls
Courses:       $courses
Teachers:      $teachers

──────────────── DEFAULT VALUES ────────────────

Hard defaults:
$hardDefaults

Soft defaults:
$softDefaults

──────────────── SCHEMA ────────────────

$combinedSchema

──────────────── USER REQUEST ────────────────

"$userPrompt"

Return ONLY valid JSON — no markdown, no comments, no explanation.
EOT;
    }

    private static function buildEvolvePrompt(
        string $userPrompt,
         $halls,
         $courses,
         $teachers,
        string $contextSchema,
        string $hardDefaults,
        string $softDefaults
    ): string {
        return <<<EOT
You are an expert at **evolving** academic timetable constraints.

You are given:
• the PREVIOUS version of the constraints (JSON)
• a new natural language instruction from the user

Your task:
- Start from the PREVIOUS JSON (do NOT go back to defaults)
- Apply ONLY the changes explicitly requested or strongly implied in the new instruction
- Keep everything else **exactly** as it was in the previous version
- Output a new version that still conforms to the overall schema

──────────────── CORE EVOLVE RULES ────────────────

1. PRESERVE EVERYTHING NOT MENTIONED
- If the user does not talk about a field → keep its current value unchanged
- Do NOT reset to defaults
- Do NOT remove constraints that already exist unless explicitly asked

2. MINIMAL SURGICAL CHANGES
- Only modify the smallest necessary part
- Never turn a soft preference into a hard rule unless clearly instructed
- Never invent new constraint types

3. HARD/SOFT CLASSIFICATION remains the same as before

──────────────── PREVIOUS CONSTRAINTS (start here!) ────────────────

$contextSchema

──────────────── ENTITY LISTS (for reference / ID matching) ────────────────

Halls/rooms:   $halls
Courses:       $courses
Teachers:      $teachers

──────────────── DEFAULTS (only use if user explicitly asks to reset) ────────────────

Hard defaults:
$hardDefaults

Soft defaults:
$softDefaults

──────────────── NEW USER INSTRUCTION ────────────────

"$userPrompt"

──────────────── OUTPUT ────────────────

Return ONLY the updated JSON object — nothing else.
EOT;
    }

    private static function generateCombinedSchema(string $hardSchema, string $softSchema): string
    {
        $hardProps = json_decode($hardSchema, true)['properties']['hard_constraints'] ?? json_decode($hardSchema, true);
        $softProps = json_decode($softSchema, true)['properties']['soft_constraints'] ?? json_decode($softSchema, true);

        $schema = [
            '$schema' => 'https://json-schema.org/draft/2020-12/schema',
            'type' => 'object',
            'required' => ['hard_constraints', 'soft_constraints'],
            'properties' => [
                'hard_constraints' => $hardProps,
                'soft_constraints'  => $softProps,
            ],
            'additionalProperties' => false
        ];

        return json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    private function fallbackDefaultsWithWarning(): array
    {
        return [
            'hard_constraints' => HardTimetableConstraints::getDefaultArray(),
            'soft_constraints'  => SoftTimetableConstraints::getDefaultArray(),
            'warning' => 'Invalid AI response — defaults restored'
        ];
    }

    private function fallbackDefaultsWithError(): array
    {
        return [
            'hard_constraints' => HardTimetableConstraints::getDefaultArray(),
            'soft_constraints'  => SoftTimetableConstraints::getDefaultArray(),
            'error' => 'Constraint generation failed'
        ];
    }
}
