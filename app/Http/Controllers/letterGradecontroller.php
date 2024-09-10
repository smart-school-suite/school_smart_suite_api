<?php

namespace App\Http\Controllers;

use App\Models\LetterGrade;
use Illuminate\Http\Request;

class letterGradecontroller extends Controller
{
    //

    public function create_letter_grade(Request $request){
        $request->validate([
            'letter_grade'
        ]);

        $new_letter_grade_instance = new LetterGrade();
        
        $new_letter_grade_instance->letter_grade = $request->letter_grade;

        $new_letter_grade_instance->save();

        return response()->json(['message' => 'Letter grade created succesfully'], 200);
    }

    public function get_all_letter_grades(Request $request){
        $grades_data = LetterGrade::all();

        return response()->json(['letter_grades' => $grades_data], 200);
    }

    public function delete_letter_grade(Request $request, $letter_grade_id){
        $find_letter_grade = LetterGrade::find($letter_grade_id);
        if(!$find_letter_grade){
            return response()->json(['message' => 'Letter grade not found'], 404);
        }

        $find_letter_grade->delete();

        return response()->json(['message' => 'Letter grade deleted succesfully'], 200);
    }

    public function update_letter_grade(Request $request, $letter_grade_id){
        $find_letter_grade = LetterGrade::find($letter_grade_id);
        if(!$find_letter_grade){
            return response()->json(['message' => 'Letter grade not found'], 404);
        }

        $fillable_data = $request->all();
        $filtered_data = array_filter($fillable_data);
        $find_letter_grade->fill($fillable_data);

        $find_letter_grade->save();

        return response()->json(['message' => 'Letter grade updated succefully'], 200);
    }
    
}
