<?php

namespace App\Services;

use App\Models\Exams;
use App\Models\Marks;
use App\Models\Grades;
use App\Models\Student;
use App\Models\Studentresit;
use App\Models\Examtype;
use App\Models\Resitablecourses;
use Exception;
use Illuminate\Support\Facades\DB;

class AddScoreService
{
    // Implement your logic here
    public function addStudentScores(array $studentScores, $currentSchool)
    {
        $results = [];
        DB::beginTransaction();
        try {
            foreach ($studentScores as $scoreData) {
                $student = $this->getStudent($currentSchool->id, $scoreData['student_id']);
                $exam = Exams::find($scoreData['exam_id']);

                if (!$student || !$exam) {
                    throw new Exception('Student or Exam not found', 404);
                }

                if ($this->isDuplicateEntry($currentSchool->id, $scoreData)) {
                    throw new Exception('Duplicate data entry for this student', 409);
                }

                $determinedExam = $this->determineExamType($scoreData['exam_id']);
                if (!$determinedExam) {
                    throw new Exception('Exam type not found', 400);
                }

                $grade = null;
                $scoreData['score'] = $this->handleExamTypes($scoreData, $determinedExam, $exam, $student, $currentSchool->id);

                $marks = Marks::create($this->prepareMarksData($scoreData, $grade, $currentSchool->id, $student));
                $results[] = $marks;
            }
            DB::commit();
            return $results;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function handleExamTypes(array $scoreData, array $determinedExam, $exam, $student, $schoolId)
    {
        if ($determinedExam['ca']) {
            $this->validateCaMark($exam, $scoreData['score']);
            return $this->determineLetterGrade($scoreData['score'], $schoolId, $scoreData['exam_id']);
        } elseif ($determinedExam['exam']) {
            $totalScore = $this->calculateTotalScore($schoolId, $student, $scoreData, $exam);
            if ($totalScore === false) {
                throw new Exception('CA mark not found for this course', 400);
            }
            return $this->determineExamLetterGrade($totalScore, $schoolId, $student, $scoreData['exam_id'], $scoreData['courses_id']);
        } else {
            throw new Exception('Exam type not found', 400);
        }
    }

    private function prepareMarksData(array $scoreData, $grade, $schoolId, $student)
    {
        return array_merge($scoreData, [
            'grade' => $grade['letterGrade'] ?? null,
            'grade_status' => $grade['gradeStatus'] ?? null,
            'gratification' => $grade['gratification'] ?? null,
            'student_batch_id' => $student->student_batch_id,
            'school_branch_id' => $schoolId,
        ]);
    }

    private function getStudent($schoolId, $studentId)
    {
        return Student::where('school_branch_id', $schoolId)->find($studentId);
    }

    private function isDuplicateEntry($schoolId, $scoreData)
    {
        return Marks::where('school_branch_id', $schoolId)
            ->where('courses_id', $scoreData['courses_id'])
            ->where('exam_id', $scoreData['exam_id'])
            ->where('level_id', $scoreData['level_id'])
            ->where('specialty_id', $scoreData['specialty_id'])
            ->where('student_id', $scoreData['student_id'])
            ->exists();
    }

    private function validateCaMark($exam, $score)
    {
        if ($score > $exam->weighted_mark) {
            throw new Exception('Score exceeds maximum exam mark.', 400);
        }
    }

    private function calculateTotalScore($schoolId, $student, $scoreData, $exam)
    {
        $additionalExams = $this->findExamsBasedOnCriteria($scoreData['exam_id']);
        $ca_score = Marks::where('school_branch_id', $schoolId)
            ->where('exam_id', $additionalExams->id)
            ->where('student_id', $student->id)
            ->where('courses_id', $scoreData['courses_id'])
            ->first();

        if (!$ca_score) return false;

        $totalScore = $scoreData['score'] + $ca_score->score;
        $maxScore = $additionalExams->weighted_mark + $exam->weighted_mark;

        if ($totalScore > $maxScore) {
            throw new Exception('Total score exceeds maximum allowed score.', 400);
        }

        return $totalScore;
    }

    private function determineLetterGrade($score, $schoolId, $examId)
    {
        $grades = Grades::with('lettergrade')
            ->where('school_branch_id', $schoolId)
            ->where('exam_id', $examId)
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
                    'gratification' => $grade->gratifaction
                ];
            }
        }
        return ['letterGrade' => 'F', 'gradeStatus' => 'fail', 'gratification' => null];
    }

    private function determineExamType(string $exam_id)
    {
        $exam = Exams::with('examType')->find($exam_id);
        if (!$exam) return false;

        $examType = $exam->examType;
        if (!$examType) return false;

        $result = [
            'exam' => false,
            'ca' => false,
            'resit' => false,
        ];

        $supportedTypes = ['exam', 'ca', 'resit'];
        $result[$examType->type] = in_array($examType->type, $supportedTypes);

        return $result[$examType->type] ? $result : false;
    }

    public function findExamsBasedOnCriteria(string $examId)
    {
        $exam = Exams::with('examType')->find($examId);
        if (!$exam) {
            throw new Exception('Exam not found');
        }

        $examType = $exam->examType;
        if (!$examType || $examType->type !== 'exam') {
            throw new Exception('Exam type is not valid or not found');
        }

        $semester = $examType->semester;
        $caExamType = ExamType::where('semester', $semester)
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

    private function determineExamLetterGrade($score, $schooId, $student, $examId, $courseId)
    {
        $grades = Grades::with('lettergrade')
            ->where('school_branch_id', $schooId)
            ->where('exam_id', $examId)
            ->orderBy('minimum_score', 'asc')
            ->get();

        if ($grades->isEmpty()) {
            throw new Exception("No grades found for school ID: {$schooId} and exam ID: {$examId}");
        }

        foreach ($grades as $grade) {
            if ($score >= $grade->minimum_score && $score <= $grade->maximum_score) {
                $gradeStatus = $grade->grade_status;
                $gratification = $grade->gratifaction;
                $letterGrade = $grade->lettergrade->letter_grade;
                if ($gradeStatus == 'resit') {
                    $this->createResitableCourse($courseId, $examId, $student, $schooId);
                }
                return $letterGrade ?? 'N/A' && $gradeStatus && $gratification;
            }
        }
        return 'F';
    }

    private function createResitableCourse($courseId, $examId, $student, $schoolId)
    {
        $resitCourseExists = Resitablecourses::where('school_branch_id', $schoolId)
            ->where('specialty_id', $student->specialty_id)
            ->where('courses_id', $courseId)
            ->where('level_id', $student->level_id)
            ->where('student_batch_id', $student->student_batch_id)
            ->exists();
        if (!$resitCourseExists) {
            Resitablecourses::create([
                'school_branch_id' => $schoolId,
                'specialty_id' => $student->specialty_id,
                'courses_id' => $courseId,
                'exam_id' => $examId,
                'student_batch_id' => $student->student_batch_id,
                'level_id' => $student->level_id,
            ]);
            Studentresit::create([
                'school_branch_id' => $schoolId,
                'student_id' => $student->specialty_id,
                'course_id' => $courseId,
                'exam_id' => $examId,
                'student_batch_id' => $student->student_batch_id,
                'specialty_id' => $student->specialty_id,
                'level_id' => $student->level_id
            ]);
        } else {
            Studentresit::create([
                'school_branch_id' => $schoolId,
                'student_id' => $student->specialty_id,
                'course_id' => $courseId,
                'exam_id' => $examId,
                'specialty_id' => $student->specialty_id,
                'student_batch_id' => $student->student_batch_id,
                'level_id' => $student->level_id
            ]);
        }
    }
}
