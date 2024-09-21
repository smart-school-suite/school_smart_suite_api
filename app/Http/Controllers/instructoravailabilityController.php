<?php

namespace App\Http\Controllers;
use App\Models\InstructorAvailability;
use Illuminate\Http\Request;

class instructoravailabilityController extends Controller
{
    //
    public function create_availability(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $request->validate([
           'teacher_id' => 'required|string',
           'day_of_week' => 'required|string',
           'specialty_id' => 'required|string',
           'level_id' => 'required|string',
           'semester_id' => 'required|string',
           'start_time' => 'required|date_format:H:i',
           'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $new_availability_instance = new  InstructorAvailability();

        $new_availability_instance->school_branch_id = $currentSchool->id;
        $new_availability_instance->teacher_id = $request->teacher_id;
        $new_availability_instance->day_of_week = $request->day_of_week;
        $new_availability_instance->start_time = $request->start_time;
        $new_availability_instance->end_time = $request->end_time;
        $new_availability_instance->level_id = $request->level_id;
        $new_availability_instance->semester_id = $request->semester_id;
        $new_availability_instance->specialty_id = $request->specialty_id;

        $clashExists = InstructorAvailability::where('school_branch_id', $currentSchool->id) // Scope to current school
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

    if ($clashExists) {
        return response()->json([
            'status' => 'error',
            'message' => 'Time slot clash with existing timetable entries'
        ], 409);
    }

        $new_availability_instance->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'instructor availability created succesfully'
        ], 200);

    }

    public function get_all_teacher_avialability(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $teacher_availability_data = InstructorAvailability::Where('school_branch_id', $currentSchool->id)->get();
        if($teacher_availability_data->isEmpty()){
            return response()->json([
                'status' => 'ok',
                'message' => 'no teacher records found'
            ], 400);
        }
        return response()->json([
            'status' => 'ok',
            'message' => 'teacher availability data fetched successfully',
            'teacher_avialability' => $teacher_availability_data
        ], 200);
    }

    public function get_all_avialability_not_scoped(Request $request, $teacher_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $teacher_availability_data = InstructorAvailability::Where('school_branch_id', $currentSchool->id)->
        Where('teacher_id', $teacher_id)->get();
        if($teacher_availability_data->isEmpty()){
            return response()->json([
                'status' => 'error',
                'message' => 'No records found'
            ], 400);
        }
        return response()->json([
            'status' => 'ok',
            'message' => 'Teacher records fetched sucessfully',
            'teacher_avialability' => $teacher_availability_data
        ], 200);
    }

    public function delete_teacher_avialability(Request $request, $availabilty_id){
        $availabilty = InstructorAvailability::find($availabilty_id);
        if(!$availabilty){
            return response()->json([
                'status' => 'error',
                'message' => 'Teacher with this availability not found'
            ], 404);
        }

        $availabilty->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'Teachers availability deleted succefully',
            'deleted_teacher_avialability' => $availabilty
        ], 200);
    }

    public function update_teacher_avialability(Request $request,  $availabilty_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $timetable = InstructorAvailability::where('school_branch_id', $currentSchool->id)->find($availabilty_id);
        if(!$timetable){
            return response()->json([
                'status' => 'error',
                'message' => 'Teacher with this availability not found'
            ], 404);
        }

        $clashExists = InstructorAvailability::where('school_branch_id', $currentSchool->id)
        ->where('instructor_id', $request->teacher_id ?? $timetable->teacher_id)
        ->where('degree_level', $request->degree_level ?? $timetable->degree_level)
        ->where('semester', $request->semester ?? $timetable->semester)
        ->where('day_of_week', $request->day_of_week ?? $timetable->day_of_week)
        ->where(function ($query) use ($request, $timetable) {
            $query->whereBetween('start_time', [$request->start_time ?? $timetable->start_time, $request->end_time ?? $timetable->end_time])
                ->orWhereBetween('end_time', [$request->start_time ?? $timetable->start_time, $request->end_time ?? $timetable->end_time])
                ->orWhere(function ($query) use ($request, $timetable) {
                    $query->where('start_time', '<=', $request->start_time ?? $timetable->start_time)
                        ->where('end_time', '>=', $request->end_time ?? $timetable->end_time);
                });
        })
        ->where('id', '!=', $availabilty_id)
        ->exists();

    if ($clashExists) {
        return response()->json([
            'status' => 'error',
            'message' => 'Time slot clash with existing timetable entries'
        ], 409);
    }

        $availabilty_data = $request->all();
        $availabilty_data = array_filter($availabilty_data);
        $timetable->fill();

        return response()->json([
            'status' => 'ok',
            'message' => 'Availability updated succesfully'
        ], 200);
    }

    public function delete_scoped_teacher_availability(Request $request, $availabilty_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $availabilty_data = InstructorAvailability::where('school_branch_id', $currentSchool->id)->find($availabilty_id);
        if(!$availabilty_data){
            return response()->json([
                'status' => 'error',
                'message' => 'Aviability details not found'
            ], 404);
        }
        $availabilty_data->delete();
        return response()->json([
            'status' => 'error',
            'message' => 'Deleted succefully'
        ], 200);
    }

    
}
