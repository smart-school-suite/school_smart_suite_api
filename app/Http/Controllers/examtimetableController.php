<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Examtimetable;
use Illuminate\Http\Request;

class examtimetableController extends Controller
{
    //
    public function create_exam_timetable(Request $request)
    {
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
            ->where(function ($query) use ($startTime, $endTime) {
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

    public function delete_exam_time_table_scoped(Request $request, $exam_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $exam_data_entry = Examtimetable::Where('school_branch_id', $currentSchool->id)->find($exam_id);
        if (!$exam_data_entry) {
            return response()->json(['message' => 'Exam not found'], 409);
        }

        $exam_data_entry->delete();

        return response()->json(['message' => 'exam entry deleted sucessfully'], 200);
    }


    public function update_exam_time_table_scoped(Request $request, $exam_id)
    {
        // Retrieve the current school from request attributes
        $currentSchool = $request->attributes->get('currentSchool');

        // Find the exam timetable entry for the given exam_id in the current school
        $exam_data_entry = ExamTimetable::where('school_branch_id', $currentSchool->id)->find($exam_id);

        // Check if the exam data entry exists
        if (!$exam_data_entry) {
            return response()->json(['message' => 'Exam not found'], 409);
        }

        // Validate the incoming request data
        $request->validate([
            'course_id' => 'sometimes|exists:courses,id',
            'exam_id' => 'sometimes|exists:exams,id',
            'specialty_id' => 'sometimes|exists:specialties,id',
            'level_id' => 'sometimes|exists:educationlevels,id', // Assuming level_id is also an attribute
            'day' => 'sometimes|string', // Validate day as a string (modify as needed)
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
        ]);

        // Only calculate start and end times if they are provided
        $startTime = null;
        $endTime = null;
        if ($request->has('start_time') && $request->has('end_time')) {
            $startTime = \Carbon\Carbon::parse($request->start_time);
            $endTime = \Carbon\Carbon::parse($request->end_time);
        }

        // Check for overlapping timetables
        $overlappingTimetables = ExamTimetable::where('school_branch_id', $currentSchool->id)
            ->where('course_id', $request->course_id)
            ->where('specialty_id', $request->specialty_id)
            ->where('level_id', $request->level_id)
            ->where('day', $request->day)
            ->where(function ($query) use ($startTime, $endTime) {
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

        // Get all request data and filter out any empty values
        $exam_timetable_data = array_filter($request->all());

        // Update start_time and end_time to calculate new duration if they are provided
        if (isset($startTime) && isset($endTime)) {
            $exam_timetable_data['duration'] = $startTime->diffInMinutes($endTime);
        }

        // Fill the existing exam data entry with new data
        $exam_data_entry->fill($exam_timetable_data);
        $exam_data_entry->save(); // Don't forget to save the changes!

        return response()->json(['message' => 'Exam timetable updated successfully'], 200);
    }




    public function generate_time_table_for_specialty(Request $request, $specialty_id, $level_id)
    {

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
