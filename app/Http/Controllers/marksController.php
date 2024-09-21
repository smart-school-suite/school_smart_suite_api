<?php

namespace App\Http\Controllers;

use App\Models\Marks;
use App\Models\Grades;
use App\Models\Exams;
use App\Models\Resitablecourses;
use App\Models\Student;
use Illuminate\Http\Request;

class marksController extends Controller
{
    public function add_student_mark(Request $request)
    {
        $request->validate([
            'student_id' => 'required|string',
            'courses_id' => 'required|string',
            'exam_id' => 'required|string',
            'level_id' => 'required|string',
            'specialty_id' => 'required|string',
            'score' => 'required'
        ]);

        $currentSchool = $request->attributes->get('currentSchool');
        //checking if student with this mark already exist

        $find_student = Student::where('school_branch_id', $currentSchool->id)->find($request->student_id);
        if (!$find_student) {
            return response()->json([
                'status' => 'ok',
                'message' => 'student not found'
            ], 404);
        }

        $check_if_duplicate_records = Marks::Where('school_branch_id', $currentSchool->id)
            ->Where('courses_id', $request->courses_id)
            ->Where('exam_id', $request->exam_id)
            ->Where('level_id', $request->level_id)
            ->Where('specialty_id', $request->specialty_id)
            ->Where('student_id', $request->student_id)
            ->exists();

        if ($check_if_duplicate_records) {
            return response()->json([
                'status' => 'ok',
                'message' => 'No student can have dublicated data entries',
            ], 409);
        }


        $exam = Exams::Where('school_branch_id', $currentSchool->id)
            ->findOrFail($request->exam_id);

        $exam_configuration = $this->exam_config($request->exam_id, $currentSchool);
        if (empty($exam_configuration['related_ca'])) {
            if ($request->score > $exam->weighted_mark) {
                return response()->json([
                    'status' => 'ok',
                    'message' => 'Score cannot be greater than the exam mark.',
                ], 409);
            }

            // Determine the grade using the custom grading logic
            $grade = $this->determine_letter_grade_of_ca($request->score, $currentSchool->id, $request->exam_id);
            Marks::create([
                'student_id' => $request->student_id,
                'exam_id' => $exam->id,
                'score' => $request->score,
                'grade' => $grade,
                'student_batch_id' => $find_student->student_batch_id,
                'level_id' => $request->level_id,
                'courses_id' => $request->courses_id, // Assuming this is being provided in the request
                'school_branch_id' => $currentSchool->id,
                'specialty_id' => $request->specialty_id
            ]);

            return response()->json([
                'status' => 'ok',
                'message' => 'Marks added successfully!',
                'grade' =>   $grade
            ], 201);
        } else {
            $get_ca_score = Marks::where('school_branch_id', $currentSchool->id)
                 ->where('exam_id', $exam_configuration['related_ca']->id)
                ->where('student_id', $find_student->id)
                ->where('courses_id', $request->courses_id)
                ->first();
            if($get_ca_score === null){
                return response()->json([
                    'status' => 'ok',
                    'message' => 'This student does not have a CA mark for this course'
                ], 400);
            }
            if ($request->score + $get_ca_score->score > $exam_configuration['related_ca']->weighted_mark + $exam->weighted_mark) {
                return response()->json([
                    'message' => 'student mark exam score plus student marks for ca is grater than the weighted mark for this exam',
                    'ca_score' =>  $get_ca_score->score,
                    'exam_score' => $request->score,
                    'total_score' => $request->score + $get_ca_score->score,
                    'max_exam_score' => $exam_configuration['related_ca']->weighted_mark + $exam->weighted_mark
                ]);
            }
            $grade = $this->determine_letter_grade_of_exam(
                $request->score,
                $get_ca_score->score,
                $currentSchool->id,
                $request->exam_id
            );
            
            $this->create_resitable_courses($grade, $request->courses_id, 
            $request->exam_id, $request->specialty_id, $currentSchool, $request->level_id);
            
            Marks::create([
                'student_id' => $request->student_id,
                'exam_id' => $exam->id,
                'score' => $request->score  + $get_ca_score->score,
                'grade' => $grade,
                'student_batch_id' => $find_student->student_batch_id,
                'level_id' => $request->level_id,
                'courses_id' => $request->courses_id, // Assuming this is being provided in the request
                'school_branch_id' => $currentSchool->id,
                'specialty_id' => $request->specialty_id
            ]);

            return response()->json([
                'status' => 'ok',
                'message' => 'Marks added successfully!',
                'ca_score' => $get_ca_score->score,
                'exam_score' => $request->score,
                'total_score' => $request->score + $get_ca_score->score,
                'grade' =>   $grade
            ], 201);
        }

        // Create Marks entry

    }

