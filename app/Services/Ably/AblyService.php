<?php

namespace App\Services\Ably;

use Ably\AblyRest;
use Ably\Models\TokenParams; // <-- Add this import

class AblyService
{
    public function getAblyToken($authUser)
    {
        $ably = new AblyRest(env('ABLY_KEY'));

        $tokenParams = new TokenParams([
            'clientId' => (string) $authUser->id, // clientId must be string or null
            'capability' => [
                '*' => ['publish', 'subscribe', 'presence'],
            ],
            // Optional: add ttl, timestamp, etc.
            // 'ttl' => 3600, // 1 hour in seconds
        ]);

        $tokenRequest = $ably->auth->createTokenRequest($tokenParams);

        return $tokenRequest;
    }
}
