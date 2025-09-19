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
use Illuminate\Support\Str;
class CreateExamJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 3;
    /**
     * Create a new job instance.
     */
    protected $semesterDetails;
    protected $currentSchool;
    public function __construct($semesterDetails, $currentSchool)
    {
        $this->semesterDetails = $semesterDetails;
        $this->currentSchool = $currentSchool;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
       $this->createExam($this->semesterDetails, $this->currentSchool);
    }

    public function createExam($semesterDetails, $currentSchool)
    {
        $specialty = Specialty::where("school_branch_id", $currentSchool->id)
            ->find($semesterDetails['specialty_id']);
        $semester = Semester::find($semesterDetails['semester_id']);
        if ($semester->count == 1) {
            $examTypes = Examtype::where("semester", "first")
                ->where("type", '!=', 'resit')
                ->get();
            foreach ($examTypes as $examType) {
                $examId = Str::uuid();
                DB::table('exams')->insert([
                    'id' => $examId,
                    'school_branch_id' => $currentSchool->id,
                    'exam_type_id' => $examType->id,
                    'level_id' => $specialty->level_id,
                    'semester_id' => $semesterDetails['semester_id'],
                    'school_year' => $semesterDetails['school_year'],
                    'specialty_id' => $specialty->id,
                    'student_batch_id' => $semesterDetails['student_batch_id'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

            }
        }
        if ($semester->count == 2) {
            $examTypes = Examtype::where("semester", "second")
                ->where("type", '!=', 'resit')
                ->get();
            foreach ($examTypes as $examType) {
                $examId = Str::uuid();
                DB::table('exams')->insert([
                    'id' => $examId,
                    'school_branch_id' => $currentSchool->id,
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
            }
        }
    }

    public function createExamCandidate($semesterDetails, $specialty, $examId){
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
