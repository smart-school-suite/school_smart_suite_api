<?php

namespace App\Services;
use App\Models\Grades;
use App\Models\Exams;
use App\Models\Examtype;
use Illuminate\Support\Facades\DB;
use Exception;
class AddGradesService
{
    // Implement your logic here
    public function makeGradeForExam(array $grades, $currentSchool)
    {
        try {
            DB::beginTransaction();
            $createdGrades = [];
            $errors = [];

            foreach ($grades as $gradeData) {
                $this->validateExistingGrade($gradeData, $currentSchool->id, $errors);
                $this->validateMinimumMaximumScore($gradeData, $currentSchool->id, $errors);
                $this->saveGrade($gradeData, $currentSchool->id);
                $createdGrades[] = $gradeData;
            }
            DB::commit();
            return $createdGrades;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;

        }
    }
    protected function validateExistingGrade($gradeData, $currentSchoolId, &$errors)
    {
        if (Grades::where('school_branch_id', $currentSchoolId)
            ->where('exam_id', $gradeData['exam_id'])
            ->where('minimum_score', $gradeData['minimum_score'])
            ->where('maximum_score', $gradeData['maximum_score'])
            ->where('letter_grade_id', $gradeData['letter_grade_id'])
            ->exists()
        ) {
            $errors[] = [
                'exam_id' => $gradeData['exam_id'],
                'minimum_score' => $gradeData['minimum_score'],
                'maximum_score' => $gradeData['maximum_score'],
                'message' => 'Grade already exists',
            ];
        }
    }

    protected function validateMinimumMaximumScore($gradeData, $currentSchoolId, &$errors)
    {
        $exam = Exams::where('school_branch_id', $currentSchoolId)
            ->where('id', $gradeData['exam_id'])
            ->first();

        if (!$exam) {
            $errors[] = [
                'exam_id' => $gradeData['exam_id'],
                'message' => 'Exam not found',
            ];
        }

        $examType = $exam->examType;
        if (!$examType || $examType->type !== 'exam') {
            throw new Exception('Exam type is not of type exam or not found');
        }

        $semester = $examType->semester;
        $caExamType = Examtype::where('semester', $semester)
            ->where('type', 'ca')
            ->first();
        if (!$caExamType) {
            throw new Exception('Corresponding CA exam type not found');
        }

        $additionalExams = Exams::where('school_year', $exam->school_year)
            ->where('exam_type_id', $caExamType->id)
            ->where('specialty_id', $exam->specialty_id)
            ->where('level_id', $exam->level_id)
            ->where('semester_id', $exam->semester_id)
            ->where('department_id', $exam->department_id)
            ->get();
        $isExamWithCA = $exam->exam_type_id === $caExamType->id;
        if ($gradeData['minimum_score'] > $gradeData['maximum_score']) {
            $errors[] = [
                'exam_id' => $gradeData['exam_id'],
                'minimum_score' => $gradeData['minimum_score'],
                'maximum_score' => $gradeData['maximum_score'],
                'message' => 'Minimum score cannot be greater than maximum score',
            ];
        } elseif ($isExamWithCA) {
            if ($gradeData['minimum_score'] > $exam->weighted_mark + $additionalExams->first()->weighted_mark || $gradeData['maximum_score'] > $exam->weighted_mark + $additionalExams->first()->weighted_mark) {
                $errors[] = [
                    'exam_id' => $gradeData['exam_id'],
                    'minimum_score' => $gradeData['minimum_score'],
                    'maximum_score' => $gradeData['maximum_score'],
                    'message' => 'Scores cannot be greater than exam max score',
                    'exam_max_score' => $exam->weighted_mark,
                ];
            }
        } else {
            if ($gradeData['minimum_score'] > $exam->weighted_mark || $gradeData['maximum_score'] > $exam->weighted_mark) {
                $errors[] = [
                    'exam_id' => $gradeData['exam_id'],
                    'minimum_score' => $gradeData['minimum_score'],
                    'maximum_score' => $gradeData['maximum_score'],
                    'message' => 'Scores cannot be greater than exam max score',
                    'exam_max_score' => $exam->weighted_mark,
                ];
            }
        }
    }

    protected function saveGrade($gradeData, $currentSchoolId)
    {
        $grade = new Grades();
        $grade->school_branch_id = $currentSchoolId;
        $grade->letter_grade_id = $gradeData['letter_grade_id'];
        $grade->grade_points = $gradeData['grade_points'];
        $grade->exam_id = $gradeData['exam_id'];
        $grade->determinant = $gradeData['determinant'];
        $grade->grade_status = $gradeData['grade_status'];
        $grade->minimum_score = floatval($gradeData['minimum_score']);
        $grade->maximum_score = floatval($gradeData['maximum_score']);
        $grade->save();

        return $grade;
    }
}
