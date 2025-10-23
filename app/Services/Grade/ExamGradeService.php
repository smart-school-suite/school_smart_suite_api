<?php

namespace App\Services\Grade;

use App\Exceptions\AppException;
use App\Models\Grades;
use App\Models\Examtype;
use App\Models\LetterGrade;
use App\Models\Exams;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ExamGradeService
{
    public function getExamGrades($currentSchool)
    {
        $gradesData = Grades::where('school_branch_id', $currentSchool->id)
            ->with(['exam.examtype.semesters', 'lettergrade'])
            ->get();

        if ($gradesData->isEmpty()) {
            throw new AppException(
                "There are no exam grades available for this school branch yet.",
                404,
                "No Grades Found",
                "We could not find any grades associated with your school branch. Please ensure grades have been assigned to exams.",
                null
            );
        }

        return $gradesData;
    }
    public function deleteExamGrading($currentSchool, $examId)
    {
        try {
            $exam = Exams::where('school_branch_id', $currentSchool->id)->findOrFail($examId);

            $exam->grades_category_id = null;
            $exam->grading_added = false;
            $exam->save();

            return $exam;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "The exam you are trying to modify was not found.",
                404,
                "Exam Not Found",
                "We could not find the exam with the provided ID for this school. Please verify the ID and try again.",
                null
            );
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while removing the exam grading.",
                500,
                "Grading Deletion Error",
                "A server-side issue prevented the grading from being removed. Please try again.",
                null
            );
        }
    }
    public function bulkDeleteExamGrading($examIds, $currentSchool)
    {
        $results = [];
        try {
            DB::beginTransaction();
            foreach ($examIds as $examId) {
                $exam = Exams::where('school_branch_id', $currentSchool->id)->findOrFail($examId);
                $exam->grades_category_id = null;
                $exam->grading_added = false;
                $exam->save();
                $results[] = [
                    'exam' => $exam
                ];
            }
            DB::commit();
            return $results;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new AppException(
                "One or more exams were not found.",
                404,
                "Exams Not Found",
                "We couldn't find one or more exams with the provided IDs. They may have already been un-graded.",
                null
            );
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "An unexpected error occurred while removing exam grading in bulk.",
                500,
                "Bulk Grading Removal Error",
                "A server issue prevented the grading from being removed for all selected exams.",
                null
            );
        }
    }
    public function updateExamGrading($currentSchool, $examId, $updateData)
    {
        try {
            $exams = Exams::where('school_branch_id', $currentSchool->id)->findOrFail($examId);
            $exams->grades_category_id = $updateData['grades_category_id'];
            $exams->grading_added = true;
            $exams->save();
            return $exams;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "The exam you are trying to update was not found.",
                404,
                "Exam Not Found",
                "We couldn't find the exam with the provided ID for this school.",
                null
            );
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while updating the exam grading.",
                500,
                "Grading Update Error",
                "A server issue prevented the grading from being updated.",
                null
            );
        }
    }
    public function getExamGradesConfiguration($currentSchool, string $examId)
    {
        try {
            $exam = Exams::where('school_branch_id', $currentSchool->id)->findOrFail($examId);

            $grades = Grades::where("school_branch_id", $currentSchool->id)
                ->where("grades_category_id", $exam->grades_category_id)
                ->with(['lettergrade'])
                ->get();

            if ($grades->isEmpty()) {
                throw new AppException(
                    "No grades have been configured for this exam.",
                    404,
                    "Grades Configuration Missing",
                    "The selected exam does not have any grades assigned to it yet. Please add a grading configuration first.",
                    null
                );
            }

            return $grades;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "The exam you are looking for was not found.",
                404,
                "Exam Not Found",
                "We could not find the exam with the provided ID for this school.",
                null
            );
        }
    }
    public function getExamConfigData($currentSchool, string $examId)
    {
        $exam = Exams::where('school_branch_id', $currentSchool->id)
            ->with(['specialty', 'level', 'examType', 'semester'])
            ->find($examId);

        if (!$exam) {
            throw new AppException(
                "The exam you are looking for was not found.",
                404,
                "Exam Not Found",
                "We could not find an exam with the provided ID for this school. Please verify the ID and try again.",
                null
            );
        }

        $examType = $exam->examType;

        if (!$examType || $examType->type == 'exam') {
            $semester = $examType->semester;
            $caExamType = Examtype::where('semester', $semester)
                ->where('type', 'ca')
                ->first();

            if (!$caExamType) {
                throw new AppException(
                    "The corresponding continuous assessment (CA) exam type was not found.",
                    404,
                    "CA Exam Type Not Found",
                    "The system could not locate a continuous assessment type for this exam's semester. Please ensure it is configured.",
                    null
                );
            }

            $additionalExams = Exams::where('exam_type_id', $caExamType->id)
                ->where('specialty_id', $exam->specialty_id)
                ->where('semester_id', $exam->semester_id)
                ->where("level_id", $exam->level_id)
                ->where("school_year", $exam->school_year)
                ->with(['examType', 'level', 'specialty', 'semester'])
                ->first();

            if (!$additionalExams) {
                throw new AppException(
                    "The corresponding continuous assessment (CA) exam for this final exam was not found.",
                    404,
                    "CA Exam Not Found",
                    "You are trying to configure a final exam, but no corresponding continuous assessment exam exists. Please create the continuous assessment exam first.",
                    null
                );
            }

            $letterGrades = LetterGrade::all();

            if ($letterGrades->isEmpty()) {
                throw new AppException(
                    "No letter grades have been configured for the system.",
                    500,
                    "Grades Configuration Missing",
                    "The system requires letter grades to be configured before you can proceed.",
                    null
                );
            }

            $examGradesData = [];
            foreach ($letterGrades as $letterGrade) {
                $examGradesData[] = [
                    'letter_grade_id' => $letterGrade->id,
                    'letter_grade' => $letterGrade->letter_grade,
                    'weighted_score' => ($exam->weighted_mark + $additionalExams->weighted_mark),
                    'level_id' => $exam->level_id,
                    'specialty_id' => $exam->specialty_id,
                    'exam_id' => $exam->id
                ];
            }
            return $examGradesData;
        } else {
            throw new AppException(
                "Grading can only be configured for final exams.",
                400,
                "Invalid Exam Type",
                "You can only apply grading to exams of type 'exam'. The selected exam is of type '{$examType->type}'.",
                null
            );
        }
    }
}
