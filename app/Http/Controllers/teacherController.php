<?php

namespace App\Http\Controllers;
use App\Models\Teacher;
use Carbon\Carbon;
use App\Models\Timetable;
use Illuminate\Http\Request;

class teacherController extends Controller
{
    //

    public function get_all_teachers_Without_relations(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $teachers = Teacher::Where('school_branch_id', $currentSchool->id)->get();
        if($teachers->isEmpty()){
            return response()->json([
                'status' => 'error',
                'message' => 'Teacher records seem to be empty'
            ]);
        }
        return response()->json([
            'status' => 'ok',
            'message' => 'teachers fetched succefully',
            'teachers' => $teachers
        ], 200);
    }

    public function get_all_teachers_with_relations_scoped(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $teachers = Teacher::where('school_branch_id', $currentSchool->id)
        ->with('courses', 'instructoravailability');
          
        return response()->json(['teacher_data' => $teachers], 201);
    }

    public function delete_teacher_scoped(Request $request, $teacher_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $teacher_data = Teacher::Where('school_branch_id', $currentSchool->id)->find($teacher_id);
        if(!$teacher_id){
            return response()->json([
                'status' => 'error',
                'message' => 'Teacher Not found'
            ], 409);
        }
        
        $teacher_data->delete();

        return response()->json([
            'status' => 'ok',
            'deleted_teacher' => $teacher_data,
            'message' => 'Teacher deleted sucessfully'
        ], 200);
    }

    public function update_teacher_data_scoped(Request $request, $teacher_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $teacher_data = Teacher::Where('school_branch_id', $currentSchool->id)->find($teacher_id);
        if(!$teacher_data){
            return response()->json([
                'status' => 'error',
                'message' => 'Teacher deleted succefully'
            ], 409);
        }
        
        $teacher_data_request = $request->all();
        $teacher_data_request = array_filter($teacher_data_request);
        $teacher_data->fill();
        $teacher_data->save();

        return response()->json([
            'status' => 'ok',
            'updated_teacher' => $teacher_data,
            'message' => 'Teacher updated sucessfully'
        ], 201);
    }
    public function get_all_teachers_not_scoped(Request $request){
          $teacher_data = teacher::all();
          return response()->json(['teacher_data' => $teacher_data ], 201);
    }


    public function get_my_timetable(Request $request, $teacher_id){
           $currentSchool = $request->attributes->get('currentSchool');
           $teacher_id = $request->route('teacher_id');

           $teacher_timetable_data = Timetable::where('school_branch_id', $currentSchool->id)
           ->where('teacher_id', $teacher_id)
           ->with(['specialty', 'course', 'level'])
           ->get();

           if($teacher_timetable_data->isEmpty()){
              return response()->json([
                 'status' => 'error',
                 'message' => 'No records found'
              ], 409);
           }
        
           //return response()->json($teacher_timetable_data);
           $time_table = [
            "monday" => [],
            "tuesday" => [],
            "wednesday" => [],
            "thursday" => [],
            "friday" => []
        ];

        foreach ($teacher_timetable_data as $entry) {
            $day = strtolower($entry->day_of_week);

            if (array_key_exists($day, $time_table)) {
                $time_table[$day][] = [
                    'level_name' => $entry->level->name,
                    'level' => $entry->level->level,
                    "specialty" => $entry->specialty->specialty_name,
                    "course" => $entry->course->course_title,
                    "start_time" => Carbon::parse($entry->start_time)->format('g:i A'),
                    "end_time" => Carbon::parse($entry->end_time)->format('g:i A'),
                    "teacher" => $entry->teacher->name
                ];
            }
        }
        return response()->json([
             'status' => 'ok',
             'message' => 'Teacher time table fetched succefully',
             'teacher_timetable' => $time_table
        ], 200);
    } 
    


}
