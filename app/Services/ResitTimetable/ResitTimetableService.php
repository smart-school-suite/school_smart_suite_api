<?php

namespace App\Services\ResitTimetable;

use App\Exceptions\AppException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Illuminate\Support\Str;
use Exception;
use App\Models\ResitExam;
use App\Models\Resitexamtimetable;
use App\Models\Studentresit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Events\Actions\AdminActionEvent;

class ResitTimetableService
{
    public function getResitableCoursesByExam($currentSchool, $resitExamId)
    {
        try {
            $resitExam = ResitExam::with(['semester'])
                ->where("school_branch_id", $currentSchool->id)
                ->findOrFail($resitExamId);

            $resitableCourses = Studentresit::where("school_branch_id", $currentSchool->id)
                ->where("specialty_id", $resitExam->specialty_id)
                ->where("level_id", $resitExam->level_id)
                ->with([
                    'courses',
                    'exam' => function ($query) use ($resitExam) {
                        $query->where("semester_id", $resitExam->semester_id);
                    }
                ])
                ->get();

            if ($resitableCourses->isEmpty()) {
                throw new AppException(
                    "No students have registered to resit any courses for this exam.",
                    404,
                    "No Resit Courses Found",
                    "There are no registered resit courses for the specified exam. Please check student registrations.",
                    null
                );
            }

            $result = $resitableCourses->pluck('courses')->unique()->values()->toArray();

            if (empty($result)) {
                throw new AppException(
                    "No resitable courses were found for this exam.",
                    404,
                    "No Resitable Courses Found",
                    "The system could not find any courses available for resitting, even though students are registered. Please verify the data.",
                    null
                );
            }

            return [
                'resit_exam' => $resitExam,
                'resitable_courses' => $result,
            ];
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "The resit exam you are looking for was not found.",
                404,
                "Resit Exam Not Found",
                "We could not find the resit exam with the provided ID for this school. Please verify the ID and try again.",
                null
            );
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while fetching resitable courses.",
                500,
                "Internal Server Error",
                "A server-side issue prevented the list of resitable courses from being retrieved.",
                null
            );
        }
    }
    public function formatDurationFromTimes(string $startTime, string $endTime): string
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        $diffInMinutes = $start->diffInMinutes($end);
        $hours = floor($diffInMinutes / 60);
        $minutes = $diffInMinutes % 60;

        $duration = '';
        if ($hours > 0) {
            $duration .= "$hours hours";
        }
        if ($minutes > 0 || $duration === '') {
            $duration .= "$minutes minutes";
        }

        return trim($duration);
    }
    public function createResitTimetable(array $resitTimetableEntries, object $currentSchool, string $resitExamId, $authAdmin)
    {
        DB::beginTransaction();
        try {
            if (empty($resitTimetableEntries)) {
                throw new AppException(
                    "The list of timetable entries cannot be empty.",
                    400,
                    "Invalid Request",
                    "You must provide a list of timetable entries to create a timetable.",
                    null
                );
            }

            $exam = ResitExam::where("school_branch_id", $currentSchool->id)
                ->findOrFail($resitExamId);

            if ($exam->timetable_published === true) {
                throw new AppException(
                    "The resit exam timetable has already been published and cannot be modified.",
                    409,
                    "Timetable Already Published",
                    "You can only create a timetable for resit exams that have not yet been published.",
                    null
                );
            }

            $timetableData = collect($resitTimetableEntries)->map(function ($entry) use ($currentSchool, $exam) {
                return [
                    'id' => Str::uuid(),
                    'course_id' => $entry['course_id'],
                    'resit_exam_id' => $exam->id,
                    'specialty_id' => $exam->specialty_id,
                    'level_id' => $exam->level_id,
                    'date' => $entry['date'],
                    'start_time' => $entry['start_time'],
                    'end_time' => $entry['end_time'],
                    'duration' => $this->formatDurationFromTimes($entry['start_time'], $entry['end_time']),
                    'school_branch_id' => $currentSchool->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();
            Resitexamtimetable::insert($timetableData);

            $exam->timetable_published = true;
            $exam->save();
            DB::commit();

            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.resitTimetable.create"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "resitTimetableManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $timetableData,
                    "message" => "Resit Timetable Created",
                ]
            );
            return true;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new AppException(
                "The resit exam you are trying to create a timetable for was not found.",
                404,
                "Resit Exam Not Found",
                "We could not find the specified resit exam. Please verify the exam ID and try again.",
                null
            );
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "An unexpected error occurred while creating the resit timetable.",
                500,
                "Timetable Creation Error",
                "A server-side issue prevented the timetable from being created.",
                null
            );
        }
    }
    public function deleteResitTimetable($resitExamId, $currentSchool, $authAdmin)
    {
        try {
            $resitExam = ResitExam::where("school_branch_id", $currentSchool->id)
                ->find($resitExamId);

            if (!$resitExam) {
                throw new AppException(
                    "The resit exam you are looking for was not found.",
                    404,
                    "Resit Exam Not Found",
                    "We could not find the resit exam with the provided ID for this school. Please verify the ID and try again.",
                    null
                );
            }

            if ($resitExam->timetable_published === false) {
                throw new AppException(
                    "The resit exam timetable cannot be deleted because it has not been published.",
                    409,
                    "Timetable Not Published",
                    "You can only delete published timetables. This timetable has not been published yet.",
                    null
                );
            }

            $timetableEntries = Resitexamtimetable::where("school_branch_id", $currentSchool->id)
                ->where("exam_id", $resitExamId)->get();

            if ($timetableEntries->isEmpty()) {
                throw new AppException(
                    "No timetable entries found for the specified resit exam.",
                    404,
                    "No Timetable Entries Found",
                    "There are no timetable entries to delete for the given resit exam ID. Please verify the ID and try again.",
                    null
                );
            }

            foreach ($timetableEntries as $entry) {
                $entry->delete();
            }

            $resitExam->timetable_published = false;
            $resitExam->save();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.resitTimetable.delete"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "resitTimetableManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $timetableEntries,
                    "message" => "Resit Timetable Dleted",
                ]
            );
            return $timetableEntries;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while deleting the resit timetable.",
                500,
                "Timetable Deletion Error",
                "A server-side issue prevented the timetable from being deleted. Please try again.",
                null
            );
        }
    }
    public function updateResitTimetable(array $entries, object $currentSchool, string $resitExamId, $authAdmin)
    {
        DB::beginTransaction();
        try {
            if (empty($entries)) {
                return collect();
            }
            $timetableEntries = Resitexamtimetable::where("school_branch_id", $currentSchool->id)
                ->where("resit_exam_id", $resitExamId)
                ->get()
                ->keyBy('id');

            $updatedIds = [];

            foreach ($entries as $entry) {
                $entryId = $entry['entry_id'];
                $timetableEntryToUpdate = $timetableEntries[$entryId];
                $updateData = [];
                if (isset($entry['course_id'])) {
                    $updateData['course_id'] = $entry['course_id'];
                }
                if (isset($entry['date'])) {
                    $updateData['date'] = $entry['date'];
                }
                if (isset($entry['start_time'])) {
                    $updateData['start_time'] = $entry['start_time'];
                }
                if (isset($entry['end_time'])) {
                    $updateData['end_time'] = $entry['end_time'];
                }
                if (isset($entry['duration'])) {
                    $updateData['duration'] = $entry['duration'];
                }
                if (isset($updateData['date']) || isset($updateData['start_time']) || isset($updateData['end_time'])) {
                    $clashingEntries = Resitexamtimetable::where("school_branch_id", $currentSchool->id)
                        ->where("resit_exam_id", $resitExamId)
                        ->where('id', '!=', $entryId)
                        ->where('date', $updateData['date'] ?? $timetableEntryToUpdate->date)
                        ->where(function ($query) use ($updateData, $timetableEntryToUpdate) {
                            $newStartTime = $updateData['start_time'] ?? $timetableEntryToUpdate->start_time;
                            $newEndTime = $updateData['end_time'] ?? $timetableEntryToUpdate->end_time;

                            $query->where(function ($q) use ($newStartTime, $newEndTime) {
                                $q->where('start_time', '<', $newEndTime)
                                    ->where('end_time', '>', $newStartTime);
                            });
                        })
                        ->exists();

                    if ($clashingEntries) {
                        DB::rollBack();
                        throw new Exception("Time clash detected for entry ID: {$entryId}");
                    }
                }

                if (isset($updateData['course_id'])) {
                    $duplicateCourse = Resitexamtimetable::where("school_branch_id", $currentSchool->id)
                        ->where("resit_exam_id", $resitExamId)
                        ->where("course_id", $updateData['course_id'])
                        ->where('id', '!=', $entryId)
                        ->exists();
                    if ($duplicateCourse) {
                        DB::rollBack();
                        throw new Exception("Duplicate course ID '{$updateData['course_id']}' in timetable for entry ID: {$entryId}");
                    }
                }
                $timetableEntryToUpdate->update($updateData);
                $updatedIds[] = $entryId;
            }

            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.resitTimetable.update"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "resitTimetableManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $timetableEntries,
                    "message" => "Resit Timetable Updated",
                ]
            );
            return Resitexamtimetable::whereIn('id', $updatedIds)->get(); // Return updated models

        } catch (InvalidArgumentException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('An error occurred while updating the exam timetable: ' . $e->getMessage());
        }
    }
}
