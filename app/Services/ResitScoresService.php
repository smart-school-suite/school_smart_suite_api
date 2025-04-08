<?php

namespace App\Services;

use App\Models\AccessedResitStudent;
use App\Models\Marks;
use App\Models\Studentresit;
use Illuminate\Support\Facades\DB;
use App\Models\Exams;
use App\Models\Grades;
use App\Models\Examtype;
use App\Models\ResitMarks;
use Exception;

class ResitScoresService
{
    public function submitStudentResitScores(array $entries, $currentSchool, $candidateId)
    {
        DB::beginTransaction();

        try {
            $results = [];

            foreach ($entries as $entry) {
                $failedMark = Marks::where('school_branch_id', $currentSchool->id)
                    ->where('course_id', $entry['course_id'])
                    ->where('student_id', $entry['student_id'])
                    ->where('grade_status', 'fail')
                    ->where('specialty_id', $entry['specialty_id'])
                    ->firstOrFail();

                $exam = Exams::findOrFail($failedMark->exam_id);
                $caExam = $this->findExamsBasedOnCriteria($exam->id);
                $caMark = $this->retrieveCaScore($currentSchool->id, $entry['student_id'], $entry['course_id'], $caExam->id);

                $resitGradeInfo = $this->determineResitLetterGrade($entry['score'], $currentSchool->id, $entry['exam_id']);
                $newExamScoreData = $this->determineNewExamScores($currentSchool, $resitGradeInfo['letterGrade'], $exam);
                $newCaScoreData = $this->determineNewCaScore($currentSchool, $resitGradeInfo, $caExam);

                $this->updateMarkScore($caMark, $newCaScoreData);
                $this->updateMarkScore($failedMark, $newExamScoreData);

                $resitStatus = $this->updateStudentResitStatus($entry['student_id'], $entry['course_id'], $entry['specialty_id'], $currentSchool, $resitGradeInfo);

                $results[] = [
                    'failed_ca_score' => $caMark,
                    'new_ca_score' => $newCaScoreData,
                    'resit_grade' => $resitGradeInfo,
                    'failed_exam_score' => $failedMark,
                    'new_exam_score' => $newExamScoreData,
                    'resit_status_update' => $resitStatus,
                ];
            }

            $this->updateAccessmentStatus($candidateId);
            DB::commit();
            return $results;
        } catch (Exception  $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function storeResitScore($examid, $entry){
        ResitMarks::create([
            'school_branch_id' => $entry['school_branch_id'],
            'exam_id' => $examid,
            'student_id' => $entry['student_id'],
            'course_id' => $entry['course_id'],
            'specialty_id' => $entry['specialty_id'],
            'score' => $entry['score'],
            'grade_points' => $entry['grade_points'] ?? null,
            'grade_status' => $entry['grade_status'],
            'determinant' => $entry['determinant'] ?? null,
        ]);

    }
    private function updateAccessmentStatus($candidateId){
        $candidate = AccessedResitStudent::findOrFail($candidateId);
        $candidate->status = 'completed';
        $candidate->grades_submitted = true;
        $candidate->student_accessed = 'student_accessed';
        $candidate->save();
        return $candidate;
    }
    private function updateStudentResitStatus($student_id, $course_id, $specialty_id, $currentSchool, $resitLetterGrade)
    {
        $resitCourses = Studentresit::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student_id)
            ->where("course_id", $course_id)
            ->where("specialty_id", $specialty_id)
            ->first();
        if ($resitLetterGrade['grade_status'] === "fail") {

            $resitCourses->exam_status = "failed";
            $resitCourses->attempt_number = $resitCourses->attempt_number + 1;
            $resitCourses->iscarry_over = true;
            $resitCourses->paid_status = 'unpaid';
            $resitCourses->save();
            return $resitCourses;
        }

        $resitCourses->exam_status = 'passed';
        $resitCourses->save();
        return $resitCourses;
    }
    private function updateMarkScore(Marks $mark, array $scoreData)
    {
        $mark->update([
            'score' => $scoreData['new_score'],
            'grade_points' => $scoreData['grade_points'] ?? $scoreData['new_grade_point'] ?? null,
            'grade_status' => $scoreData['grade_status'],
            'determinant' => $scoreData['determinant'] ?? null,
        ]);

        return $mark;
    }

    private function determineNewExamScores($currentSchool, $resitLetterGrade, $failedExam)
    {
        $examGrades = Grades::whereHas('lettergrade', function ($query) use ($resitLetterGrade) {
            $query->where('letter_grade', $resitLetterGrade);
        })
            ->where("school_branch_id", $currentSchool->id)
            ->where("grades_category_id", $failedExam->grades_category_id)
            ->first();
        if (!$examGrades) {
            throw new Exception("No grades found for school ID: {$examGrades->school_branch_id} and exam ID: {$examGrades->exam_id}");
        }
        $newExamScore = mt_rand($examGrades->minimum_score, $examGrades->maximum_score);
        return [
            'new_score' => $newExamScore,
            'new_grade_points' => $examGrades->grade_points,
            'grade_status' => $examGrades->grade_status
        ];
    }
    private function determineNewCaScore($currentSchool, $resitLetterGrade, $caExam)
    {
        $caGrades = Grades::whereHas('lettergrade', function ($query) use ($resitLetterGrade) {
            $query->where('letter_grade', $resitLetterGrade['letterGrade']);
        })
            ->where('school_branch_id', $currentSchool->id)
            ->where('grades_category_id', $caExam->grades_category_id)
            ->first();

        if (!$caGrades) {
            throw new Exception("No grades found for school ID: {$caGrades->school_branch_id} and exam ID: {$caGrades->exam_id}");
        }

        $newCaScore = mt_rand($caGrades->minimum_score, $caGrades->maximum_score);
        return [
            'new_score' => $newCaScore,
            'new_grade_point' => $caGrades->grade_points,
            'grade_status' => $caGrades->grade_status
        ];
    }
    private function determineResitLetterGrade($score, $schoolId, $examId)
    {
        $exam = Exams::findOrFail($examId);
        $grades = Grades::with('lettergrade')
            ->where('school_branch_id', $schoolId)
            ->where('grades_category_id', $exam->grades_category_id)
            ->orderBy('minimum_score', 'asc')
            ->get();
        if ($grades->isEmpty()) {
            throw new Exception("No grades found for school ID: {$schoolId} and exam ID: {$examId}");
        }

        foreach ($grades as $grade) {
            if ($score >= $grade->minimum_score && $score <= $grade->maximum_score) {
                return [
                    'letterGrade' => $grade->lettergrade->letter_grade ?? 'N/A',
                    'gradeStatus' => $grade->grade_status,
                    'gratification' => $grade->determinant,
                    'score' => $score
                ];
            }
        }

        return ['letterGrade' => 'F', 'gradeStatus' => 'fail', 'gratification' => 'poor', 'score' => $score];
    }

    private function findExamsBasedOnCriteria(string $examId)
    {
        $exam = Exams::with('examType')->findOrFail($examId);
        if ($exam->examType->type !== 'exam') {
            throw new Exception('Exam type is not valid or not found');
        }

        $caExamType = ExamType::where('semester', $exam->examType->semester)
            ->where('type', 'ca')
            ->first();

        if (!$caExamType) {
            throw new Exception('Corresponding CA exam type not found');
        }

        $additionalExam = Exams::where('school_year', $exam->school_year)
            ->where('exam_type_id', $caExamType->id)
            ->where('specialty_id', $exam->specialty_id)
            ->where('level_id', $exam->level_id)
            ->where('semester_id', $exam->semester_id)
            ->where('department_id', $exam->department_id)
            ->first();

        if (!$additionalExam) {
            throw new Exception('No additional exam found');
        }

        return $additionalExam;
    }

    private function retrieveCaScore($schoolId, $studentId, $courseId, $examId)
    {
        $caScore = Marks::where('school_branch_id', $schoolId)
            ->where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->first();

        if (!$caScore) {
            throw new Exception('CA mark not found for this course', 400);
        }

        return $caScore;
    }
}
