<?php

namespace App\Events\SemesterTimetable;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimetableGenerationEvent implements ShouldBroadcast
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
                ".semesterTimetable"
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'semesterTimetable.generation';
    }

    public function broadcastWith(): array
    {
        return [
            'payload'   => $this->payload,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
