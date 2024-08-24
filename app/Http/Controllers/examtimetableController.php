<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Examtimetable;
use Illuminate\Http\Request;

class examtimetableController extends Controller
{
    //
    public function create_exam_timetable(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'exam_id' => 'required|exists:exams,id',
            'specialty_id' => 'required|exists:specialty,id',
            'start_time' => 'required|date',
            'level_id' => 'required|exists:educationlevels,id',
            'day' => 'required|string',
            'end_time' => 'required|date|after:start_time',
        ]);
        
        $startTime = Carbon::parse($request->start_time);
        $endTime = Carbon::parse($request->end_time);
        $duration = $startTime->diffInMinutes($endTime);
        $durationString = gmdate("H:i:s", $duration);
        
        $overlappingTimetables = ExamTimetable::where('school_branch_id', $currentSchool->id)
            ->Where('course_id', $request->course_id)
            ->where('specialty_id', $request->specialty_id)
            ->where('level_id', $request->level_id)
            ->where('day', $request->day)
            ->where(function($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                      ->orWhereBetween('end_time', [$startTime, $endTime])
                      ->orWhere(function ($query) use ($startTime, $endTime) {
                          $query->where('start_time', '<=', $startTime)
                                ->where('end_time', '>=', $endTime);
                      });
            })
            ->exists();

        if ($overlappingTimetables) {
            return response()->json(['message' => 'The timetable overlaps with an existing course. Please choose a different time.'], 409);
        }

        // Create the exam timetable entry
        $timetable = ExamTimetable::create([
            'course_id' => $request->course_id,
            'exam_id' => $request->exam_id,
            'level_id' => $request->level_id,
            'school_branch_id' => $currentSchool->id,
            'specialty_id' => $request->specialty_id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration' => $durationString, // Use the calculated duration
        ]);

        return response()->json(['message' => 'Exam timetable created successfully!', 'timetable' => $timetable], 201);
         
    }

    public function delete_exam_time_table_scoped(Request $request, $exam_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $exam_data_entry = Examtimetable::Where('school_branch_id', $currentSchool->id)->find($exam_id);
        if(!$exam_data_entry){
            return response()->json(['message' => 'Exam not found'], 409);
        }

        $exam_data_entry->delete();

        return response()->json(['message' => 'exam entry deleted sucessfully'], 200);
    }

    public function update_exam_time_table_scoped(Request $request, $exam_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $exam_data_entry = Examtimetable::Where('school_branch_id', $currentSchool->id)->find($exam_id);
        if(!$exam_data_entry){
            return response()->json(['message' => 'Exam not found'], 409);
        }

        
    }


    public function generate_time_table_for_specialty(Request $request, $specialty_id, $level_id){

        $currentSchool = $request->attributes->get('currentSchool');
         // Fetch the exam timetable entries for the given specialty_id
         $timetables = ExamTimetable::Where('school_branch_id', $currentSchool->id)
         ->where('level_id', $level_id)
         ->where('specialty_id', $specialty_id)
         ->with('course')
         ->orderBy('day')
         ->get();

     $examTimetable = [];

     // Process the retrieved timetables and organize them by day
     foreach ($timetables as $timetable) {
         // Ensure the day exists in the response structure
         if (!isset($examTimetable[$timetable->day])) {
             $examTimetable[$timetable->day] = [];
         }

         // Create an entry for the course
         $examTimetable[$timetable->day][] = [
             'course_title' => $timetable->course->course_title, // Assuming course relationship is defined
             'credit' => $timetable->course->credit, // Assuming course has a credit property
             'course_code' => $timetable->course->course_code,
             'start_time' => $timetable->start_time->format('H:i'), // Format start time
             'end_time' => $timetable->end_time->format('H:i'), // Format end time
             'duration' => $timetable->duration, // Duration as a string
         ];
     }

     // Format the keys of the resulting array to lowercase
     $examTimetable = array_change_key_case($examTimetable, CASE_LOWER);

     return response()->json(['exam_timetable' => $examTimetable]);
    }


}
