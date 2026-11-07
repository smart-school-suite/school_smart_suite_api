<?php

namespace App\Jobs\ExamJointCourse;

use App\Services\ExamJointCourse\ExamJointCourseSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/** Links a new exam to the joint exams of the courses its specialty shares. */
class CreateExamJointCourseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    public function __construct(
        protected string $examId,
        protected string $schoolBranchId,
    ) {}

    public function handle(ExamJointCourseSync $sync): void
    {
        $sync->forExam($this->schoolBranchId, $this->examId);
    }
}
