<?php

namespace App\Services\ExamEvaluation;

use App\Models\Course\CourseSpecialty;
use App\Models\Exam\Exam;
use App\Models\Examtype;
use App\Exceptions\AppException;
use App\Models\Exam\ExamCandidate;
use App\Models\Exam\ExamScore;

class ExamEvalutionHelperService
{
    public function getExamUpdateHelperData(object $currentSchool, string $candidateId)
    {
        $candidate = ExamCandidate::where("school_branch_id", $currentSchool->id)
            ->with([
                'exam.examGradeScale.schoolGradeScale.grade',
                'exam.schoolYear.schoolSemester.semester',
                'exam.schoolYear.specialty',
                'examScores.course',
                'examScores.grade.grade'
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

        $exam = $candidate->exam;

        if (!$exam) {
            throw new AppException(
                "The exam associated with this candidate could not be found.",
                404,
                "Exam Not Found",
                "Please verify that the candidate is assigned to a valid exam.",
                "/exams"
            );
        }

        if (!$exam->examGradeScale) {
            throw new AppException(
                'Exam Grade Scale Not Set',
                400,
                'Grade Scale Not Set',
                'The grade scale for this exam has not been set. Please set the grade scale in the exam settings to proceed.',
                "/exams/{$exam->id}/edit"
            );
        }

        if ($exam->examGradeScale->schoolGradeScale->isEmpty()) {
            throw new AppException(
                'Exam Grade Scale Not Configured',
                404,
                'Grade Scale Not Configured',
                'The grade scale has been set for this exam, but this grade scale has not been configured yet. Please ensure that grades have been set up for this scale.',
                "/grades-categories"
            );
        }

        $relatedCA = $this->getRelatedCa($exam, $currentSchool->id);

        $caScores = ExamScore::where("school_branch_id", $currentSchool->id)
            ->where('exam_id', $relatedCA->id)
            ->whereHas('candidate.student', function ($query) use ($candidate, $currentSchool) {
                $query->where("school_branch_id", $currentSchool->id)
                    ->where("id", $candidate->student_id);
            })
            ->with(['course'])
            ->get();

        if ($caScores->isEmpty()) {
            throw new AppException(
                'No Continuous Assessment marks found for this student.',
                404,
                'Continuous Assessment Marks Missing',
                'There are no Continuous Assessment marks recorded for the selected student in this exam cycle. Please ensure the student has been evaluated.',
                '/marks'
            );
        }

        return [
            'grade_scale' => $exam->examGradeScale->schoolGradeScale,
            'scores' => $candidate->examScores->map(fn($score) => [
                "id" => $score->id,
                "course_id" => $score->course->id ?? null,
                "course_title" => $score->course->course_title ?? null,
                "course_code" => $score->course->course_code ?? null,
                "course_credit" => $score->course->credit ?? null,
                "score" => (float) $score->score - $caScores->where("course_id", $score->course->id)->first()->score ?? 0,
                "grade_points" => (float) $score->grade->grade_points ?? 0,
                "performance" => $score->grade->performance ?? null,
                "result" => $score->grade->result ?? null,
                "resit_result" => $score->grade->resit_result ?? null,
                "grade" => $score->grade->grade->letter_grade ?? null,
                "ca_score" => (float) $caScores->where("course_id", $score->course->id)->first()->score ?? 0
            ]),
            'max_gpa' => $currentSchool->max_gpa ?? 4.00,
            "exam" => $candidate->exam->withoutRelations(),
            "ca_exam" => $relatedCA
        ];
    }
    public function getCaExamUpdateHelperData(object $currentSchool, string $candidateId)
    {
        $candidate = ExamCandidate::where("school_branch_id", $currentSchool->id)
            ->with([
                'exam.examGradeScale.schoolGradeScale.grade',
                'exam.schoolYear.schoolSemester.semester',
                'exam.schoolYear.specialty',
                'examScores.course',
                'examScores.grade.grade'
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

        $exam = $candidate->exam;

        if (!$exam) {
            throw new AppException(
                "The exam associated with this candidate could not be found.",
                404,
                "Exam Not Found",
                "Please verify that the candidate is assigned to a valid exam.",
                "/exams"
            );
        }

        if (!$exam->examGradeScale) {
            throw new AppException(
                'Exam Grade Scale Not Set',
                400,
                'Grade Scale Not Set',
                'The grade scale for this exam has not been set. Please set the grade scale in the exam settings to proceed.',
                "/exams/{$exam->id}/edit"
            );
        }

        if ($exam->examGradeScale->schoolGradeScale->isEmpty()) {
            throw new AppException(
                'Exam Grade Scale Not Configured',
                404,
                'Grade Scale Not Configured',
                'The grade scale has been set for this exam, but this grade scale has not been configured yet. Please ensure that grades have been set up for this scale.',
                "/grades-categories"
            );
        }

        $schoolSemester = $exam->schoolYear->schoolSemester->first();

        if (!$schoolSemester || !$schoolSemester->semester) {
            throw new AppException(
                'Semester Not Set',
                400,
                'Semester Not Set',
                'The semester for this school year has not been set. Please set the semester to proceed.',
                "/school-years/{$exam->schoolYear->id}/edit"
            );
        }

        return [
            'grade_scale' => $exam->examGradeScale->schoolGradeScale,
            'courses' => $candidate->examScores->map(fn($score) => [
                "id" => $score->id,
                "course_id" => $score->course->id ?? null,
                "course_title" => $score->course->course_title ?? null,
                "course_code" => $score->course->course_code ?? null,
                "course_credit" => $score->course->credit ?? null,
                "score" => (float) $score->score ?? 0,
                "grade_points" => (float) $score->grade->grade_points ?? 0,
                "performance" => $score->grade->performance ?? null,
                "result" => $score->grade->result ?? null,
                "resit_result" => $score->grade->resit_result ?? null,
                "grade" => $score->grade->grade->letter_grade ?? null
            ]),
            'max_gpa' => 4.00,
            "exam" => $candidate->exam->withoutRelations()
        ];
    }
    public function getCaExamEvaluationHelperData(object $currentSchool, string $candidateId)
    {
        $candidate = ExamCandidate::where("school_branch_id", $currentSchool->id)
            ->with([
                'exam.examGradeScale.schoolGradeScale.grade',
                'exam.schoolYear.schoolSemester.semester',
                'exam.schoolYear.specialty'
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

        $exam = $candidate->exam;

        if (!$exam) {
            throw new AppException(
                "The exam associated with this candidate could not be found.",
                404,
                "Exam Not Found",
                "Please verify that the candidate is assigned to a valid exam.",
                "/exams"
            );
        }

        if (!$exam->examGradeScale) {
            throw new AppException(
                'Exam Grade Scale Not Set',
                400,
                'Grade Scale Not Set',
                'The grade scale for this exam has not been set. Please set the grade scale in the exam settings to proceed.',
                "/exams/{$exam->id}/edit"
            );
        }

        if ($exam->examGradeScale->schoolGradeScale->isEmpty()) {
            throw new AppException(
                'Exam Grade Scale Not Configured',
                404,
                'Grade Scale Not Configured',
                'The grade scale has been set for this exam, but this grade scale has not been configured yet. Please ensure that grades have been set up for this scale.',
                "/grades-categories"
            );
        }

        $schoolSemester = $exam->schoolYear->schoolSemester->first();

        if (!$schoolSemester || !$schoolSemester->semester) {
            throw new AppException(
                'Semester Not Set',
                400,
                'Semester Not Set',
                'The semester for this school year has not been set. Please set the semester to proceed.',
                "/school-years/{$exam->schoolYear->id}/edit"
            );
        }

        $semesterId = $schoolSemester->semester->id;

        $courses = CourseSpecialty::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $exam->schoolYear->specialty->id)
            ->whereHas('course', function ($query) use ($semesterId) {
                $query->where("semester_id", $semesterId);
            })
            ->with(['course' => function ($query) use ($semesterId) {
                $query->where("semester_id", $semesterId);
            }])
            ->get()
            ->pluck('course')
            ->filter()
            ->unique('id')
            ->values();

        return [
            'grade_scale' => $exam->examGradeScale->schoolGradeScale,
            'courses' => $courses,
            'max_gpa' => 4.00,
            "exam" => $candidate->exam->withoutRelations()
        ];
    }
    public function getExamEvaluationHelperData(object $currentSchool, string $candidateId)
    {
        $candidate = ExamCandidate::where("school_branch_id", $currentSchool->id)
            ->with([
                'exam.examGradeScale.schoolGradeScale.grade',
                'exam.schoolYear.schoolSemester.semester',
                'exam.schoolYear.specialty'
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

        $exam = $candidate->exam;

        if (!$exam) {
            throw new AppException(
                "The exam associated with this candidate could not be found.",
                404,
                "Exam Not Found",
                "Please verify that the candidate is assigned to a valid exam.",
                "/exams"
            );
        }

        if (!$exam->examGradeScale) {
            throw new AppException(
                'Exam Grade Scale Not Set',
                400,
                'Grade Scale Not Set',
                'The grade scale for this exam has not been set. Please set the grade scale in the exam settings to proceed.',
                "/exams/{$exam->id}/edit"
            );
        }

        if ($exam->examGradeScale->schoolGradeScale->isEmpty()) {
            throw new AppException(
                'Exam Grade Scale Not Configured',
                404,
                'Grade Scale Not Configured',
                'The grade scale has been set for this exam, but this grade scale has not been configured yet. Please ensure that grades have been set up for this scale.',
                "/grades-categories"
            );
        }

        $relatedCA = $this->getRelatedCa($exam, $currentSchool->id);

        $caScores = ExamScore::where("school_branch_id", $currentSchool->id)
            ->where('exam_id', $relatedCA->id)
            ->whereHas('candidate.student', function ($query) use ($candidate, $currentSchool) {
                $query->where("school_branch_id", $currentSchool->id)
                    ->where("id", $candidate->student_id);
            })
            ->with(['course'])
            ->get();

        if ($caScores->isEmpty()) {
            throw new AppException(
                'No Continuous Assessment marks found for this student.',
                404,
                'Continuous Assessment Marks Missing',
                'There are no Continuous Assessment marks recorded for the selected student in this exam cycle. Please ensure the student has been evaluated.',
                '/marks'
            );
        }

        return [
            'grade_scale' => $exam->examGradeScale->schoolGradeScale,
            'ca_scores' => $caScores,
            'max_gpa' => $currentSchool->max_gpa ?? 4.00,
            "exam" => $candidate->exam->withoutRelations(),
            "ca_exam" => $relatedCA
        ];
    }
    private function getRelatedCa(Exam $exam, string $schoolBranchId)
    {
        if ($exam->examType->type !== 'exam') {
            throw new AppException(
                'Invalid Exam Type',
                400,
                'Exam Type Error',
                'The selected evaluation is not a final exam. Please select a valid exam to proceed.',
                '/exams'
            );
        }

        $caExamType = Examtype::where('semester_id', $exam->examType->semester_id)
            ->where('type', 'ca')
            ->first();

        if (!$caExamType) {
            throw new AppException(
                'No Continuous Assessment Type Found',
                404,
                'Continuous Assessment Not Configured',
                'No Continuous Assessment type is set up for this semester. Please configure it in the settings before proceeding.',
                '/exam-types'
            );
        }

        $relatedCaExam = Exam::where('school_branch_id', $schoolBranchId)
            ->where('exam_type_id', $caExamType->id)
            ->where('school_year_id', $exam->school_year_id)
            ->first();

        if (!$relatedCaExam) {
            throw new AppException(
                'No Continuous Assessment Found',
                404,
                'Continuous Assessment Missing',
                'No corresponding Continuous Assessment exam exists for this academic year and semester. Please create one to proceed.',
                '/exams'
            );
        }

        return $relatedCaExam;
    }
}
