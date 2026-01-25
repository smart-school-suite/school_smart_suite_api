<?php

namespace App\Services\SemesterTimetableScheduler;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
class PreferenceSchedulingClient
{
    protected Client $client;
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.semester_timetable_scheduler.base_url', 'http://127.0.0.1:8080');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => 150.0,
            'headers'  => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function scheduleWithPreferences(array $body): ?array
    {
        try {
            $response = $this->client->post('/api/v1/schedule/with-preference', [
                'json' => $body,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode >= 200 && $statusCode < 300) {
                return json_decode($response->getBody()->getContents(), true);
            }

            Log::warning('Timetable scheduler returned non-success status', [
                'status' => $statusCode,
                'body'   => (string) $response->getBody(),
            ]);

            return null;
        } catch (GuzzleException $e) {
            Log::error('Failed to call timetable scheduler API', [
                'message' => $e->getMessage(),
                'code'    => $e->getCode(),
                'payload_size' => strlen(json_encode($body)),
            ]);

            // You can also re-throw or return structured error – depends on your app
            return null;
        }
    }
}
