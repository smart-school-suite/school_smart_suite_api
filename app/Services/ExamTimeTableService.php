<?php

namespace App\Services;

use App\Jobs\NotificationJobs\SendAdminExamTimetableAvailableNotificationJob;
use App\Jobs\NotificationJobs\SendExamTimetableAvailableNotificationJob;
use Illuminate\Support\Facades\DB;
use App\Models\Examtimetable;
use App\Models\Exams;
use App\Models\Schoolbranches;
use InvalidArgumentException;
use App\Models\Courses;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ExamTimeTableService
{

    /**
     * Creates exam timetable entries in the database.
     *
     * @param array $examTimetableEntries An array of exam timetable entry data.  Already validated!
     * @param Schoolbranches $currentSchool The current school Branch.
     * @param string $examId The ID of the exam.
     * @return array An array of created timetable IDs.
     * @throws InvalidArgumentException If the input data is invalid.
     * @throws Exception If an error occurs during the database transaction.
     */
    public function createExamTimeTable(array $examTimetableEntries, Schoolbranches $currentSchool, string $examId): array
    {
        DB::beginTransaction();
        try {
            if (empty($examTimetableEntries)) {
                throw new InvalidArgumentException('Exam timetable data cannot be empty.');
            }

            $createdTimetables = [];

            foreach ($examTimetableEntries as $entry) {
                $createdTimetableId = DB::table('examtimetable')->insertGetId([
                    'id' => Str::uuid(),
                    'course_id' => $entry['course_id'],
                    'exam_id' => $examId,
                    'student_batch_id' => $entry['student_batch_id'],
                    'specialty_id' => $entry['specialty_id'],
                    'level_id' => $entry['level_id'],
                    'date' => $entry['date'],
                    'start_time' => $entry['start_time'],
                    'duration' => $entry['duration'],
                    'end_time' => $entry['end_time'],
                    'school_branch_id' => $currentSchool->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $createdTimetables[] = $createdTimetableId;
            }

            $exam = Exams::with(['examtype.semesters', 'level'])->findOrFail($examId);
            $exam->timetable_published = true;
            $exam->save();
            DB::commit();
            $examData = [
                'semester' => $exam->examtype->semesters->name,
                'examName' => $exam->examtype->exam_name,
                'level' => $exam->level->name,
                'schoolYear' => $exam->school_year
            ];
            SendExamTimetableAvailableNotificationJob::dispatch(
                $exam->specialty_id,
                 $currentSchool->id,
                  $examData
            );
            SendAdminExamTimetableAvailableNotificationJob::dispatch(
                $currentSchool->id,
                $examData
            );
            return $createdTimetables;
        } catch (InvalidArgumentException $e) {
            DB::rollBack();
            Log::error('Invalid argument in ExamTimetableService::createExamTimeTable: ' . $e->getMessage(), [
                'examTimetableEntries' => $examTimetableEntries,
                'currentSchoolId' => $currentSchool->id,
                'examId' => $examId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Exam not found in ExamTimetableService::createExamTimeTable: ' . $e->getMessage(), [
                'examTimetableEntries' => $examTimetableEntries,
                'currentSchoolId' => $currentSchool->id,
                'examId' => $examId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            Log::critical('Error in ExamTimetableService::createExamTimeTable: ' . $e->getMessage(), [
                'examTimetableEntries' => $examTimetableEntries,
                'currentSchoolId' => $currentSchool->id,
                'examId' => $examId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception('An error occurred while creating the exam timetable: ' . $e->getMessage()); // Keep original message.
        }
    }

    /**
     * Deletes a single exam timetable entry.
     *
     * @param string $entryId The ID of the exam timetable entry to delete.
     * @param Schoolbranches $currentSchool The current school Branch.
     * @return Examtimetable|null The deleted Examtimetable entry, or null on error.
     */
    public function deleteTimetableEntry(string $entryId, Schoolbranches $currentSchool): ?Examtimetable
    {
        try {
            $examTimeTableEntry = Examtimetable::where('school_branch_id', $currentSchool->id)->findOrFail($entryId); // Use findOrFail
            $examTimeTableEntry->delete();
            return $examTimeTableEntry;
        } catch (ModelNotFoundException $e) {
            Log::error('Exam Time Table Entry Not Found in ExamTimetableService::deleteTimetableEntry: ' . $e->getMessage(), [
                'entryId' => $entryId,
                'currentSchoolId' => $currentSchool->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Generates the exam timetable data for a given level and specialty.
     *
     * @param string $examId The ID of the level.
     * @param SchoolBranches $currentSchool The current school.
     * @return array The generated exam timetable data, keyed by date.
     */
    public function generateExamTimeTable(string $examId, Schoolbranches $currentSchool): array
    {
        $timetables = Examtimetable::where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $examId)
            ->with(['course' => function ($query) {
                $query->select('id', 'course_title', 'credit', 'course_code');
            }])
            ->orderBy('date')
            ->get(['id', 'course_id', 'date', 'start_time', 'end_time', 'duration']);

        $examTimetable = [];

        foreach ($timetables as $timetable) {
            $date = $timetable->date;

            if (!isset($examTimetable[$date])) {
                $examTimetable[$date] = [];
            }

            $examTimetable[$date][] = [
                'id' => $timetable->id,
                'course_title' => $timetable->course->course_title,
                'credit' => $timetable->course->credit,
                'course_code' => $timetable->course->course_code,
                'start_time' => $timetable->start_time,
                'end_time' => $timetable->end_time,
                'duration' => $timetable->duration,
            ];
        }

        return array_change_key_case($examTimetable, CASE_LOWER);
    }
    /**
     * Prepares exam timetable data for a given exam.
     *
     * @param string $examId The ID of the exam.
     * @param SchoolBranches $currentSchool The current school.
     * @return array The prepared exam timetable data.
     */
    public function prepareExamTimeTableData($examId, SchoolBranches $currentSchool): array
    {
        try {
            $exam = Exams::with(['semester:id,name', 'specialty:id,specialty_name', 'level:id,name'])
                ->where('id', $examId)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            Log::error('Exam Not Found in ExamTimetableService::prepareExamTimeTableData: ' . $e->getMessage(), [
                'examId' => $examId,
                'currentSchoolId' => $currentSchool->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception("Exam Not Found");
        }

        $coursesData = Courses::where('school_branch_id', $currentSchool->id)
            ->where('specialty_id', $exam->specialty->id)
            ->where('semester_id', $exam->semester_id)
            ->get(['id', 'course_title']);

        $results = [];
        $levelId = $exam->level->id;
        $specialtyId = $exam->specialty->id;
        foreach ($coursesData as $course) {
            $results[] = [
                'course_id' => $course->id,
                'course_name' => $course->course_title,
                'level_id' => $levelId,
                'specialty_id' => $specialtyId,
                'exam_id' => $examId,
            ];
        }
        return $results;
    }

    /**
     * Deletes all timetable entries for a given exam and school.
     *
     * @param string $examId The ID of the exam.
     * @param SchoolBranches $currentSchool The current school.
     * @return Collection|null A collection of the deleted Examtimetable entries, or null on error.
     */
    public function deleteExamTimetable(string $examId, SchoolBranches $currentSchool): ?Collection
    {
        try {
            $timetableEntries = Examtimetable::where('school_branch_id', $currentSchool->id)
                ->where('exam_id', $examId)
                ->get();

            DB::transaction(function () use ($timetableEntries) {
                foreach ($timetableEntries as $entry) {
                    $entry->delete();
                }
            });
            $exam = Exams::findOrFail($examId);
            $exam->timetable_published = false;
            $exam->save();
            return $timetableEntries;
        } catch (ModelNotFoundException $e) {
            Log::error('Exam not found in ExamTimetableService::deleteExamTimetable: ' . $e->getMessage(), [
                'examId' => $examId,
                'currentSchoolId' => $currentSchool->id,
                'trace' => $e->getTraceAsString(),
            ]);
             throw $e;
        } catch (Exception $e) {
            Log::critical('Error deleting timetable in ExamTimetableService::deleteExamTimetable: ' . $e->getMessage(), [
                'examId' => $examId,
                'currentSchoolId' => $currentSchool->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Updates exam timetable entries in the database.
     *
     * This method assumes that the input data ($examTimetableEntries) has already been validated
     * by the controller.  Therefore, it focuses on updating the database efficiently
     * and handling potential database-related errors.
     *
     * @param array $examTimetableEntries An array of exam timetable entry data.
     * Each element should be an array with the following keys:
     * 'entry_id', 'course_id', 'exam_id', 'student_batch_id',
     * 'specialty_id', 'school_year', 'start_time', 'level_id',
     * 'date', 'end_time'.
     * @param SchoolBranches $currentSchool The current school.
     * @return Collection|null A collection of the updated Examtimetable entries, or null on error.
     * @throws Exception If an error occurs during the database transaction.
     */
    public function updateExamTimetable(array $examTimetableEntries, SchoolBranches $currentSchool): ?Collection
    {
        try {
            if (empty($examTimetableEntries)) {
                Log::warning('No exam timetable entries to update in ExamTimetableService::updateExamTimetable.');
                return collect();
            }

            $updatedTimetables = collect();

            DB::transaction(function () use ($examTimetableEntries, $currentSchool, &$updatedTimetables) {
                foreach ($examTimetableEntries as $entryData) {
                    $entryId = $entryData['entry_id'];
                    $timetableEntry = Examtimetable::where('school_branch_id', $currentSchool->id)
                        ->findOrFail($entryId);
                    $timetableEntry->fill([
                        'course_id' => $entryData['course_id'],
                        'exam_id' => $entryData['exam_id'],
                        'student_batch_id' => $entryData['student_batch_id'],
                        'specialty_id' => $entryData['specialty_id'],
                        'level_id' => $entryData['level_id'],
                        'date' => $entryData['date'],
                        'start_time' => $entryData['start_time'],
                        'end_time' => $entryData['end_time'],
                    ]);

                    $timetableEntry->save();
                    $updatedTimetables->push($timetableEntry);
                }
            });

            return $updatedTimetables;
        } catch (ModelNotFoundException $e) {
            Log::error('One or more timetable entries not found in ExamTimetableService::updateExamTimetable: ' . $e->getMessage(), [
                'examTimetableEntries' => $examTimetableEntries,
                'currentSchoolId' => $currentSchool->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        } catch (Exception $e) {
            Log::critical('Error updating exam timetable entries in ExamTimetableService::updateExamTimetable: ' . $e->getMessage(), [
                'examTimetableEntries' => $examTimetableEntries,
                'currentSchoolId' => $currentSchool->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception('Failed to update exam timetable entries: ' . $e->getMessage());
        }
    }
}
