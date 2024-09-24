<?php

namespace App\Http\Controllers;
use App\Models\Grades;
use App\Models\Exams;
use Illuminate\Http\Request;

class gradesController extends Controller
{
    //when creating grades for EXAM (FIRST_SEMESTER, SECOND_SEMESTER, THIRD_SEMESTER, NTH_SEMESETER) 
    //You need to take into consideration the related weighted mark of the ca
    // eg if weighted mark for ca = 30 and weighted_exam_mark = 70 then means your grades of the exam will be based on
    // 30 + 70 = 100 which is 100
    public function make_grade_for_exam_scoped(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $request->validate([
            'letter_grade_id' => 'required|string',
            'minimum_score' => 'required|numeric|min:0|max:100', // Example values for a score
            'grade_points' => 'required',
            'exam_id' => 'required|string',
            'grade_status' => 'required|string'
        ]);
       
        $new_grade_instance = new Grades();
       
        $check_grade = Grades::Where('school_branch_id', $currentSchool->id)
        ->Where('exam_id', $request->exam_id)
        ->Where('minimum_score', $request->minimum_score)
        ->Where('letter_grade_id', $request->letter_grade_id)
        ->exists();

        if($check_grade){
            return response()->json([
                'status' => 'ok',
                'message' => 'Grades already exist',
                'existing_grade' => $check_grade
            ], 409);
        }
         
        $find_exam = Exams::where('school_branch_id', $currentSchool->id)
                           ->where('exam_id', $request->exam_id)
                            ->find();
        if($request->minimum_score > $find_exam->weighted_mark ){
            return response()->json([
                 'status' => 'ok',
                 'message' => 'Score cannot be greater than exam mark',
                 'exam_max_score' => $find_exam->weighted_mark,
                 'minimum_score' => $request->minimum_score
            ], 409);
        }
        $new_grade_instance->school_branch_id = $currentSchool->id;
        $new_grade_instance->letter_grade_id = $request->letter_grade_id;
        $new_grade_instance->grade_points = $request->grade_points;
        $new_grade_instance->exam_id = $request->exam_id;
        $new_grade_instance->grade_status = $request->grade_status;
        $new_grade_instance->minimum_score = $request->minimum_score;


        $new_grade_instance->save();

        return response()->json([
             'status' => 'ok',
            'message' => 'Grade created succefully',
             $new_grade_instance
        ], 200);
        
    }

    public function get_all_grades_scoped(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $grades_data = Grades::where('school_branch_id', $currentSchool->id)
        ->with(['exam.examtype.semesters', 'lettergrade'])->get();
        return response()->json(['grades_data' => $grades_data], 200);
    }

    public function update_grades_scoped(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $grades_id = $request->route('grade_id');
        $check_grades_data = Grades::where('school_branch_id', $currentSchool->id)
        ->find($grades_id);
        if(!$check_grades_data){
            return response()->json([
                'status' => 'ok',
                'message' => 'grade data not found'
            ], 409);
        }

        $grades_data = $request->all();
        $grades_data = array_filter($grades_data);
        $check_grades_data->fill($grades_data);
        $check_grades_data->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Grade updated succefully'
        ], 200);

    }

    public function delete_grades_scoped(Request $request, $grades_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $check_grades_data = Grades::where('school_branch_id', $currentSchool->id)
        ->find($grades_id);
        if(!$check_grades_data){
            return response()->json([
                'status' => 'error',
                'message' => 'grade data not found'
            ], 409);
        }

        $check_grades_data->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'Grade deleted sucessfully',
            'deleted_grade' => $check_grades_data
        ], 200);
    }

    
}
