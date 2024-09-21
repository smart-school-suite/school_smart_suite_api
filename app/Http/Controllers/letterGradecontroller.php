<?php

namespace App\Http\Controllers;

use App\Models\LetterGrade;
use Illuminate\Http\Request;

class letterGradecontroller extends Controller
{
    //

    public function create_letter_grade(Request $request){
        $request->validate([
            'letter_grade' => 'required|string'
        ]);

        $new_letter_grade_instance = new LetterGrade();
        
        $new_letter_grade_instance->letter_grade = $request->letter_grade;

        $new_letter_grade_instance->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Letter grade created succesfully',
            'created_letter_grade' => $new_letter_grade_instance
        ], 200);
    }

    public function get_all_letter_grades(Request $request){
        $grades_data = LetterGrade::all();
         
        if($grades_data->isEmpty()){
            return response()->json([
                'status' => 'error',
                'message' => 'Letter grade created succesfully'
            ], 400);
        }
        return response()->json([
            'status' => 'ok',
            'message' => 'Letter grade data fetched succefully',
            'letter_grades' => $grades_data
        ], 200);
    }

    public function delete_letter_grade(Request $request, $letter_grade_id){
        $find_letter_grade = LetterGrade::find($letter_grade_id);
        if(!$find_letter_grade){
            return response()->json([
                'status' => 'error',
                'message' => 'Letter grade not found'
            ], 404);
        }

        $find_letter_grade->delete();

        return response()->json([
            'status' => 'error',
            'message' => 'Letter grade deleted succesfully',
            'deleted_letter_grade' => $find_letter_grade
        ], 200);
    }

    public function update_letter_grade(Request $request, $letter_grade_id){
        $find_letter_grade = LetterGrade::find($letter_grade_id);
        if(!$find_letter_grade){
            return response()->json([
                'status' => 'error',
                'message' => 'Letter grade not found'
            ], 404);
        }

        $fillable_data = $request->all();
        $filtered_data = array_filter($fillable_data);
        $find_letter_grade->fill($fillable_data);

        $find_letter_grade->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Letter grade updated succefully',
            'updated_letter_grade' => $find_letter_grade
        ], 200);
    }
    
}
