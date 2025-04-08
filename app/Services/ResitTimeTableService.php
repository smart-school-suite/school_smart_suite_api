<?php

namespace App\Services;

use App\Models\Resitablecourses;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Illuminate\Support\Str;
use Exception;
use App\Models\Exams;
use App\Models\Resitexamtimetable;

class ResitTimeTableService
{
    // Implement your logic here

    public function getResitableCoursesByExam($currentSchool, $examId){
        $resitableCourses = Resitablecourses::where("school_branch_id", $currentSchool->id)
            ->where("exam_id", $examId)
            ->with(['courses'])
            ->get();
        return $resitableCourses;
    }

    public function createResitTimetable($resitTimetableEntries, $currentSchool, $examId)
    {
        DB::beginTransaction();

        try {
            if (!isset($resitTimetableEntries) || !is_array($resitTimetableEntries)) {
                throw new InvalidArgumentException('Invalid exam timetable data.');
            }

            $createdTimetables = [];

            foreach ($resitTimetableEntries as $entry) {
                $uniqueId = Str::random(30);
                $createdTimetableId = DB::table('resit_examtimetable')->insertGetId([
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

    public function deleteResitTimetable($examId, $currentSchool){
        $timetableEntries = Resitexamtimetable::where("school_branch_id", $currentSchool->id)->where("exam_id", $examId)->get();
        foreach($timetableEntries as $entry){
            $entry->delete();
        }
        return $timetableEntries;
    }
}
