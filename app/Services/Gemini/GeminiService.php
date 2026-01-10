<?php

namespace App\Services\Gemini;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\Constant\Timetable\HardTimetableConstraints;
use App\Constant\Timetable\SoftTimetableConstraints;

class GeminiService
{
    protected $client;
    protected $apiKey;
    protected $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.x-goog-api-key');
        $this->model  = config('services.gemini.model', 'gemini-1.5-flash');

        $this->client = new Client([
            'base_uri' => 'https://generativelanguage.googleapis.com/v1beta/',
            'timeout'  => 60,
        ]);
    }

    public function generateStructuredJson(string $userPrompt, array $courses): array
    {
        $hardDefaults = HardTimetableConstraints::getDefaultJson();
        $hardSchema   = HardTimetableConstraints::getJsonSchema();

        $softDefaults = SoftTimetableConstraints::getDefaultJson();
        $softSchema   = SoftTimetableConstraints::getJsonSchema();

        $combinedSchema = json_encode([
            '$schema' => 'https://json-schema.org/draft/2020-12/schema',
            'type' => 'object',
            'required' => ['hard_constraints', 'soft_constraints'],
            'properties' => [
                'hard_constraints' => json_decode($hardSchema, true)['properties']['hard_constraints'] ?? json_decode($hardSchema, true),
                'soft_constraints' => json_decode($softSchema, true)['properties']['soft_constraints']
            ],
            'additionalProperties' => false
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $prompt = <<<EOT
You are a precise and professional academic timetable configuration expert.

Your task:
Generate a single JSON object containing both hard_constraints and soft_constraints based on the user's request.

Important rules:
- Start exactly from these default values (optimized for a convenient, balanced, and fair timetable):
Hard defaults:
$hardDefaults

Soft defaults:
$softDefaults

- ONLY modify values if the user explicitly mentions or strongly implies a change.
  Examples:
  - "School starts at 8:00" → change hard_constraints.operational_period.start_time to "08:00"
  - "No classes on Friday" → add hard_constraints.operational_period.constrains with appropriate override
  - "Teachers should not teach more than 6 hours a day" → set soft_constraints.teacher_max_daily_hours to 6.0
  - "Prefer practicals in the morning" → change soft_constraints.course_preferred_time_of_day.practical to "morning"

  - If the user says nothing specific about a section → return the defaults unchanged.
  - Never add, remove, or rename fields.
  - Never invent new fields.
  - Output ONLY valid JSON. No explanations, no markdown, no extra text.

  Strict combined JSON Schema you MUST follow exactly:
  $combinedSchema

  User request: "$userPrompt"

  Return only the final JSON object with this exact structure:
  {
  "hard_constraints": { ... },
  "soft_constraints": { ... }
  }
EOT;

        $endpoint = "models/{$this->model}:generateContent";

        $body = [
            "contents" => [
                ["parts" => [["text" => $prompt]]]
            ],
            "generationConfig" => [
                "responseMimeType" => "application/json",   // Critical for structured output
                "temperature"     => 0.1,                   // Very low = highly deterministic
                "topP"            => 0.9,
                "maxOutputTokens" => 4096,
            ]
        ];

        try {
            $response = $this->client->post($endpoint, [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'x-goog-api-key' => $this->apiKey,
                ],
                'json' => $body,
            ]);

            $result   = json_decode($response->getBody()->getContents(), true);
            $jsonText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

            $parsed = json_decode($jsonText, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('Gemini returned invalid constraints JSON', [
                    'raw' => $jsonText,
                    'prompt_length' => strlen($prompt)
                ]);

                // Fallback: return merged defaults
                return [
                    'hard_constraints' => HardTimetableConstraints::getDefaultArray(),
                    'soft_constraints' => SoftTimetableConstraints::getDefaultArray(),
                    'warning' => 'AI returned invalid JSON, using defaults'
                ];
            }

            return $parsed;
        } catch (\Exception $e) {
            Log::error('Gemini API error during constraint generation', [
                'message' => $e->getMessage()
            ]);

            // Safe fallback
            return [
                'hard_constraints' => HardTimetableConstraints::getDefaultArray(),
                'soft_constraints' => SoftTimetableConstraints::getDefaultArray(),
                'error' => 'API request failed'
            ];
        }
    }
}
