<?php

namespace App\Services\Ably;
use Ably\AblyRest;
class AblyService
{
    public function getAblyToken($authUser){
        $ably = new AblyRest(env('ABLY_KEY'));
        $token = $ably->auth->createTokenRequest([
            'clientId' => $authUser->id,
            'capability' => [
                "*" => ["publish", "subscribe", "presence"],
            ],
        ]);
        return $token;
    }
}
