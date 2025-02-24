<?php

namespace App\Http\Controllers;

use App\Models\Resitablecourses;
use Carbon\Carbon;
use App\Models\Examtimetable;
use App\Models\Resitexamtimetable;
use App\Models\Specialty;
use App\Services\ApiResponseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ResitTimeTableController extends Controller
{
    //ResitTimeTableController
    //ResitcontrollerTimetable
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

    public function create_resit_timetable_entry(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $request->validate([
            'resit_timetable' => 'required|array',
            'resit_timetable.*.course_id' => 'required|exists:courses,id',
            'resit_timetable.*.exam_id' => 'required|exists:exams,id',
            'resit_timetable.*.specialty_id' => 'required|exists:specialties,id',
            'resit_timetable.*.start_time' => 'required|date',
            'resit_timetable.*.level_id' => 'required|exists:education_levels,id',
            'resit_timetable.*.date' => 'required|date',
            'resit_timetable.*.end_time' => 'required|date|after:start_time',
        ]);

        $errors = [];
        $createdTimetables = [];

        DB::beginTransaction();

        try {
            foreach ($request->resit_timetable as $timetableData) {
                $find_resitable_courses = ResitableCourses::where('school_branch_id', $currentSchool->id)
                    ->where('course_id', $timetableData['course_id'])
                    ->exists();

                if (!$find_resitable_courses) {
                    $errors[] = [
                        'course_id' => $timetableData['course_id'],
                        'message' => 'No student has failed this course.'
                    ];
                    continue;
                }

                $startTime = Carbon::parse($timetableData['start_time']);
                $endTime = Carbon::parse($timetableData['end_time']);
                $duration = $startTime->diffInMinutes($endTime);
                $durationString = gmdate("H:i:s", $duration);

                $overlappingTimetables = ExamTimetable::where('school_branch_id', $currentSchool->id)
                    ->where('course_id', $timetableData['course_id'])
                    ->where('specialty_id', $timetableData['specialty_id'])
                    ->where('level_id', $timetableData['level_id'])
                    ->where('date', $timetableData['date'])
                    ->where('exam_id', $timetableData['exam_id'])
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
                    foreach ($overlappingTimetables as $overlap) {
                        $errors[] = [
                            'course_id' => $timetableData['course_id'],
                            'exam_id' => $timetableData['exam_id'],
                            'overlap_start' => $overlap->start_time,
                            'overlap_end' => $overlap->end_time,
                            'message' => 'The timetable overlaps with an existing entry.'
                        ];
                    }
                    continue;
                }

                $createdEntry = ResitExamTimetable::create([
                    'course_id' => $timetableData['course_id'],
                    'exam_id' => $timetableData['exam_id'],
                    'level_id' => $timetableData['level_id'],
                    'school_branch_id' => $currentSchool->id,
                    'specialty_id' => $timetableData['specialty_id'],
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'duration' => $durationString,
                ]);

                $createdTimetables[] = $createdEntry;
            }

            if (!empty($errors)) {
                DB::rollback();
                return response()->json([
                    'status' => 'error',
                    'errors' => $errors,
                ], 409);
            }

            DB::commit(); // Commit the transaction if all entries are created successfully

            return response()->json([
                'status' => 'ok',
                'message' => 'Exam timetable entries created successfully!',
                'created_timetables' => $createdTimetables // Return the created entries
            ], 201);
        } catch (\Exception $e) {
            DB::rollback(); // Rollback if an exception occurs
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating the timetable entries: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function generate_resit_timetable(Request $request, $specialty_id, $level_id)
    {
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
