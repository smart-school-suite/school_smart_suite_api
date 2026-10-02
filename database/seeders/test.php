<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Examtype;
use App\Models\Exam\Exam;
use App\Models\TuitionFees;
use App\Models\AcademicYear\SchoolAcademicYear;
use App\Jobs\Resit\CreateResitExamJob;
use App\Models\Announcement;
use App\Models\AnnouncementAuthor;
use App\Models\FeeSchedule;
use App\Models\RegistrationFee;
use App\Models\Schooladmin;
use App\Models\Student;

class test extends Seeder
{
    private function isExamFullyEvaluated(string $examId): bool
    {
        $exam = Exam::with('examCandidate.examScores')->findOrFail($examId);

        return $exam->examCandidate->every(
            fn($candidate) => $candidate->examScores->isNotEmpty()
        );
    }

    public function run(): void
    {
        $schoolAdmin = Schooladmin::where("email", "chongongprecious@gmail.com")->first();
        $announcements = Announcement::all();
        foreach ($announcements as $a) {
            AnnouncementAuthor::create([
                'school_branch_id' => $a->school_branch_id,
                'authorable_id' => $schoolAdmin->id,
                'authorable_type' => Schooladmin::class,
                'announcement_id' => $a->id,
            ]);
        }
        // $registrationFees = RegistrationFee::with(['student'])->get();
        // foreach ($registrationFees as $registrationFee) {
        //     $registrationFee->update([
        //         'specialty_id' => $registrationFee->student->specialty_id
        //     ]);
        // }
        // $examId = "a2b2a30e-340f-4347-b044-0c5d956b14cc";
        // $schoolId = "50207b5e-65fb-46ca-b507-963931071777";
        // $isEvaluated = $this->isExamFullyEvaluated($examId);
        // CreateResitExamJob::dispatch($examId, $schoolId);
        // $statusText = $isEvaluated ? 'TRUE' : 'FALSE';
        // $this->command->info("Is Exam Fully Evaluated: {$statusText}");
        // $exams = [
        //     [
        //         'exam_name' => 'First Semester CA (Continuous Assessment)',
        //         'description' => 'Evaluates ongoing academic performance (tests, assignments, attendance) during the first half of the session. Typically worth 30–40% of the total semester grade.'
        //     ],
        //     [
        //         'exam_name' => 'First Semester Exam',
        //         'description' => 'The major end-of-term assessment covering all first-semester course topics. Typically accounts for 60–70% of the final semester grade.'
        //     ],
        //     [
        //         'exam_name' => 'First Semester Resit',
        //         'description' => 'A supplementary assessment for students seeking to clear failed courses or improve low grades from the first semester.'
        //     ],
        //     [
        //         'exam_name' => 'Second Semester CA (Continuous Assessment)',
        //         'description' => 'Tracks continuous coursework and practical assessments throughout the second term. Contributes 30–40% toward the final semester evaluation.'
        //     ],
        //     [
        //         'exam_name' => 'Second Semester Exam',
        //         'description' => 'The final cumulative exam of the academic session covering second-semester material. Carries 60–70% of the semester grade to determine session completion.'
        //     ],
        //     [
        //         'exam_name' => 'Second Semester Resit',
        //         'description' => 'A second-chance assessment allowing students to remediate failed second-semester courses before the next academic year.'
        //     ]
        // ];

        // foreach ($exams as $examData) {
        //     $exam = Examtype::where("exam_name", $examData['exam_name'])->first();

        //     if ($exam) {
        //         $exam->update([
        //             'description' => $examData['description']
        //         ]);
        //     }
        // }
    }
}
