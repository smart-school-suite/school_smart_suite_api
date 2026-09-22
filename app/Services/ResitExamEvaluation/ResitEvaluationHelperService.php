<?php

namespace App\Services\ResitExamEvaluation;

use App\Exceptions\AppException;
use App\Models\ResitCandidates;
use App\Models\Studentresit;
use App\Models\ResitExamRef;

class ResitEvaluationHelperService
{
    public function getResitEvaluationHelper(string $candidateId, object $currentSchool)
    {
        $candidate = ResitCandidates::where("school_branch_id", $currentSchool->id)
            ->with([
                'resitExam.examGradeScale.schoolGradeScale.grade',
                'resitExam.schoolYear.schoolSemester.semester',
                'resitExam.schoolYear.specialty',
                'student'
            ])
            ->find($candidateId);

        if (!$candidate) {
            throw new AppException(
                "The selected candidate could not be found.",
                404,
                "Candidate Not Found",
                "Please verify that the selected candidate exists or has not been removed.",
                "/candidates"
            );
        }

        $resitExam = $candidate->resitExam;

        if (!$resitExam) {
            throw new AppException(
                "The exam associated with this candidate could not be found.",
                404,
                "Exam Not Found",
                "Please verify that the candidate is assigned to a valid exam.",
                "/exams"
            );
        }

        if (!$resitExam->examGradeScale) {
            throw new AppException(
                'Exam Grade Scale Not Set',
                400,
                'Grade Scale Not Set',
                'The grade scale for this exam has not been set. Please set the grade scale in the exam settings to proceed.',
                "/exams/{$resitExam->id}/edit"
            );
        }

        if ($resitExam->examGradeScale->schoolGradeScale->isEmpty()) {
            throw new AppException(
                'Exam Grade Scale Not Configured',
                404,
                'Grade Scale Not Configured',
                'The grade scale has been set for this exam, but this grade scale has not been configured yet. Please ensure that grades have been set up for this scale.',
                "/grades-categories"
            );
        }


        $examIds = ResitExamRef::where(
            'resit_exam_id',
            $resitExam->id
        )
            ->pluck('exam_id');

        $resitableCourses = Studentresit::with('courses')
            ->where('school_branch_id', $currentSchool->id)
            ->where('student_id', $candidate->student->id)
            ->whereIn('exam_id', $examIds)
            ->get();

        return [
            'grade_scale' => $resitExam->examGradeScale->schoolGradeScale,
            'courses' => $resitableCourses->map(fn($c) => [
                "resit_id" => $c->id,
                "course_id" => $c->courses->id ?? null,
                "course_title" => $c->courses->course_title ?? null,
                "course_code" => $c->courses->course_code ?? null,
                "course_credit" => $c->courses->credit ?? null,
                "course_description" => $c->courses->description ?? null
            ]),
            'max_gpa' => 4.00,
            "resit_exam" => $candidate->resitExam->withoutRelations()
        ];
    }
    public function getResitUpdateEvaluationHelper(string $candidateId, object $currentSchool)
    {
        $candidate = ResitCandidates::where("school_branch_id", $currentSchool->id)
            ->with([
                'resitExam.examGradeScale.schoolGradeScale.grade',
                'resitExam.schoolYear.schoolSemester.semester',
                'resitScores.resit' => function ($query) {
                    $query->withTrashed();
                },
                'resitExam.schoolYear.specialty',
                'resitScores.resit.courses.types',
                'resitScores.grade.grade',
                'student'
            ])
            ->find($candidateId);

        if (!$candidate) {
            throw new AppException(
                "The selected candidate could not be found.",
                404,
                "Candidate Not Found",
                "Please verify that the selected candidate exists or has not been removed.",
                "/candidates"
            );
        }

        $resitExam = $candidate->resitExam;

        if (!$resitExam) {
            throw new AppException(
                "The exam associated with this candidate could not be found.",
                404,
                "Exam Not Found",
                "Please verify that the candidate is assigned to a valid exam.",
                "/exams"
            );
        }

        if (!$resitExam->examGradeScale) {
            throw new AppException(
                'Exam Grade Scale Not Set',
                400,
                'Grade Scale Not Set',
                'The grade scale for this exam has not been set. Please set the grade scale in the exam settings to proceed.',
                "/exams/{$resitExam->id}/edit"
            );
        }

        if ($resitExam->examGradeScale->schoolGradeScale->isEmpty()) {
            throw new AppException(
                'Exam Grade Scale Not Configured',
                404,
                'Grade Scale Not Configured',
                'The grade scale has been set for this exam, but this grade scale has not been configured yet. Please ensure that grades have been set up for this scale.',
                "/grades-categories"
            );
        }

        return [
            'grade_scale' => $resitExam->examGradeScale->schoolGradeScale,
            'courses' => $candidate->resitScores->map(fn($score) => [
                "id" => $score->id,
                "course_id" => $score->resit->courses->id ?? null,
                "course_title" => $score->resit->courses->course_title ?? null,
                "course_code" => $score->resit->courses->course_code ?? null,
                "course_credit" => $score->resit->courses->credit ?? null,
                "score" => (float) $score->score ?? 0,
                "grade_points" => (float) $score->grade->grade_points ?? 0,
                "performance" => $score->grade->performance ?? null,
                "result" => $score->grade->result ?? null,
                "resit_result" => $score->grade->resit_result ?? null,
                "grade" => $score->grade->grade->letter_grade ?? null
            ]),
            'max_gpa' => 4.00,
            "resit_exam" => $candidate->resitExam->withoutRelations()
        ];
    }
}
