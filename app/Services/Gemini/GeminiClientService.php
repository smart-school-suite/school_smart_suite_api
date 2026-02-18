<?php

namespace App\Services\Gemini;

use GuzzleHttp\Client;
class GeminiClientService
{
    protected Client $client;
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.x-goog-api-key');
        $this->model  = config('services.gemini.model', 'gemini-1.5-flash');

        $this->client = new Client([
            'base_uri' => 'https://generativelanguage.googleapis.com/v1beta/',
            'timeout'  => 45,
        ]);
    }

    public function generate(string $prompt, array $config = []): array
    {
        $endpoint = "models/{$this->model}:generateContent";

        $body = [
            "contents" => [
                ["parts" => [["text" => $prompt]]]
            ],
            "generationConfig" => array_merge([
                "temperature"     => 0.1,
                "topP"            => 0.9,
                "maxOutputTokens" => 2048,
            ], $config)
        ];

        $response = $this->client->post($endpoint, [
            'headers' => [
                'Content-Type'  => 'application/json',
                'x-goog-api-key' => $this->apiKey,
            ],
            'json' => $body,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
