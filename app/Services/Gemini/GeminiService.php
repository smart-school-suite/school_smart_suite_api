<?php

namespace App\Services\Gemini;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $client;
    protected $apiKey;
    protected $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.x-goog-api-key');
        $this->model  = config('services.gemini.model', 'gemini-2.5-flash');

        $this->client = new Client([
            'base_uri' => 'https://generativelanguage.googleapis.com/v1beta/',
            'timeout'  => 30,
        ]);
    }

    public function generate(string $prompt): string
    {
        $endpoint = "models/{$this->model}:generateContent";
Log::info('API Key Debug: ' . ($this->apiKey ? 'PRESENT (length: ' . strlen($this->apiKey) . ')' : 'MISSING'));
        $body = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ]
        ];

        $response = $this->client->post($endpoint, [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->apiKey,   // IMPORTANT
            ],
            'json' => $body,
        ]);

        $json = json_decode($response->getBody()->getContents(), true);

        return $json['candidates'][0]['content']['parts'][0]['text']
            ?? 'No response.';
    }
}
