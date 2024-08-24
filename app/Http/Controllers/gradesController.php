<?php

namespace App\Http\Controllers;
use App\Models\Grades;
use Illuminate\Http\Request;

class gradesController extends Controller
{
    //
    public function make_grade_for_exam_scoped(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $request->validate([
            'letter_grade' => 'required|string|max:1|min:1',
            'minimum_score' => 'required|decimal:min,max',
            'exam_id' => 'required|string'
        ]);
       
        $new_grade_instance = new Grades();
       
        $check_grade = Grades::Where('school_branch_id', $currentSchool->id)
        ->Where('exam_id', $request->grade_id)
        ->Where('minimum_score', $request->minimum_score)
        ->Where('letter_grade', $request->letter_grade)
        ->exist();

        if($check_grade){
            return response()->json(['message' => 'Grades already exist'], 409);
        }

        $new_grade_instance->school_branch_id = $currentSchool->id;
        $new_grade_instance->letter_grade = $request->letter_grade;
        $new_grade_instance->exam_id = $request->exam_id;
        $new_grade_instance->minimum_score = $request->minimum_score;


        $new_grade_instance->save();

        return response()->json(['message' => 'Grade created succefully'], 200);
        
    }

    public function get_all_grades_scoped(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $grades_data = Grades::where('school_branch_id', $currentSchool->id)
        ->with('exams')->get();
        return response()->json(['grades_data' => $grades_data], 200);
    }

    public function update_grades_scoped(Request $request, $grades_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $check_grades_data = Grades::where('school_branch_id', $currentSchool->id)
        ->find($grades_id);
        if(!$check_grades_data){
            return response()->json(['message' => 'grade data not found'], 409);
        }

        $grades_data = $request->all();
        $grades_data = array_filter($grades_data);
        $check_grades_data->fill($grades_data);

        return response()->json(['message' => 'Grade updated succefully'], 200);

    }

    public function delete_grades_scoped(Request $request, $grades_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $check_grades_data = Grades::where('school_branch_id', $currentSchool->id)
        ->find($grades_id);
        if(!$check_grades_data){
            return response()->json(['message' => 'grade data not found'], 409);
        }
        

        $check_grades_data->delete();

        return response()->json(['message' => 'Grade deleted sucessfully'], 200);
    }

    
}
