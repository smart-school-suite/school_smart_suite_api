<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\InvalidMessage;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\RegistrationToken;

class FireBaseService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(config('firebase.credentials'));
        $this->messaging = $factory->createMessaging();
    }

    /**
     * Send notification to a single device token.
     */
    public function sendNotification(string $deviceToken, string $title, string $body, array $data = [])
    {
        try {
            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            return $this->messaging->send($message);
        } catch (\Throwable $e) {
            Log::error('FCM Error (Single): ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to multiple device tokens.
     */
    public function sendMulticastNotification(array $deviceTokens, string $title, string $body, array $data = [])
    {
        try {
            // Convert device tokens to valid Firebase token objects
            $registrationTokens = array_map(
                fn($token) => RegistrationToken::fromValue($token),
                $deviceTokens
            );

            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $report = $this->messaging->sendMulticast($message, $registrationTokens);

            Log::info("FCM Multicast Sent: {$report->successes()->count()} success, {$report->failures()->count()} failed.");

            return $report;
        } catch (\Throwable $e) {
            Log::error('FCM Error (Multicast): ' . $e->getMessage());
            return false;
        }
    }
}
