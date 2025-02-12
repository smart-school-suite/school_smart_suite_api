<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Examtimetable;
use App\Models\Exams;
use App\Models\Courses;
use Exception;

class ExamTimeTableService
{
    // Implement your logic here

    public function createExamTimeTable($examTimeTableData, $currentSchool)
    {
        $createdTimetables = [];
        DB::beginTransaction();

        try {
            foreach ($examTimeTableData as $examCourse) {
                $startTime = Carbon::parse($examCourse['start_time']);
                $endTime = Carbon::parse($examCourse['end_time']);
                $duration = $startTime->diffInMinutes($endTime);
                $durationString = gmdate("H:i:s", $duration);
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
                    return ApiResponseService::error("The timetable overlaps with existing courses. Please choose a different time", null, 409);
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

                $createdTimetables[] = $timetable;
            }
            DB::commit();

           return $createdTimetables;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteTimeTable($timeTableId, $currentSchool){
        $examTimeTableEntry = Examtimetable::Where('school_branch_id', $currentSchool->id)->find($timeTableId);
        if(!$examTimeTableEntry){
            return ApiResponseService::error("Exam Time Table Entry Not Found", null, 404);
        }
        $examTimeTableEntry->delete();
        return $examTimeTableEntry;
    }

    public function generateExamTimeTable($levelId, $specailtyId, $currentSchool){
        $timetables = ExamTimetable::Where('school_branch_id', $currentSchool->id)
            ->where('level_id', $levelId)
            ->where('specialty_id', $specailtyId)
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
            return $examTimetable;

    }

    public function prepareExamTimeTableData($examId, $currentSchool){
        $find_exam_id = Exams::with(["semester", "specialty", "level"])
            ->where("id", $examId)
            ->first();

        if (!$find_exam_id) {
            return ApiResponseService::error("Exam Not Found", null, 404);
        }

        $coursesData = Courses::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $find_exam_id->specialty->id)
            ->get();

        $results = [];
        $level_id = $find_exam_id->level->id;
        $specialty_id = $find_exam_id->specialty->id;

        foreach ($coursesData as $course) {
            $results[] = [
                'course_id' => $course->id,
                'course_name' => $course->course_title,
                'level_id' => $level_id,
                'specialty_id' => $specialty_id,
                'exam_id' => $examId
            ];
        }

        return $results;
    }


}
