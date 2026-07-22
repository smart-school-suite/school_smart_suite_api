<?php

namespace App\Events\ExamTimetable;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExamTimetableGenerationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly mixed $actor,
        public readonly object $currentSchool,
        public readonly array $payload,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                "schoolBranch.{$this->currentSchool->id}" .
                    ".schoolAdmin.{$this->actor->id}" .
                    ".examTimetable"
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'examTimetable.generation';
    }

    public function broadcastWith(): array
    {
        return [
            'payload'   => $this->payload,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
