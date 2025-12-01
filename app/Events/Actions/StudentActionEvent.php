<?php

namespace App\Events\Actions;

use App\Models\Student;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class StudentActionEvent implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected string $schoolBranchId;
    protected string $feature;
    protected ?array $specialtyIds = null;
    protected ?array $studentIds = null;
    protected string $targetBy;
    protected $data = [];
    protected $message;
    protected $authUser = null;

    public function __construct(array $options)
    {
        $this->schoolBranchId = $options['schoolBranch']
            ?? $options['school_branch_id']
            ?? throw new \InvalidArgumentException('schoolBranch is required');

        $this->feature  = $options['feature'] ?? 'general';
        $this->message  = $options['message'] ?? 'An action occurred';
        $this->data     = $options['data'] ?? [];
        $this->authUser = $options['authUser'] ?? null;

        $this->specialtyIds = $options['specialtyIds'] ?? $options['specialties'] ?? null;
        $this->studentIds   = $options['studentIds'] ?? $options['students'] ?? null;

        $this->targetBy = $options['targetBy'] ?? $this->determineTargetMode();
    }

    protected function determineTargetMode(): string
    {
        if ($this->studentIds && !$this->specialtyIds) {
            return 'student';
        }
        if ($this->specialtyIds && !$this->studentIds) {
            return 'specialty';
        }
        if ($this->studentIds && $this->specialtyIds) {
            return 'both';
        }

        throw new \InvalidArgumentException('Must provide either specialtyIds, studentIds, or both');
    }

    public function broadcastOn(): array
    {
        $query = Student::where('school_branch_id', $this->schoolBranchId);

        if ($this->authUser && $this->authUser instanceof Student) {
            $query->where('id', '!=', $this->authUser->id);
        }

        match ($this->targetBy) {
            'student' => $query->whereIn('id', $this->studentIds),
            'specialty' => $query->whereIn('specialty_id', $this->specialtyIds),
            'both' => $query->whereIn('id', $this->studentIds)
                           ->orWhereIn('specialty_id', $this->specialtyIds),
            default => throw new \LogicException('Invalid target mode'),
        };

        $students = $query->get(['id']);

        return $students->map(fn($student) => new PrivateChannel(
            "schoolBranch.{$this->schoolBranchId}.student.{$student->id}.actions"
        ))->all();
    }

    public function broadcastWith(): array
    {
        return [
            'feature'          => $this->feature,
            'message'          => $this->message,
            'data'             => $this->data,
            'school_branch_id' => $this->schoolBranchId,
            'timestamp'        => now()->toDateTimeString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'student.action';
    }
}
