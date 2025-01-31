<?php

namespace App\Http\Controllers;

use App\Models\Courses;
use App\Models\Educationlevels;
use App\Models\Exams;
use Carbon\Carbon;
use App\Models\Examtimetable;
use App\Models\Semester;
use Illuminate\Http\Request;

class examtimetableController extends Controller
{

public function create_exam_timetable(Request $request)
{
    $currentSchool = $request->attributes->get('currentSchool');

    $request->validate([
        'exam_courses' => 'required|array',
        'exam_courses.*.course_id' => 'required|exists:courses,id',
        'exam_courses.*.exam_id' => 'required|exists:exams,id',
        'exam_courses.*.specialty_id' => 'required|exists:specialty,id',
        'exam_courses.*.start_time' => 'required|date_format:H:i', // Validating time format
        'exam_courses.*.level_id' => 'required|exists:education_levels,id',
        'exam_courses.*.day' => 'required|date',
        'exam_courses.*.end_time' => 'required|date_format:H:i|after:exam_courses.*.start_time',
    ]);

    $createdTimetables = []; // Array to hold created timetables

    foreach ($request->exam_courses as $examCourse) {
        // Parse start and end times
        $startTime = Carbon::parse($examCourse['start_time']);
        $endTime = Carbon::parse($examCourse['end_time']);
        $duration = $startTime->diffInMinutes($endTime);
        $durationString = gmdate("H:i:s", $duration);

        // Check for overlapping timetables
        $overlappingTimetables = ExamTimetable::where('school_branch_id', $currentSchool->id)
            ->where('course_id', $examCourse['course_id'])
            ->where('specialty_id', $examCourse['specialty_id'])
            ->where('level_id', $examCourse['level_id'])
            ->where('day', $examCourse['day'])
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                      ->orWhereBetween('end_time', [$startTime, $endTime])
                      ->orWhere(function ($query) use ($startTime, $endTime) {
                          $query->where('start_time', '<=', $startTime)
                                ->where('end_time', '>=', $endTime);
                      });
            })
            ->get();

        if ($overlappingTimetables->isNotEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'The timetable overlaps with existing courses. Please choose a different time.',
                'overlapping_schedule' => $overlappingTimetables
            ], 409);
        }

        // Create the timetable entry
        $timetable = ExamTimetable::create([
            'course_id' => $examCourse['course_id'],
            'exam_id' => $examCourse['exam_id'],
            'level_id' => $examCourse['level_id'],
            'day' => $examCourse['day'],
            'school_branch_id' => $currentSchool->id,
            'specialty_id' => $examCourse['specialty_id'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration' => $durationString,
        ]);

        $createdTimetables[] = $timetable; // Save the created timetable in the array
    }

    return response()->json([
        'status' => 'ok',
        'message' => 'Exam timetable(s) created successfully!',
        'timetables' => $createdTimetables
    ], 201);
}

    public function delete_exam_time_table_scoped(Request $request, $examtimetable_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $exam_data_entry = Examtimetable::Where('school_branch_id', $currentSchool->id)->find($examtimetable_id);
        if (!$exam_data_entry) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Exam not found'
            ], 409);
        }

        $exam_data_entry->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'exam entry deleted sucessfully',
            'delete_timetable' => $exam_data_entry
        ], 200);
    }


    public function update_exam_time_table_scoped(Request $request, $examtimetable_id)
    {

        $currentSchool = $request->attributes->get('currentSchool');

        $exam_data_entry = ExamTimetable::where('school_branch_id', $currentSchool->id)->find($examtimetable_id);

        if (!$exam_data_entry) {
            return response()->json(['message' => 'Exam not found'], 409);
        }

        $request->validate([
            'course_id' => 'sometimes|exists:courses,id',
            'exam_id' => 'sometimes|exists:exams,id',
            'specialty_id' => 'sometimes|exists:specialties,id',
            'level_id' => 'sometimes|exists:educationlevels,id',
            'day' => 'sometimes|string',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
        ]);

        $startTime = null;
        $endTime = null;
        if ($request->has('start_time') && $request->has('end_time')) {
            $startTime = \Carbon\Carbon::parse($request->start_time);
            $endTime = \Carbon\Carbon::parse($request->end_time);
        }

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
            return response()->json([
                'status' => 'ok',
                'message' => 'The timetable overlaps with an existing course. Please choose a different time.'
            ], 409);
        }

        $exam_timetable_data = array_filter($request->all());

        if (isset($startTime) && isset($endTime)) {
            $exam_timetable_data['duration'] = $startTime->diffInMinutes($endTime);
        }

        $exam_data_entry->fill($exam_timetable_data);
        $exam_data_entry->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Exam timetable updated successfully'
        ], 200);
    }




    public function generate_time_table_for_specialty(Request $request, $specialty_id, $level_id)
    {

        $currentSchool = $request->attributes->get('currentSchool');
        $timetables = ExamTimetable::Where('school_branch_id', $currentSchool->id)
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
            'exam_timetable' => $examTimetable
        ]);
    }

    public function prepareExamTimeTableData(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $exam_id = $request->route("exam_id");

        $find_exam_id = Exams::with(["semester", "specialty", "level"])
                              ->where("id", $exam_id)
                              ->first();

        if (!$find_exam_id) {
            return response()->json([
                'status' => "error",
                "message" => "Exam not found"
            ]);
        }

        $courses_data = Courses::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $find_exam_id->specialty->id)
            ->get();

        $results = [];
        $level_id = $find_exam_id->level->id;
        $specialty_id = $find_exam_id->specialty->id;

        foreach ($courses_data as $course) {
            $results[] = [
                'course_id' => $course->id,
                'course_name' => $course->course_title,
                'level_id' => $level_id,
                'specialty_id' => $specialty_id,
                'exam_id' => $exam_id
            ];
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Course data fetched successfully',
            'associated_courses' => $results
        ], 200);
    }
}
