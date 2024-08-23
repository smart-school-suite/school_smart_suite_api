<?php

namespace App\Http\Controllers;
use App\Models\Courses;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isEmpty;

class coursesController extends Controller
{
    //
    public function create_course(Request $request){
          $request->validate([
             'course_code' => 'required|string',
             'course_title' => 'required|string',
             'specialty_id' => 'required|string',
             'department_id' => 'required|string',
             'credit' => 'required|integer',
             'semester' => 'required|string',
             'level' => 'required|string'
          ]);

          $course = new Courses();
          $course->course_code = $request->course_code;
          $course->course_title = $request->course_title;
          $course->specialty_id = $request->specialty_id;
          $course->department_id = $request->department_id;
          $course->credit = $request->credit;
          $course->semester = $request->semester;
          $course->level = $request->level;

          $course->save();

          return response()->json(['message' => 'course created succesfully'], 200);
    }

    public function delete_course(Request $request, $course_id){
            $course = Courses::find($course_id);
            if(!$course){
                return response()->json(['message' => 'course not found'], 404);
            }
            $course->delete();

            return response()->json(['message' => 'course deleted successfully'], 200);
    }

    public function get_all_courses_with_department_specialty(Request $request){
           $course = Courses::with('department', 'specialty');
           return response()->json(['courses' => $course], 200);
    }

    public function get_all_courses_with_no_relation(Request $request){
          $course = Courses::all();
          return response()->json(['courses' => $course], 200);
    }

    public function update_course(Request $request, $course_id){
          $course = Courses::find($course_id);
          if(!$course){
            return response()->json(['message' => 'course not found'], 200);
          }

          $course_data = $request->all();
          $course_data = array_filter($course_data);
          $course->fill($course_data);

          $course->save();

          return response()->json(['message' => 'Course updated succefully'], 200);
    }
}
