<?php

namespace App\Services\Ably;

use Ably\AblyRest;
use Ably\Models\TokenParams; // <-- Add this import

class AblyService
{
    public function getAblyToken($authUser)
    {
        $ably = new AblyRest(env('ABLY_KEY'));

        $tokenParams = new TokenParams();
        $tokenParams->clientId = (string) $authUser->id;
        $tokenParams->capability = json_encode(['*' => ['publish', 'subscribe', 'presence']]);
        // $tokenParams->ttl = 3600; // optional

        $tokenRequest = $ably->auth->createTokenRequest($tokenParams->toArray());

        return $tokenRequest;
    }
}
