<?php

namespace App\Jobs\DataCreationJob;


use App\Models\AccessedStudent;
use App\Models\Exams;
use App\Models\Student;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateExamCandidateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $specialtId;
    protected $levelId;
    protected $studntBatchId;
    protected $examId;
    public function __construct($specialtId, $levelId, $studntBatchId, $examId)
    {
        //
        $this->specialtId = $specialtId;
        $this->levelId = $levelId;
        $this->studntBatchId = $studntBatchId;
        $this->examId = $examId;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $students = Student::where('specialty_id', $this->specialtId)
            ->where('level_id', $this->levelId)
            ->where('student_batch_id', $this->studntBatchId)
            ->get();
        $exam = Exams::findOrFail($this->examId);
        foreach ($students as $student) {
            AccessedStudent::create([
                'student_id' => $student->id,
                'exam_id' => $this->examId,
                'school_branch_id' => $student->school_branch_id,
                'level_id' => $student->level_id,
                'specialty_id' => $student->specialty_id,
                'student_batch_id' => $student->student_batch_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
       $exam->expected_candidate_number = $students->count();
       $exam->save();
    }
}
