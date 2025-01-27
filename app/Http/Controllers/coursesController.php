<?php

namespace App\Http\Controllers;

use App\Models\Courses;
use App\Models\Specialty;
use Illuminate\Http\Request;

class coursesController extends Controller
{
      //
      public function create_course(Request $request)
      {
            $currentSchool = $request->attributes->get('currentSchool');
            $request->validate([
                  'course_code' => 'required|string',
                  'course_title' => 'required|string',
                  'specialty_id' => 'required|string',
                  'department_id' => 'required|string',
                  'credit' => 'required|integer',
                  'semester_id' => 'required|string',
                  'level_id' => 'required|string'
            ]);

            $course = new Courses();
            $course->course_code = $request->course_code;
            $course->course_title = $request->course_title;
            $course->specialty_id = $request->specialty_id;
            $course->department_id = $request->department_id;
            $course->credit = $request->credit;
            $course->school_branch_id = $currentSchool->id;
            $course->semester_id = $request->semester_id;
            $course->level_id = $request->level_id;

            $course->save();

            return response()->json([
                  'status' => 'ok',
                  'message' => 'course created succesfully',
                   $course,
            ], 200);
      }

      public function delete_course(Request $request, $course_id)
      {
            $currentSchool = $request->attributes->get('currentSchool');
            $course = Courses::Where('school_branch_id', $currentSchool->id)->find($course_id);
            if (!$course) {
                  return response()->json([
                        'status' => 'ok',
                        'message' => 'course not found'
                  ], 404);
            }
            $course->delete();

            return response()->json([
                  'status' => 'ok',
                  'message' => 'course deleted successfully'
            ], 200);
      }

      public function get_all_courses_with_department_specialty(Request $request)
      {
            $currentSchool = $request->attributes->get('currentSchool');
            $course = Courses::where('school_branch_id', $currentSchool->id)->with(['department', 'specialty']);
            return response()->json(['courses' => $course], 200);
      }

      public function get_all_courses_with_no_relation(Request $request)
      {
            $currentSchool = $request->attributes->get('currentSchool');
            $course = Courses::where('school_branch_id', $currentSchool->id)->with(['specialty', 'level', 'semester'])->get();

            return response()->json([
                  'status' => 'ok',
                  'message' => 'fetch succesfull',
                  'courses' => $course
            ], 200);
      }

      public function update_course(Request $request, $course_id)
      {
            $currentSchool = $request->attributes->get('currentSchool');
            $course = Courses::Where('school_branch_id', $currentSchool->id)->find($course_id);
            if (!$course) {
                  return response()->json([
                        'status' => 'ok',
                        'message' => 'course not found'
                  ], 200);
            }

            $course_data = $request->all();
            $course_data = array_filter($course_data);
            $course->fill($course_data);

            $course->save();

            return response()->json([
                  'status' => 'ok',
                  'message' => 'Course updated succefully'
            ], 200);
      }

      public function courses_details(Request $request){
             $currentSchool = $request->attributes->get("currentSchool");
             $course_id = $request->route("course_id");
             $find_course = Courses::find($course_id);
             if(!$find_course){
                  return response()->json([
                         "status" => "error",
                         "message" => "Course not found"
                  ], 200);
             }

             $course_details = Courses::where("school_branch_id", $currentSchool->id)
                                       ->with(['level', 'semester', 'specialty'])
                                       ->where("id", $course_id)
                                        ->get();

            return response()->json([
               "status" => "ok",
               "message" => "Course Details fetched succefully",
               "course_details" => $course_details
            ], 200);
       }

       public function get_specialty_level_semester_courses(Request $request)
       {

           $currentSchool = $request->attributes->get("currentSchool");
           $specialtyId = $request->route("specialty_id");
           $semesterId = $request->route("semester_id");

           if (!$currentSchool || !$specialtyId || !$semesterId) {
               return response()->json([
                   'status' => 'error',
                   'message' => 'Invalid input parameters',
               ], 400);
           }


           $specialty = Specialty::find($specialtyId);
           if (!$specialty) {
               return response()->json([
                   'status' => 'error',
                   'message' => 'Specialty not found',
               ], 404);
           }


           $levelId = $specialty->level->id;

           $coursesData = Courses::where("school_branch_id", $currentSchool->id)
               ->where("semester_id", $semesterId)
               ->where("specialty_id", $specialtyId)
               ->where("level_id", $levelId)
               ->get();


           if (!$coursesData->count()) {
               return response()->json([
                   'status' => 'error',
                   'message' => 'No courses data found',
               ], 404);
           }


           return response()->json([
               'status' => 'ok',
               'message' => 'Courses data fetched successfully',
               'courses_data' => $coursesData,
           ], 200);
       }
}
