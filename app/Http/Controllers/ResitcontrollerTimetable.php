<?php

namespace App\Http\Controllers;

use App\Models\Resitablecourses;
use Carbon\Carbon;
use App\Models\Examtimetable;
use App\Models\Resitexamtimetable;
use App\Models\Specialty;
use Illuminate\Http\Request;

class ResitcontrollerTimetable extends Controller
{
    //
    public function get_resits_for_specialty(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $specialty_id = $request->route('specialty_id');
        $exam_id = $request->route('exam_id');

        $find_specialty = Specialty::where('school_branch_id', $currentSchool->id)
                                    ->find($specialty_id);

        if (!$find_specialty) {
            return response()->json([
                'status' => 'error',
                'message' => 'Specialty not found'
            ], 404);
        }

        $resitable_courses =
        Resitablecourses::where('school_branch_id', $currentSchool->id)
                                             ->where('exam_id', $exam_id)
                                             ->where('specialty_id', $specialty_id)
                                             ->with(['courses'])
                                             ->get();

        if ($resitable_courses->isEmpty()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'It appears there are no resits.'
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Resitable courses fetched successfully.',
            'courses' => $resitable_courses
        ], 200); // Use 200 for successful requests
    }

    public function create_resit_timetable_entry(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'exam_id' => 'required|exists:exams,id',
            'specialty_id' => 'required|exists:specialty,id',
            'start_time' => 'required|date',
            'level_id' => 'required|exists:educationlevels,id',
            'date' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $find_resitable_courses = Resitablecourses::where('school_branch_id', $currentSchool->id)->find($request->course_id);

        if(!$find_resitable_courses){
            return response()->json([
                'status' => 'ok',
                'message' => 'No student failed this course'
            ], 409);
        }

        $startTime = Carbon::parse($request->start_time);
        $endTime = Carbon::parse($request->end_time);
        $duration = $startTime->diffInMinutes($endTime);
        $durationString = gmdate("H:i:s", $duration);

        $overlappingTimetables = ExamTimetable::where('school_branch_id', $currentSchool->id)
            ->Where('course_id', $request->course_id)
            ->where('specialty_id', $request->specialty_id)
            ->where('level_id', $request->level_id)
            ->where('day', $request->day)
            ->where('exam_id', $request->exam_id)
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
            return response()->json([
                'status' => 'ok',
                'message' => 'The timetable overlaps with an existing course. Please choose a different time.'
            ], 409);
        }

        // Create the exam timetable entry
        $timetable = Resitexamtimetable::create([
            'course_id' => $request->course_id,
            'exam_id' => $request->exam_id,
            'level_id' => $request->level_id,
            'school_branch_id' => $currentSchool->id,
            'specialty_id' => $request->specialty_id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration' => $durationString, // Use the calculated duration
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Exam timetable created successfully!',
            'timetable' => $timetable
        ], 201);
    }

    public function generate_resit_timetable(Request $request, $specialty_id, $level_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $timetables = Resitexamtimetable::Where('school_branch_id', $currentSchool->id)
            ->where('level_id', $level_id)
            ->where('specialty_id', $specialty_id)
            ->with('course')
            ->orderBy('day')
            ->get();

        $examTimetable = [];

        foreach ($timetables as $timetable) {
            if (!isset($examTimetable[$timetable->day])) {
                $examTimetable[$timetable->day] = [];
            }
            $examTimetable[$timetable->day][] = [
                'course_title' => $timetable->course->course_title,
                'credit' => $timetable->course->credit,
                'course_code' => $timetable->course->course_code,
                'start_time' => $timetable->start_time->format('H:i'),
                'end_time' => $timetable->end_time->format('H:i'),
                'duration' => $timetable->duration,
            ];
        }

        $examTimetable = array_change_key_case($examTimetable, CASE_LOWER);

        return response()->json([
            'status' => 'ok',
            'message' => 'exam time table generated succefully',
            'resit_timetable' => $examTimetable
        ]);
    }

}
