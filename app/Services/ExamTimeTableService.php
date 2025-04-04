<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Examtimetable;
use App\Models\Exams;
use InvalidArgumentException; // Import the InvalidArgumentException
use App\Models\Courses;
use Illuminate\Support\Str;
use Exception;

class ExamTimeTableService
{
    // Implement your logic here

    public function createExamTimeTable($examTimetableEntries, $currentSchool, $examId)
    {

        DB::beginTransaction();

        try {

            if (!isset($examTimetableEntries) || !is_array($examTimetableEntries)) {
                throw new InvalidArgumentException('Invalid exam timetable data.');
            }

            $createdTimetables = [];
            $uniqueId = Str::random(30);

            foreach ($examTimetableEntries as $entry) {
                $createdTimetableId = DB::table('examtimetable')->insertGetId([
                    'id' => $uniqueId,
                    'course_id' => $entry['course_id'],
                    'exam_id' => $entry['exam_id'],
                    'student_batch_id' => $entry['student_batch_id'],
                    'specialty_id' => $entry['specialty_id'],
                    'level_id' => $entry['level_id'],
                    'date' => $entry['date'],
                    'start_time' => $entry['start_time'],
                    'duration' => $entry['duration'],
                    'end_time' => $entry['end_time'],
                    'school_year' => $entry['school_year'],
                    'school_branch_id' => $currentSchool->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $createdTimetables[] = $createdTimetableId;
            }

            $exam = Exams::findOrFail($examId);
            $exam->timetable_published = true;
            $exam->save();

            DB::commit();

            return $createdTimetables;

        } catch (InvalidArgumentException $e) {

            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('An error occurred while creating the exam timetable: ' . $e->getMessage());
        }
    }

    public function deleteTimetableEntry($timeTableId, $currentSchool)
    {
        $examTimeTableEntry = Examtimetable::Where('school_branch_id', $currentSchool->id)->find($timeTableId);
        if (!$examTimeTableEntry) {
            return ApiResponseService::error("Exam Time Table Entry Not Found", null, 404);
        }
        $examTimeTableEntry->delete();
        return $examTimeTableEntry;
    }

    public function deleteTimetable(string $examId, $currentSchool){
        $timetableEntries = Examtimetable::where("school_branch_id", $currentSchool->id)->where("exam_id", $examId)->get();
        foreach($timetableEntries as $entry){
           $entry->delete();
        }
        $exam = Exams::findOrFail($examId);
        $exam->timetable_published = false;
        $exam->save();
        return $timetableEntries;
    }

    public function generateExamTimeTable($levelId, $specailtyId, $currentSchool)
    {
        $timetables = ExamTimetable::Where('school_branch_id', $currentSchool->id)
            ->where('level_id', $levelId)
            ->where('specialty_id', $specailtyId)
            ->with('course')
            ->orderBy('date')
            ->get();
        $examTimetable = [];
        foreach ($timetables as $timetable) {
            if (!isset($examTimetable[$timetable->date])) {
                $examTimetable[$timetable->date] = [];
            }
            $examTimetable[$timetable->date][] = [
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

    public function prepareExamTimeTableData($examId, $currentSchool)
    {
        $exam = Exams::with(["semester", "specialty", "level"])
            ->where("id", $examId)
            ->first();

        if (!$exam) {
            return ApiResponseService::error("Exam Not Found", null, 404);
        }

        $coursesData = Courses::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $exam->specialty->id)
            ->where('semester_id', $exam->semester_id)
            ->get();

        $results = [];
        $level_id = $exam->level->id;
        $specialty_id = $exam->specialty->id;

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
