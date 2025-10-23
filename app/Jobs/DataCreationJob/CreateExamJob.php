<?php

namespace App\Jobs\DataCreationJob;

use App\Models\Exams;
use App\Models\Examtype;
use App\Models\Semester;
use App\Models\Specialty;
use App\Models\Student;
use App\Models\AccessedStudent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateExamJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 3;
    /**
     * Create a new job instance.
     */
    protected $semesterDetails;
    protected $schoolBranchId;
    public function __construct($semesterDetails, $schoolBranchId)
    {
        $this->semesterDetails = $semesterDetails;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->createExam($this->semesterDetails, $this->schoolBranchId);
    }
    public function createExam($semesterDetails, $schoolBranchId)
    {
        $specialty = Specialty::where("school_branch_id", $schoolBranchId)
            ->find($semesterDetails['specialty_id']);
        $semester = Semester::find($semesterDetails['semester_id']);

        $semesterName = strtolower(trim($semester->name));

        if (Str::contains($semesterName, 'first') && !Str::contains($semesterName, 'second')) {
            $examTypes = Examtype::where("semester", "first")
                ->where("type", '!=', 'resit')
                ->get();
            foreach ($examTypes as $examType) {
                $exists = DB::table('exams')
                    ->where('exam_type_id', $examType->id)
                    ->where('semester_id', $semesterDetails['semester_id'])
                    ->where('specialty_id', $specialty->id)
                    ->where('school_year', $semesterDetails['school_year'])
                    ->exists();

                if (!$exists) {
                    $examId = Str::uuid();
                    DB::table('exams')->insert([
                        'id' => $examId,
                        'school_branch_id' => $schoolBranchId,
                        'exam_type_id' => $examType->id,
                        'level_id' => $specialty->level_id,
                        'semester_id' => $semesterDetails['semester_id'],
                        'school_year' => $semesterDetails['school_year'],
                        'specialty_id' => $specialty->id,
                        'student_batch_id' => $semesterDetails['student_batch_id'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $this->createExamCandidate($semesterDetails, $specialty, $examId);
                } else {
                    Log::warning("Exam already exists for exam_type_id: {$examType->id}, semester_id: {$semesterDetails['semester_id']}");
                }
            }
        } elseif (Str::contains($semesterName, 'second')) {
            $examTypes = Examtype::where("semester", "second")
                ->where("type", '!=', 'resit')
                ->get();
            foreach ($examTypes as $examType) {
                $exists = DB::table('exams')
                    ->where('exam_type_id', $examType->id)
                    ->where('semester_id', $semesterDetails['semester_id'])
                    ->where('specialty_id', $specialty->id)
                    ->where('school_year', $semesterDetails['school_year'])
                    ->exists();

                if (!$exists) {
                    $examId = Str::uuid();
                    DB::table('exams')->insert([
                        'id' => $examId,
                        'school_branch_id' => $schoolBranchId,
                        'exam_type_id' => $examType->id,
                        'level_id' => $specialty->level_id,
                        'semester_id' => $semesterDetails['semester_id'],
                        'school_year' => $semesterDetails['school_year'],
                        'specialty_id' => $specialty->id,
                        'student_batch_id' => $semesterDetails['student_batch_id'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $this->createExamCandidate($semesterDetails, $specialty, $examId);
                } else {
                    Log::warning("Exam already exists for exam_type_id: {$examType->id}, semester_id: {$semesterDetails['semester_id']}");
                }
            }
        } else {
            Log::error("Unexpected semester name: {$semester->name}");
        }
    }
    public function createExamCandidate($semesterDetails, $specialty, $examId)
    {
        $students = Student::where('specialty_id', $specialty->id)
            ->where('level_id', $specialty->level_id)
            ->where('student_batch_id', $semesterDetails['student_batch_id'])
            ->get();
        $exam = Exams::find($examId);
        foreach ($students as $student) {
            AccessedStudent::create([
                'student_id' => $student->id,
                'exam_id' => $exam->id,
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
