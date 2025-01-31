<?php

namespace App\Http\Controllers;

use App\Models\Marks;
use App\Models\Grades;
use App\Models\Exams;
use App\Models\Examtimetable;
use App\Models\Resitablecourses;
use App\Models\Student;
use App\Models\Studentresit;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Examtype;
use Illuminate\Http\Request;

class marksController extends Controller
{
    public function add_student_mark(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');


        $request->validate([
            'student_scores' => 'required|array',
            'student_scores.*.student_id' => 'required|string',
            'student_scores.*.courses_id' => 'required|string',
            'student_scores.*.exam_id' => 'required|string',
            'student_scores.*.level_id' => 'required|string',
            'student_scores.*.specialty_id' => 'required|string',
            'student_scores.*.score' => 'required|numeric'
        ]);

        $results = [];

        DB::beginTransaction();

        try {
            foreach ($request->student_scores as $scoreData) {
                $student = $this->getStudent($currentSchool->id, $scoreData['student_id']);
                if (!$student) return $this->errorResponse('Student not found', 404);

                $exam = Exams::find($scoreData['exam_id']);
                if (!$exam) return $this->errorResponse('Exam not found', 404);

                if ($this->isDuplicateEntry($currentSchool->id, $scoreData)) {
                    return $this->errorResponse('Duplicate data entry for this student', 409);
                }

                $determined_exam = $this->determineExamType($scoreData['exam_id']);
                if ($determined_exam === false) {
                    return $this->errorResponse('Exam type not found.', 400);
                }

                $grade = null;
                if (isset($determined_exam['ca']) && $determined_exam['ca']) {
                    $this->validateCaMark($exam, $scoreData['score']);
                    $grade = $this->determine_letter_grade($scoreData['score'], $currentSchool->id, $scoreData['exam_id']);
                } elseif (isset($determined_exam['exam']) && $determined_exam['exam']) {
                    $totalScore = $this->calculateTotalScore($currentSchool->id, $student, $scoreData, $exam);
                    if ($totalScore === false) return $this->errorResponse('CA mark not found for this course', 400);
                    $grade = $this->determine_letter_grade($totalScore, $currentSchool->id, $scoreData['exam_id']);
                    $scoreData['score'] = $totalScore;  // Use total score for database entry
                } else {
                    return $this->errorResponse('Exam type not found', 400);
                }

                $marks = Marks::create(array_merge($scoreData, [
                    'grade' => $grade,
                    'student_batch_id' => $student->student_batch_id,
                    'school_branch_id' => $currentSchool->id,
                ]));

                $results[] = $marks;
            }


            DB::commit();

            return response()->json([
                'status' => 'ok',
                'message' => 'Marks submitted successfully',
                'results' => $results
            ], 200);
        } catch (\Exception $e) {

            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
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
            throw new \Exception('Score exceeds maximum exam mark.', 400);
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
            throw new \Exception('Total score exceeds maximum allowed score.', 400);
        }

        return $totalScore;
    }

