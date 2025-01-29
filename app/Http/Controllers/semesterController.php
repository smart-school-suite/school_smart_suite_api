<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use Illuminate\Http\Request;

class semesterController extends Controller
{
    //
    public function create_semester(Request $request){
        $request->validate([
            'name' => 'string|required',
            'program_name' => 'string|required',
            'count' =>  'required|integer'
        ]);

        $new_semster_instance = new Semester();
        $new_semster_instance->name = $request->name;
        $new_semster_instance->program_name = $request->program_name;
        $new_semster_instance->count = $request->count;
        $new_semster_instance->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'semester created succesfully',
            'created_semester' => $new_semster_instance
        ], 200);
    }

    public function delete_semester(Request $request, $semester_id){
        $semester = Semester::find($semester_id);
        if(!$semester){
            return response()->json([
                'status' => 'ok',
                'message' => 'Semester not found'
            ], 409);
        }

        $semester->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'semester deleted successfully',
            'deleted_semester' => $semester
        ], 200);
    }

    public function update_semester(Request $request, $semester_id){
        $semester = Semester::find($semester_id);
        if(!$semester){
            return response()->json([
                'status' => 'ok',
                'message' => 'Semester not found'
            ], 409);
        }

        $semester_data = $request->all();
        $semester_data = array_filter($semester_data);
        $semester->fill($semester_data);

        $semester->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'semester updated succesully',
            'updated_semester' => $semester
        ], 200);
    }

    public function get_all_semesters(Request $request) {

        $semester_data = Semester::all();
        $currentSchool = $request->attributes->get('currentSchool');
        $num_semesters = $currentSchool->semester_count;

        if ($semester_data->isEmpty()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Records seem to be empty'
            ], 409);
        }


        $filtered_semesters = Semester::whereBetween('count', [1, $num_semesters])->get();


        if ($filtered_semesters->isEmpty()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'No semesters found within the specified count range.'
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Records fetched successfully',
            'semester_data' => $filtered_semesters
        ], 200);
    }
}
