<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstructorAvailability;
use Carbon\Carbon;
use App\Models\Timetable;

class timetableController extends Controller
{
    //
    public function create_time_slots_scoped(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $request->validate([
            'teacher_id' => 'required|string',
            'course_id' => 'required|exists:courses,id',
            'day_of_week' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'specialty_id' => 'required|string',
            'level_id' => 'required|string',
            'semester_id' => 'required|string',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $time_table_data = new Timetable();

        $check_if_teacher_avialable = InstructorAvailability::where('school_branch_id', $currentSchool->id) // Scope to current school
            ->where('teacher_id', $request->teacher_id)
            ->where('level_id', $request->level_id)
            ->where('semester_id', $request->semester_id)
            ->where('specialty_id', $request->specialty_id)
            ->where('day_of_week', $request->day_of_week)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                    ->orWhere(function ($query) use ($request) {
                        $query->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                    });
            })
            ->exists();

        $check_if_teacher_already_available_on_this_time = Timetable::where('school_branch_id', $currentSchool->id) // Scope to current school
        ->where('teacher_id', $request->teacher_id)
        ->where('level_id', $request->level_id)
        ->where('semester_id', $request->semester_id)
        ->where('specialty_id', $request->specialty_id)
        ->where('day_of_week', $request->day_of_week)
        ->where(function ($query) use ($request) {
            $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                ->orWhere(function ($query) use ($request) {
                    $query->where('start_time', '<=', $request->start_time)
                        ->where('end_time', '>=', $request->end_time);
                });
        })
        ->exists();

        if(!$check_if_teacher_avialable){
            return response()->json([
                'status' => 'ok',
                'message' => 'Teacher is not available at this time',
                'data' => $check_if_teacher_avialable
            ], 200);
        }

        if($check_if_teacher_already_available_on_this_time){
            return response()->json([
                'status' => 'ok',
                'message' => 'Teacher is already assign to this time',
                'data' => $check_if_teacher_already_available_on_this_time
            ], 200);
        }
        
        if($check_if_teacher_avialable && !$check_if_teacher_already_available_on_this_time){


            $time_table_data->school_branch_id = $currentSchool->id;
            $time_table_data->course_id = $request->course_id;
            $time_table_data->teacher_id = $request->teacher_id;
            $time_table_data->day_of_week = $request->day_of_week;
            $time_table_data->specialty_id = $request->specialty_id;
            $time_table_data->level_id = $request->level_id;
            $time_table_data->semester_id = $request->semester_id;
            $time_table_data->start_time = $request->start_time;
            $time_table_data->end_time = $request->end_time;

            $time_table_data->save();

            return response()->json([
                'status' => 'ok',
                'message' => 'Entry created succesfully',
                'created_timetable_entry' => $time_table_data
            ], 200);
        }

        
    }


    public function delete_timetable_scoped(Request $request, $timetable_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $time_table = Timetable::Where('school_id', $currentSchool->id)->find($timetable_id);
        if (!$time_table) {
            return response()->json([
                'status' => 'error',
                'message' => 'Entry not created'
            ], 409);
        }

        $time_table->delete();

        return response()->json([
             'status' => 'ok',
             'message' => 'Entry deleted sucessfully',
             'deleted_timetable' => $time_table
        ], 200);
    }


    public function update_time_table_record_scoped(Request $request, $timetable_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $time_table = Timetable::where('school_id', $currentSchool->id)->find($timetable_id);
        if (!$time_table) {
            return response()->json([
                'status' => 'error',
                'message' => 'Entry not found'
            ], 409);
        }

        $clashExists = InstructorAvailability::where('school_id', $currentSchool->id) // Scope to current school
            ->where('teacher_id', $request->teacher_id)
            ->where('level_id', $request->level_id)
            ->where('semester_id', $request->semester_id)
            ->where('specialty_id', $request->specialty_id)
            ->where('day_of_week', $request->day_of_week)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                    ->orWhere(function ($query) use ($request) {
                        $query->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                    });
            })
            ->exists();

        if (!$clashExists) {
            $entry_data = $request->all();
            $entry_data = array_filter($entry_data);
            $time_table->fill($entry_data);
            $time_table->save();
            return response()->json([
                'status' => 'ok',
                'message' => 'Entry created succesfully',
                'updated_timetable' => $clashExists
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Teacher not available within this time slot',
                'clashes' => $clashExists,
            ], 409);
        }
    }


    public function generate_time_table_scoped(Request $request, $specailty_id, $level_id)
    {
        $specailty_id = $request->route('specailty_id');
        $level_id = $request->route('level_id');
        $currentSchool = $request->attributes->get('currentSchool');


        $timetables = Timetable::where('school_branch_id', $currentSchool->id)
            ->where('specialty_id', $specailty_id)
            ->where('level_id', $level_id)
            ->with(['course', 'teacher']) 
            ->get();

        if($timetables->isEmpty()){
            return response()->json([
                'status' => 'error',
                'message' => 'Timetable records seem to be empty'
            ], 409);
        }
        $time_table = [
            "monday" => [],
            "tuesday" => [],
            "wednesday" => [],
            "thursday" => [],
            "friday" => []
        ];
        foreach ($timetables as $entry) {
            $day = strtolower($entry->day_of_week);

            if (array_key_exists($day, $time_table)) {
                $time_table[$day][] = [
                    "course" => $entry->course->course_title,
                    "start_time" => Carbon::parse($entry->start_time)->format('g:i A'),
                    "end_time" => Carbon::parse($entry->end_time)->format('g:i A'),
                    "teacher" => $entry->teacher->name
                ];
            }
        }

        return response()->json([
             'status' => 'ok',
             'message' => 'Time table fetched succefully',
             'timetable' => $time_table
        ], 200);
    }

    public function get_timetable_details(Request $request){
         $currentSchool = $request->attributes->get("currentSchool");
         $entry_id =  $request->route("entry_id");
         $find_timetable_entry = Timetable::find($entry_id);
         if(!$find_timetable_entry){
            return response()->json([
                 "status" => "error",
                 "message" => "Timetable entry not found"
            ], 400);
         }

         $timetable_details = Timetable::where("school_branch_id", $currentSchool->id)
                                         ->where("id", $entry_id)
                                         ->with([''])
                                         ->get();
        return response()->json([
             "status" => "ok",
             "message" => "Time table details fetched sucessfully",
             "timetable_details" => $timetable_details
        ], 200);

    }
}