    private function errorResponse($message, $statusCode)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $statusCode);
    }

    private function determine_letter_grade($score, $schoolId, $examId)
    {
        $grades = Grades::with('lettergrade')
            ->where('school_branch_id', $schoolId)
            ->where('exam_id', $examId)
            ->orderBy('minimum_score', 'asc')
            ->get();

        if ($grades->isEmpty()) {
            throw new \Exception("No grades found for school ID: {$schoolId} and exam ID: {$examId}");
        }

        foreach ($grades as $grade) {
            if ($score >= $grade->minimum_score && $score <= $grade->maximum_score) {
                return $grade->lettergrade->letter_grade ?? 'N/A';
            }
        }
        return 'F';
    }

    private function determineExamType(string $exam_id)
    {
        $exam = Exams::with('examType')->find($exam_id);
        if (!$exam) return false;

        $examType = $exam->examType;
        if (!$examType) {
            return false;
        }

        // Initialize result array
        $result = [
            'exam' => false,
            'ca' => false,
            'resit' => false,
        ];

        // Define supported exam types
        $supportedTypes = ['exam', 'ca', 'resit'];
        $result[$examType->type] = in_array($examType->type, $supportedTypes);

        // Return false if the type is not supported
        return $result[$examType->type] ? $result : false;
    }

    public function findExamsBasedOnCriteria(string $examId)
    {
        $exam = Exams::with('examType')->find($examId);
        if (!$exam) {
            throw new ModelNotFoundException('Exam not found');
        }

        $examType = $exam->examType;
        if (!$examType || $examType->type !== 'exam') {
            throw new ModelNotFoundException('Exam type is not valid or not found');
        }

        $semester = $examType->semester;
        $caExamType = ExamType::where('semester', $semester)
            ->where('type', 'ca')
            ->first();

        if (!$caExamType) {
            throw new ModelNotFoundException('Corresponding CA exam type not found');
        }

        $additionalExam = Exams::where('school_year', $exam->school_year)
            ->where('exam_type_id', $caExamType->id)
            ->where('specialty_id', $exam->specialty_id)
            ->where('level_id', $exam->level_id)
            ->where('semester_id', $exam->semester_id)
            ->where('department_id', $exam->department_id)
            ->first();

        if (!$additionalExam) {
            throw new ModelNotFoundException('No additional exam found');
        }

        return $additionalExam;
    }


    private function create_resitable_courses($letter_grade, $courses_id, $exam_id, $specialty_id, $currentSchool, $level_id, $student_id)
    {
        $check_if_resit_course_already_exist = Resitablecourses::where('school_branch_id', $currentSchool->id)
            ->where('specialty_id', $specialty_id)
            ->where('courses_id', $courses_id)
            ->where('level_id', $level_id)
            ->exists();
        $grades = Grades::with('lettergrade')
            ->where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $exam_id)
            ->orderBy('minimum_score', 'desc')
            ->get();

        if ($check_if_resit_course_already_exist) {
            foreach ($grades as $grade_data) {
                if (
                    $grade_data->lettergrade->letter_grade === $letter_grade &&
                    $grade_data->grade_status === 'resit'
                ) {
                    Studentresit::create([
                        'school_branch_id' => $currentSchool->id,
                        'student_id' => $student_id,
                        'course_id' => $courses_id,
                        'exam_id' => $exam_id,
                        'specialty_id' => $specialty_id,
                        'level_id' => $level_id
                    ]);
                    break;
                }
            }
        } else {

            foreach ($grades as $grade_data) {
                if (
                    $grade_data->lettergrade->letter_grade === $letter_grade &&
                    $grade_data->grade_status === 'resit'
                ) {

                    Resitablecourses::create([
                        'school_branch_id' => $currentSchool->id,
                        'specialty_id' => $specialty_id,
                        'courses_id' => $courses_id,
                        'exam_id' => $exam_id,
                        'level_id' => $level_id,
                    ]);
                    break;
                }
            }
        }
    }

    public function delete_mark_of_student_scoped(Request $request, $mark_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $check_if_mark_exist = Marks::Where('school_branch_id', $currentSchool->id)->find($mark_id);

        if (!$check_if_mark_exist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student mark record not found'
            ], 409);
        }

        $check_if_mark_exist->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'Student mark deleted succefully',
            'deleted_mark' => $check_if_mark_exist
        ], 200);
    }

    public function update_student_mark_scoped(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $mark_id = $request->route('mark_id');
        $check_if_mark_exists = Marks::Where('school_branch_id', $currentSchool->id)->find($mark_id);

        if (!$check_if_mark_exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student mark record not found'
            ], 409);
        }

        $student_mark_data = $request->all();
        $student_mark_data = array_filter($student_mark_data);
        $check_if_mark_exists->fill($student_mark_data);

        $check_if_mark_exists->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Student mark updated succefully',
            'update_mark' => $check_if_mark_exists
        ], 200);
    }

    public function get_all_student_marks(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $exam_id = $request->route('exam_id');
        $student_id = $request->route('student_id');

        $find_student = Student::where('school_branch_id', $currentSchool->id)->find($student_id);
        $find_exam = Exams::where('school_branch_id', $currentSchool->id)->find($exam_id);

        if (!$student_id || !$find_exam || is_null($find_student)) {
            return response()->json([
                'status' => 'error',
                'message' => 'The provided credentials are invalid'
            ]);
        }
        $scores_data = Marks::where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $exam_id)
            ->where('student_id', $find_student->id)
            ->with(['student', 'course', 'exams.examtype', 'level'])
            ->get();
        return response()->json([
            'status' => 'ok',
            'message' => 'scores fetched successfully',
            'student_scores_data' => $scores_data
        ], 201);
    }

    public function get_all_student_scores(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $student_scores = Marks::where("school_branch_id", $currentSchool->id)->with(['course', 'student', 'exams.examtype', 'level', 'specialty'])->get();


        return response()->json([
            "status" => "ok",
            "message" => "student scores fetched successfully",
            "scores" => $student_scores
        ], 201);
    }

    public function get_exam_score_details(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $mark_id = $request->route("mark_id");
        $find_exam_score = Marks::find($mark_id);
        if (!$find_exam_score) {
            return response()->json([
                "status" => "error",
                "message" => "Exam score not found"
            ], 400);
        }

        $marks_details = Marks::where("school_branch_id", $currentSchool->id)
            ->where("id", $mark_id)
            ->with(['student', 'course', 'exams', 'specialty', 'level'])
            ->get();
        return response()->json([
            "status" => "ok",
            "message" => "score details fetched succefully",
            "score_details" => $marks_details
        ], 200);
    }

    public function get_exam_score_associated_data(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $exam_id = $request->route("exam_id");
        $student_id = $request->route("student_id");

        $find_student = Student::find($student_id);
        $find_exam = Exams::find($exam_id);
        if (!$find_student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student not found'
            ], 400);
        }

        if (!$find_exam) {
            return response()->json([
                "status" => "error",
                "message" => "exam not found"
            ], 400);
        }

        $specailty_id = $find_student->specialty_id;
        $level_id = $find_student->level_id;
        $get_exam_courses = Examtimetable::where("school_branch_id", $currentSchool->id)
            ->where("exam_id", $exam_id)
            ->where("specialty_id", $specailty_id)
            ->with(["course"])
            ->get();

        $results = [];
        foreach ($get_exam_courses as $course) {

            $results[] = [
                "level_id" => $level_id,
                "course_id" => $course->course->id,
                "course_name" => $course->course->course_title,
                "exam_id" => $exam_id,
                "specailty_id" => $course->specialty_id,
                "weighted_mark" => $find_exam->weighted_mark,
                "student_id" => $student_id
            ];
        }

        $results_two = [];
        $grades_calculator_data = Grades::where("school_branch_id", $currentSchool->id)
            ->where("exam_id", $exam_id)
            ->with(["lettergrade"])
            ->get();

        foreach ($grades_calculator_data as $grade) {

            $results_two[] = [
                "id" => $grade->id,
                "letter_grade" => $grade->lettergrade->letter_grade,
                "grade_points" => $grade->grade_points,
                "minimum_score" => $grade->minimum_score
            ];
        }

        return response()->json([
            "status" => "ok",
            "message" => "Data fetched successfully",
            "accessed_courses" => $results,
            "grades_determinant" => $results_two
        ], 200);
    }
}
