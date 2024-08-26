<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use Illuminate\Http\Request;

class semesterController extends Controller
{
    //
    public function create_semester(Request $request){
        $request->validate([
            'name' => 'string|required'
        ]);

        $new_semster_instance = new Semester();
        $new_semster_instance->name = $request->new;
        $new_semster_instance->save();

    }

    public function delete_semester(Request $request, $semester_id){
        $semester = Semester::find($semester_id);
        if(!$semester){
            return response()->json(['message' => 'Semester not found']);
        }

        $semester->delete();
    }

    public function update_semester(Request $request, $semester_id){
        $semester = Semester::find($semester_id);
        if(!$semester){
            return response()->json(['message' => 'Semester not found']);
        }

        $semester_data = $request->all();
        $semester_data = array_filter($semester_data);
        $semester->fill($semester_data);

        $semester->save();

        return response()->json(['message' => 'semester updated succesully'], 200);
    }

    public function get_all_semesters(Request $request){
        $semester_data = Semester::all();
        return response()->json(['semester_data' => $semester_data], 200);
    }
}
