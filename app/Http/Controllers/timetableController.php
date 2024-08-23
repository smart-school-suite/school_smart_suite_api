<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstructorAvailability;
use App\Models\Timetable;

class timetableController extends Controller
{
    //
    public function create_time_slots_scoped(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $request->validate([
            'teacher_id' => 'required|string',
            'day_of_week' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'specialty_id' => 'required|string',
            'level_id' => 'required|string',
            'semester_id' => 'required|string',
            'end_time' => 'required|date_format:H:i|after:start_time',
         ]);
        
        $time_table_data = new Timetable();

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
           $time_table_data->school_branch_id = $currentSchool->id;
           $time_table_data->teacher_id = $request->teacher_id;
           $time_table_data->day_of_week = $request->day_of_week;
           $time_table_data->specialty_id = $request->specialty_id;
           $time_table_data->level_id = $request->level_id;
           $time_table_data->semester_id = $request->semester_id;
           $time_table_data->start_time = $request->start_time;
           $time_table_data->end_time = $request->end_time;

           $time_table_data->save();

           return response()->json(['message' => 'Entry created succesfully'], 200);
      }
    else{
        return response()->json(['error' => 'Teacher not available within this time slot'], 409);   
     }
      
    }
   

    public function delete_timetable_scoped(Request $request, $timetable_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $time_table = Timetable::Where('school_id', $currentSchool->id)->find($timetable_id);
        if(!$time_table){
            return response()->json(['message' => 'Entry not created'], 404);
        }

        $time_table->delete();

        return response()->json(['message' => 'Entry deleted sucessfully'], 200);
    }


    public function update_time_table_record_scoped(Request $request, $timetable_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $time_table = Timetable::where('school_id', $currentSchool->id)->find($timetable_id);
        if(!$time_table){
            return response()->json(['message' => 'Entry not found'], 200);
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
          $time_table->fill();

          return response()->json(['message' => 'Entry created succesfully'], 200);
     }
   else{
       return response()->json(['error' => 'Teacher not available within this time slot'], 409);   
    }

    }


    public function generate_time_table_scoped(Request $request, $specailty_id, $level_id){
            // Get current school from request attributes
    $currentSchool = $request->attributes->get('currentSchool');

    // Fetch timetable entries for the current school branch
    $timetables = Timetable::where('school_branch_id', $currentSchool->id)
        ->where('specialty_id', $specailty_id) 
        ->where('level_id', $level_id)
        ->with(['course', 'teacher']) // Eager load related course and teacher
        ->get();

    // Initialize the timetable structure
    $time_table = [
        "monday" => [],
        "tuesday" => [],
        "wednesday" => [],
        "thursday" => [],
        "friday" => [],
        "saturday" => [],
        "sunday" => []
    ];

    // Loop through the timetable entries and fill the structured array
    foreach ($timetables as $entry) {
        $day = strtolower($entry->day_of_week); // Convert day of the week to lowercase

        if (array_key_exists($day, $time_table)) { // Ensure it's a valid day
            $time_table[$day][] = [
                "course" => $entry->course->name, // Assuming the course model has a 'name' field
                "start_time" => $entry->start_time->format('g:i A'), // Formatting the time
                "end_time" => $entry->end_time->format('g:i A'), // Formatting the time
                "teacher" => $entry->teacher->name // Access the teacher's name directly
            ];
        }
    }

    return response()->json($time_table);
    }
}