    private function determine_letter_grade_of_ca($score, $schoolId, $exam_id)
    {

        $grades = Grades::with('lettergrade') // Eager loading the lettergrade relationship
            ->where('school_branch_id', $schoolId)
            ->where('exam_id', $exam_id)
            ->orderBy('minimum_score', 'desc')
            ->get();
        foreach ($grades as $grade) {
            if ($score >= $grade->minimum_score) {
                return $grade->lettergrade->letter_grade; // Accessing the letter grade via the relationship
            }
        }

        return 'F';  // Default if no grade matches
    }

    private function determine_letter_grade_of_exam($score, $ca_score, $schoolId,  $exam_id)
    {
        $grades = Grades::with('lettergrade') // Eager loading the lettergrade relationship
            ->where('school_branch_id', $schoolId)
            ->where('exam_id', $exam_id)
            ->orderBy('minimum_score', 'desc')
            ->get();

        foreach ($grades as $grade) {
            if ($score + $ca_score >= $grade->minimum_score) {
                return $grade->lettergrade->letter_grade; // Accessing the letter grade via the relationship
            }
        }
        return 'F';  // Default if no grade matches
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
            'deleted_mark' => $check_if_mark_exists 
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
                'status' => 'ok',
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

    private function exam_config($exam_id, $currentSchool)
    {
        $exam = Exams::with('examtype')->where('school_branch_id', $currentSchool->id)->find($exam_id);
        if (!$exam) {
            return null;
        }
        $programName = $exam->examtype->program_name;
        $related_exams = [
            'first_semester_exam' => 'first_semester_ca',
            'second_semester_exam' => 'second_semester_ca',
            'third_semester_exam' => 'third_semester_ca',
            'fourth_semester_exam' => 'fourth_semester_ca',
            'fifth_semester_exam' => 'fifth_semester_ca'
        ];
        if (strpos($programName, '_ca') !== false) { // If it contains '_ca' (for CAs)
            return ['exam' => $exam->load('examtype'), 'related_ca' => null]; // Return exam data with exam type as-is.
        } elseif (array_key_exists($programName, $related_exams)) {
            // If it maps to an exam type, get related CA.
            $related_ca_program_name = $related_exams[$programName];

            // Fetch the exam type for the related CA program name.
            $relatedExamType = \App\Models\Examtype::where('program_name', $related_ca_program_name)->first();

            if ($relatedExamType) {
                // Now retrieve the CA exam based on the related exam type ID.
                $caExam = \App\Models\Exams::with('examtype')->where('exam_type_id', $relatedExamType->id)->first();

                // Return both exam and related CA exam details.
                return [
                    'exam' => $exam->load('examtype'),
                    'related_ca' => $caExam ? $caExam->load('examtype') : null,
                ];
            }
        }
        return [
            'exam' => $exam->load('examtype'),
            'related_ca' => null
        ];
    }

    private function create_resitable_courses($letter_grade, $courses_id, $exam_id, $specialty_id, $currentSchool, $level_id)
    {
        $check_if_resit_course_already_exist = Resitablecourses::where('school_branch_id', $currentSchool->id)
        ->where('specialty_id', $specialty_id)
        ->where('courses_id', $courses_id)
        ->where('level_id', $level_id)
        ->exists();
        
        if ($check_if_resit_course_already_exist) {
            return; 
        } else {
            $grades = Grades::with('lettergrade') 
            ->where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $exam_id)
            ->orderBy('minimum_score', 'desc')
            ->get();
            
            foreach ($grades as $grade_data) {
                if ($grade_data->lettergrade->letter_grade === $letter_grade && 
                $grade_data->grade_status === 'resit') {
                    
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
}
