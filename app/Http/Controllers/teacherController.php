<?php

namespace App\Http\Controllers;
use App\Models\Teacher;
use Illuminate\Http\Request;

class teacherController extends Controller
{
    //

    public function get_all_teachers_Without_relations(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $teachers = Teacher::Where('school_id', $currentSchool->id)->get();
        return response()->json(['teachers' => $teachers], 200);
    }


}
