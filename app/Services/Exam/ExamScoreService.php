<?php

namespace App\Services\Exam;

use App\Exceptions\AppException;
use App\Models\Marks;
use Exception;
use App\Models\Student;
use App\Models\Examtimetable;
use App\Models\Exams;
use App\Models\Grades;
use App\Models\StudentResults;
use App\Models\AccessedStudent;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ExamScoreService
{
    public function getMarksByCandidate(string $candidateId, object $currentSchool)
    {
        try {
            $candidate = AccessedStudent::findorFail($candidateId);

            $marks = Marks::where("school_branch_id", $currentSchool->id)
                ->where("student_id", $candidate->student_id)
                ->where("level_id", $candidate->level_id)
                ->where("specialty_id", $candidate->specialty_id)
                ->get();
            if ($marks->isEmpty()) {
                throw new AppException(
                    "No marks found for this candidate.",
                    404,
                    "No Marks Found",
                    "There are no marks available for the selected candidate. it might be that the candidate has not been evaluated yet. or the marks have been deleted By mistake",
                    "/candidates"
                );
            }
            return $marks;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while fetching marks. Please try again later.",
                500,
                "Server Error",
                "We encountered an unexpected issue while retrieving the marks.",
                null
            );
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "No exam candidates found for this school branch.",
                404,
                "No Candidates Found",
                "There are no exam candidates available. Candidates are automatically created when you create an exam.",
                "/exams"
            );
        }
    }
    public function getCaMarksByExamCandidate(string $candidateId, object $currentSchool)
    {
        try {
            $candidate = AccessedStudent::find($candidateId);
            if (!$candidate) {
                throw new AppException(
                    'Exam Candidate Not Found',
                    404,
                    'Candidate Not Found',
                    'The specified exam candidate does not exist. Please verify the candidate was not deleted or the ID is correct',
                    '/candidates'
                );
            }
            $exam = Exams::where("school_branch_id", $currentSchool->id)
                ->find($candidate->exam_id);

            if (!$exam) {
                throw new AppException(
                    'Exam Not Found',
                    404,
                    'Exam Not Found',
                    'The exam associated with this candidate does not exist. Please verify the exam was not deleted or the ID is correct',
                    '/exams'
                );
            }

            if ($exam->grades_category_id === null) {
                throw new AppException(
                    'Exam Grading Not Set',
                    400,
                    'Grading Not Set',
                    'The grading category for this exam has not been set. Please set the grading category in the exam settings to proceed.',
                    "/exams/{$exam->id}/edit"
                );
            }
            $examGrades = Grades::where("school_branch_id", $currentSchool->id)
                ->where("grades_category_id", $exam->grades_category_id)
                ->with(['lettergrade'])
                ->get();

            if ($examGrades->isEmpty()) {
                throw new AppException(
                    'Exam Grading Not Found',
                    404,
                    'Grading Not Found',
                    'No grading records found for the grading category associated with this exam. Please ensure that grades have been set up for this category.',
                    "/grades-categories"
                );
            }

            $marks = Marks::where("school_branch_id", $currentSchool->id)
                ->where("student_id", $candidate->student_id)
                ->where("level_id", $candidate->level_id)
                ->where("specialty_id", $candidate->specialty_id)
                ->where("exam_id", $candidate->exam_id)
                ->with(['course'])
                ->get();

            if ($marks->isEmpty()) {
                throw new AppException(
                    'No CA marks found for this candidate.',
                    404,
                    'No CA Marks Found',
                    'There are no CA marks available for the selected candidate. it might be that the candidate has not been evaluated yet. or the marks have been deleted By mistake',
                    '/candidates'
                );
            }
            return [
                'ca_marks' => $marks,
                'exam_grades' => $examGrades,
                'max_gpa' => $currentSchool->max_gpa ?? 4.00
            ];
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while fetching CA marks. Please try again later.",
                500,
                "Server Error",
                "We encountered an unexpected issue while retrieving the CA marks.",
                null
            );
        }
    }
    public function getExamMarksByExamCandidate(string $candidateId, object $currentSchool)
    {
        try {
            $candidate = AccessedStudent::find($candidateId);
            $caExam = $this->findExamsBasedOnCriteria($candidate->exam_id);
            $exam = Exams::where("school_branch_id", $currentSchool->id)->findorFail($candidate->exam_id);
            $examGrades = Grades::where("school_branch_id", $currentSchool->id)
                ->where("grades_category_id", $exam->grades_category_id)
                ->with(['lettergrade'])
                ->get();

            if ($examGrades->isEmpty()) {
                throw new AppException(
                    'Exam Grading Not Found',
                    404,
                    'Grading Not Found',
                    'No grading records found for the grading category associated with this exam. Please ensure that grades have been set up for this category.',
                    "/grades-categories"
                );
            }

            $examMarks = Marks::where("school_branch_id", $currentSchool->id)
                ->where("student_id", $candidate->student_id)
                ->where("level_id", $candidate->level_id)
                ->where("specialty_id", $candidate->specialty_id)
                ->where("exam_id", $candidate->exam_id)
                ->with(['course'])
                ->get();

            if ($examMarks->isEmpty()) {
                throw new AppException(
                    'No exam marks found for this candidate.',
                    404,
                    'No Exam Marks Found',
                    'There are no exam marks available for the selected candidate. it might be that the candidate has not been evaluated yet. or the marks have been deleted By mistake',
                    '/candidates'
                );
            }

            $caMarks = Marks::where("school_branch_id", $currentSchool->id)
                ->where("student_id", $candidate->student_id)
                ->where("level_id", $candidate->level_id)
                ->where("specialty_id", $candidate->specialty_id)
                ->where("exam_id", $caExam->id)
                ->get();

            if ($caMarks->isEmpty()) {
                throw new AppException(
                    'No CA marks found for this candidate.',
                    404,
                    'No CA Marks Found',
                    'There are no CA marks available for the selected candidate. it might be that the candidate has not been evaluated yet. or the marks have been deleted By mistake',
                    '/candidates'
                );
            }

            $mappedMarks = [];
            $examMarksByCourse = $examMarks->keyBy('courses_id');
            $caMarksByCourse = $caMarks->keyBy('courses_id');

            $courseIds = collect(array_merge(
                $examMarks->pluck('courses_id')->toArray(),
                $caMarks->pluck('courses_id')->toArray()
            ))->unique();

            if ($courseIds->isEmpty()) {
                throw new AppException(
                    'No courses found for the marks of this candidate.',
                    404,
                    'No Courses Found',
                    'There are no courses associated with the marks of the selected candidate. Please ensure that the candidate has been evaluated in at least one course.',
                    '/courses'
                );
            }

            foreach ($courseIds as $courseId) {
                $examMark = $examMarksByCourse->get($courseId);
                $caMark = $caMarksByCourse->get($courseId);

                $mappedMark = [
                    'exam_mark_id' => $examMark ? $examMark->id : null,
                    'course_id' => $courseId,
                    'student_id' => $examMark ? $examMark->student_id : ($caMark ? $caMark->student_id : null),
                    'exam_id' => $examMark ? $examMark->exam_id : ($caMark ? $caMark->exam_id : null),
                    'level_id' => $examMark ? $examMark->level_id : ($caMark ? $caMark->level_id : null),
                    'specialty_id' => $examMark ? $examMark->specialty_id : ($caMark ? $caMark->specialty_id : null),
                    'student_batch_id' => $examMark ? $examMark->student_batch_id : ($caMark ? $caMark->student_batch_id : null),
                    'exam_score' => $examMark ? $examMark->score : null,
                    'ca_score' => $caMark ? $caMark->score : null,
                    'exam_grade_points' => $examMark ? $examMark->grade_points : null,
                    'ca_grade_points' => $caMark ? $caMark->grade_points : null,
                    'exam_grade' => $examMark ? $examMark->grade : null,
                    'ca_grade' => $caMark ? $caMark->grade : null,
                    'exam_grade_status' => $examMark ? $examMark->grade_status : null,
                    'ca_grade_status' => $caMark ? $caMark->grade_status : null,
                    'exam_resit_status' => $examMark ? $examMark->resit_status : null,
                    'ca_resit_status' => $caMark ? $caMark->resit_status : null,
                    'exam_gratification' => $examMark ? $examMark->gratification : null,
                    'ca_gratification' => $caMark ? $caMark->gratification : null,
                    'course' => $examMark && $examMark->course ? $examMark->course : ($caMark && $caMark->course ? $caMark->course : null),
                ];

                $mappedMarks[] = $mappedMark;
            }

            return [
                'mapped_marks' => $mappedMarks,
                'exam_grades' => $examGrades,
                'max_gpa' => $currentSchool->max_gpa ?? 4.00
            ];
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while fetching CA marks. Please try again later.",
                500,
                "Server Error",
                "We encountered an unexpected issue while retrieving the CA marks.",
                null
            );
        }
    }
    public function deleteMark(string $markId, object $currentSchool)
    {

        $markExists = Marks::Where('school_branch_id', $currentSchool->id)->find($markId);
        if (!$markExists) {
            throw new AppException(
                "The mark you are trying to delete was not found.",
                404,
                "Mark Not Found",
                "The mark record does not exist. It may have already been deleted.",
                "/marks"
            );
        }
        $markExists->delete();
        return $markExists;
    }
    public function getStudentScores(string $studentId, object $currentSchool, string $examId)
    {
        $student = Student::where('school_branch_id', $currentSchool->id)
            ->find($studentId);
        if (!$student) {
            throw new AppException(
                "The student with the provided ID was not found.",
                404,
                "Student Not Found",
                "Please verify that the student ID is correct and that the student exists in the system.",
                "/students"
            );
        }

        $exam = Exams::where('school_branch_id', $currentSchool->id)
            ->find($examId);

        if (!$exam) {
            throw new AppException(
                "The exam with the provided ID was not found.",
                404,
                "Exam Not Found",
                "Please verify that the exam ID is correct and that the exam exists in the system.",
                "/exams"
            );
        }

        $scoresData = Marks::where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->with(['student', 'course', 'exams.examtype', 'level'])
            ->get();

        return $scoresData;
    }
    public function getScoreDetails(string $markId, object $currentSchool)
    {
        $findScore = Marks::where("school_branch_id", $currentSchool->id)
            ->with(['student', 'course', 'exams', 'specialty', 'level'])
            ->find($markId);
        if (!$findScore) {
            throw new AppException(
                "The mark with the provided ID was not found.",
                404,
                "Mark Not Found",
                "Please verify that the mark ID is correct and that the mark exists in the system.",
                "/marks"
            );
        }
        return $findScore;
    }
    public function getAcessedCourses(string $examId, string $studentId, object $currentSchool)
    {
        $student = Student::where("school_branch_id", $currentSchool->id)
            ->find($studentId);

        if (!$student) {
            throw new AppException(
                "The student with the provided ID was not found.",
                404,
                "Student Not Found",
                "Please verify that the student ID is correct and that the student exists in the system.",
                "/students"
            );
        }
        $exam = Exams::where("school_branch_id", $currentSchool->id)
            ->find($examId);

        if (!$exam) {
            throw new AppException(
                "The exam with the provided ID was not found.",
                404,
                "Exam Not Found",
                "Please verify that the exam ID is correct and that the exam exists in the system.",
                "/exams"
            );
        }

        $examCourses = Examtimetable::where("school_branch_id", $currentSchool->id)
            ->where("exam_id", $exam->id)
            ->where("specialty_id", $student->specialty_id)
            ->with(["course"])
            ->get();

        if ($examCourses->isEmpty()) {
            throw new AppException(
                "No courses found for the specified exam and student specialty.",
                404,
                "No Courses Found",
                "There are no courses associated with the specified exam and the student's specialty. Please ensure that the exam timetable has been set up correctly.",
                "/exam-timetables"
            );
        }

        return [
            'exam' => $exam,
            'student' => $student,
            'courses' => $examCourses
        ];
    }
    public function getAllStudentsScores(object $currentSchool)
    {
        $studentScores = Marks::where("school_branch_id", $currentSchool->id)->with(['course', 'student', 'exams.examtype', 'level', 'specialty'])->get();
        return $studentScores;
    }
    public function prepareCaDataByExam(object $currentSchool, string $studentId, string $examId): array
    {
        $exam = Exams::where("school_branch_id", $currentSchool->id)->find($examId);
        if (!$exam) {
            throw new AppException(
                "The exam with the provided ID was not found.",
                404,
                "Exam Not Found",
                "Please verify that the exam ID is correct and that the exam exists in the system.",
                "/exams"
            );
        }
        $student = Student::where("school_branch_id", $currentSchool->id)->find($studentId);
        if (!$student) {
            throw new AppException(
                "The student with the provided ID was not found.",
                404,
                "Student Not Found",
                "Please verify that the student ID is correct and that the student exists in the system.",
                "/students"
            );
        }
        $caExam = $this->findExamsBasedOnCriteria($exam->id);
        $caScores = Marks::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->where("exam_id", $caExam->id)
            ->with(['course'])
            ->get();

        if ($caScores->isEmpty()) {
            throw new AppException(
                'No CA marks found for this student in the specified exam.',
                404,
                'No CA Marks Found',
                'There are no CA marks available for the selected student in relation to the specified exam. It might be that the student has not been evaluated yet or the marks have been deleted by mistake.',
                '/marks'
            );
        }
        $caResult = StudentResults::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->where("exam_id", $caExam->id)
            ->get();
        if ($caResult->isEmpty()) {
            throw new AppException(
                'No CA results found for this student in the specified exam.',
                404,
                'No CA Results Found',
                'There are no CA results available for the selected student in relation to the specified exam. It might be that the student has not been evaluated yet or the results have been deleted by mistake.',
                '/student-results'
            );
        }
        return [
            'exam' => $exam,
            'student' => $student,
            'caExam' => $caExam,
            'caScores' => $caScores,
            'caResult' => $caResult,
        ];
    }
    public function prepareCaData(object $currentSchool, string $examId, string $studentId): array
    {
        $exam = Exams::where("school_branch_id", $currentSchool->id)->find($examId);
        if (!$exam) {
            throw new AppException(
                "The exam with the provided ID was not found.",
                404,
                "Exam Not Found",
                "Please verify that the ca exam is correct and that the exam exists in the system.",
                "/exams"
            );
        }
        $student = Student::where("school_branch_id", $currentSchool->id)->find($studentId);
        if (!$student) {
            throw new AppException(
                "The student with the provided ID was not found.",
                404,
                "Student Not Found",
                "Please verify that the student ID is correct and that the student exists in the system.",
                "/students"
            );
        }
        $caScores = Marks::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->where("exam_id", $exam->id)
            ->with(['course'])
            ->get();

        if ($caScores->isEmpty()) {
            throw new AppException(
                'No CA marks found for this student in the specified exam.',
                404,
                'No CA Marks Found',
                'There are no CA marks available for the selected student in relation to the specified exam. It might be that the student has not been evaluated yet or the marks have been deleted by mistake.',
                '/marks'
            );
        }

        $caResult = StudentResults::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->where("exam_id", $exam->id)
            ->get();

        if ($caResult->isEmpty()) {
            throw new AppException(
                'No CA results found for this student in the specified exam.',
                404,
                'No CA Results Found',
                'There are no CA results available for the selected student in relation to the specified exam. It might be that the student has not been evaluated yet or the results have been deleted by mistake.',
                '/student-results'
            );
        }
        return [
            'exam' => $exam,
            'student' => $student,
            'ca_scores' => $caScores,
            'ca_result' => $caResult
        ];
    }
    public function prepareExamData(object $currentSchool,string $examId, string $studentId)
    {
        $exam = Exams::where("school_branch_id", $currentSchool->id)->find($examId);
        if (!$exam) {
            throw new AppException(
                "The exam with the provided ID was not found.",
                404,
                "Exam Not Found",
                "Please verify that the exam ID is correct and that the exam exists in the system.",
                "/exams"
            );
        }
        $caExam = $this->findExamsBasedOnCriteria($exam->id);
        $student = Student::where("school_branch_id", $currentSchool->id)->find($studentId);
        if (!$student) {
            throw new AppException(
                "The student with the provided ID was not found.",
                404,
                "Student Not Found",
                "Please verify that the student ID is correct and that the student exists in the system.",
                "/students"
            );
        }
        $examScores = Marks::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->where("exam_id", $exam->id)
            ->with(['course'])
            ->get();
        if ($examScores->isEmpty()) {
            throw new AppException(
                'No exam marks found for this student in the specified exam.',
                404,
                'No Exam Marks Found',
                'There are no exam marks available for the selected student in relation to the specified exam. It might be that the student has not been evaluated yet or the marks have been deleted by mistake.',
                '/marks'
            );
        }
        $examResult = StudentResults::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->where("exam_id", $exam->id)
            ->get();
        if ($examResult->isEmpty()) {
            throw new AppException(
                'No exam results found for this student in the specified exam.',
                404,
                'No Exam Results Found',
                'There are no exam results available for the selected student in relation to the specified exam. It might be that the student has not been evaluated yet or the results have been deleted by mistake.',
                '/student-results'
            );
        }
        $caScores = Marks::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->where("exam_id", $caExam->id)
            ->with(['course'])
            ->get();
        if ($caScores->isEmpty()) {
            throw new AppException(
                'No CA marks found for this student in the specified exam.',
                404,
                'No CA Marks Found',
                'There are no CA marks available for the selected student in relation to the specified exam. It might be that the student has not been evaluated yet or the marks have been deleted by mistake.',
                '/marks'
            );
        }
        $caResult = StudentResults::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->where("exam_id", $caExam->id)
            ->get();
        if ($caResult->isEmpty()) {
            throw new AppException(
                'No CA results found for this student in the specified exam.',
                404,
                'No CA Results Found',
                'There are no CA results available for the selected student in relation to the specified exam. It might be that the student has not been evaluated yet or the results have been deleted by mistake.',
                '/student-results'
            );
        }
        return [
            'exam' => $exam,
            'student' => $student,
            'exam_scores' => $examScores,
            'exam_result' => $examResult,
            'ca_scores' => $caScores,
            'ca_result' => $caResult
        ];
    }

}
