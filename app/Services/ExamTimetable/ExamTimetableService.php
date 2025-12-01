<?php

namespace App\Services\ExamTimetable;

use App\Exceptions\AppException;
use App\Jobs\NotificationJobs\SendAdminExamTimetableAvailableNotificationJob;
use App\Jobs\NotificationJobs\SendExamTimetableAvailableNotificationJob;
use Illuminate\Support\Facades\DB;
use App\Models\Examtimetable;
use App\Models\Exams;
use App\Models\Schoolbranches;
use App\Models\Courses;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use App\Events\Actions\AdminActionEvent;

class ExamTimetableService
{
    public function createExamTimeTable(array $examTimetableEntries, Schoolbranches $currentSchool, string $examId, $authAdmin)
    {
        DB::beginTransaction();
        try {
            $exam = Exams::with(['examtype.semesters', 'level'])
                ->findOrFail($examId);

            if ($exam->timetable_published === true) {
                throw new AppException(
                    "The exam timetable has already been published.",
                    409,
                    "Timetable Already Published",
                    "You cannot create a timetable for an exam that has already been published. Please delete the existing one first if you want to make changes.",
                    null
                );
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
                    'duration' => $this->formatDurationFromTimes($entry['start_time'], $entry['end_time']),
                    'end_time' => $entry['end_time'],
                    'school_branch_id' => $currentSchool->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $createdTimetables[] = $createdTimetableId;
            }

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
            AdminActionEvent::dispatch(
                [
                    "permissions" => ["schoolAdmin.exam.timetable.create"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" => $currentSchool->id,
                    "feature" => "examTimetableManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $createdTimetables,
                    "message" => "Exam Timetable Created",
                ]
            );
            return $createdTimetables;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new AppException(
                "The exam you are trying to create a timetable for was not found.",
                404,
                "Exam Not Found",
                "We could not find the exam with the provided ID. Please verify the ID and try again.",
                null
            );
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "An unexpected error occurred while creating the exam timetable.",
                500,
                "Timetable Creation Error",
                "A server-side issue prevented the timetable from being created.",
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
            $duration .= "$hours . h";
        }
        if ($minutes > 0 || $duration === '') {
            $duration .= "$minutes . m";
        }

        return trim($duration);
    }
    public function deleteTimetableEntry(string $entryId, Schoolbranches $currentSchool, $authAdmin)
    {
        try {
            $examTimeTableEntry = Examtimetable::where('school_branch_id', $currentSchool->id)
                ->findOrFail($entryId);
            $examTimeTableEntry->delete();
            AdminActionEvent::dispatch(
                [
                    "permissions" => ["schoolAdmin.exam.timetable.delete"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" => $currentSchool->id,
                    "feature" => "examTimetableManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $examTimeTableEntry,
                    "message" => "Exam Timetable Entry Deleted",
                ]
            );
            return $examTimeTableEntry;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "The exam timetable entry you are trying to delete was not found.",
                404,
                "Timetable Entry Not Found",
                "We could not find the timetable entry with the provided ID for this school. It may have already been deleted.",
                null
            );
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while deleting the timetable entry.",
                500,
                "Deletion Error",
                "We encountered a server-side issue while attempting to delete the timetable entry. Please try again later.",
                null
            );
        }
    }
    public function generateExamTimeTable(string $examId, Schoolbranches $currentSchool): array
    {
        try {
            $exam = Exams::where("school_branch_id", $currentSchool->id)
                ->findOrFail($examId);

            if ($exam->timetable_published === false) {
                throw new AppException(
                    "The exam timetable has not been published yet.",
                    404,
                    "Timetable Not Published",
                    "The timetable for this exam is not yet available. Please check back later or contact the administrator.",
                    null
                );
            }

            $timetables = Examtimetable::where('school_branch_id', $currentSchool->id)
                ->where('exam_id', $examId)
                ->with(['course' => function ($query) {
                    $query->select('id', 'course_title', 'credit', 'course_code');
                }])
                ->orderBy('date')
                ->get(['id', 'course_id', 'date', 'start_time', 'end_time', 'duration']);

            if ($timetables->isEmpty()) {
                throw new AppException(
                    "No timetable entries found for this exam.",
                    404,
                    "No Timetable Entries",
                    "There are no timetable entries available for the specified exam. Please ensure the timetable has been created and published.",
                    null
                );
            }

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
                    'start_time' => Carbon::parse($timetable->start_time)->format('h:i A'),
                    'end_time' => Carbon::parse($timetable->end_time)->format('h:i A'),
                    'duration' => $timetable->duration,
                ];
            }

            return array_change_key_case($examTimetable, CASE_LOWER);
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "The specified exam was not found.",
                404,
                "Exam Not Found",
                "We could not find the exam with the provided ID for this school. Please verify the ID and try again.",
                null
            );
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while generating the exam timetable.",
                500,
                "Timetable Generation Error",
                "A server-side issue prevented the timetable from being generated successfully.",
                null
            );
        }
    }
    public function prepareExamTimeTableData($examId, SchoolBranches $currentSchool): array
    {
        try {
            $exam = Exams::with(['semester:id,name', 'specialty:id,specialty_name', 'level:id,name'])
                ->where('id', $examId)
                ->firstOrFail();

            $coursesData = Courses::where('school_branch_id', $currentSchool->id)
                ->where('specialty_id', $exam->specialty->id)
                ->where('semester_id', $exam->semester_id)
                ->where("status", "active")
                ->get(['id', 'course_title']);

            if ($coursesData->isEmpty()) {
                throw new AppException(
                    "No courses were found for this exam's specialty and semester.",
                    404,
                    "No Courses Found",
                    "There are no active courses to prepare a timetable for based on the selected exam's details. Please ensure courses are correctly configured.",
                    null
                );
            }

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
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "The specified exam was not found.",
                404,
                "Exam Not Found",
                "We could not find the exam with the provided ID for this school. Please verify the ID and try again.",
                null
            );
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while preparing the timetable data.",
                500,
                "Internal Server Error",
                "A server-side issue prevented the timetable data from being prepared successfully.",
                null
            );
        }
    }
    public function deleteExamTimetable(string $examId, SchoolBranches $currentSchool, $authAdmin): ?Collection
    {
        try {
            $exam = Exams::where("school_branch_id", $currentSchool->id)
                ->findOrFail($examId);

            if ($exam->timetable_published === false) {
                throw new AppException(
                    "The exam timetable has not been published yet.",
                    409,
                    "Timetable Not Published",
                    "The timetable for this exam is not yet available. You can't delete what isn't there.",
                    null
                );
            }

            $timetableEntries = Examtimetable::where('school_branch_id', $currentSchool->id)
                ->where('exam_id', $examId)
                ->get();

            if ($timetableEntries->isEmpty()) {
                throw new AppException(
                    "No timetable entries found for this exam.",
                    404,
                    "No Timetable Entries",
                    "There are no timetable entries available to delete for the specified exam.",
                    null
                );
            }

            DB::transaction(function () use ($timetableEntries, $exam) {
                foreach ($timetableEntries as $entry) {
                    $entry->delete();
                }
                $exam->timetable_published = false;
                $exam->save();
            });

            AdminActionEvent::dispatch(
                [
                    "permissions" => ["schoolAdmin.exam.timetable.delete"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" => $currentSchool->id,
                    "feature" => "examTimetableManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $timetableEntries,
                    "message" => "Exam Timetable Deleted",
                ]
            );
            return $timetableEntries;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "The exam you are trying to delete the timetable for was not found.",
                404,
                "Exam Not Found",
                "We could not find the exam with the provided ID for this school. Please verify the ID and try again.",
                null
            );
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while deleting the exam timetable.",
                500,
                "Timetable Deletion Error",
                "A server-side issue prevented the timetable from being deleted. Please try again later.",
                null
            );
        }
    }
    public function updateExamTimetable(array $examTimetableEntries, SchoolBranches $currentSchool, $authAdmin): ?Collection
    {
        try {
            if (empty($examTimetableEntries)) {
                return collect();
            }

            $updatedTimetables = collect();

            DB::transaction(function () use ($examTimetableEntries, $currentSchool, &$updatedTimetables) {
                foreach ($examTimetableEntries as $entryData) {
                    $entryId = $entryData['entry_id'] ?? null;
                    if (!$entryId) {
                        throw new AppException(
                            "An entry ID is missing from one of the timetable update entries.",
                            400,
                            "Invalid Input",
                            "Each entry must contain a valid 'entry_id' field.",
                            null
                        );
                    }

                    $timetableEntry = Examtimetable::where('school_branch_id', $currentSchool->id)
                        ->findOrFail($entryId);

                    $fillableData = collect($entryData)->except(['entry_id'])->toArray();
                    if (empty($fillableData)) {
                        throw new AppException(
                            "No valid data was provided for one of the entries to be updated.",
                            400,
                            "No Data Provided",
                            "The entry for ID '{$entryId}' did not contain any valid fields to update.",
                            null
                        );
                    }

                    $timetableEntry->fill($fillableData);
                    $timetableEntry->save();
                    $updatedTimetables->push($timetableEntry);
                }
            });

            AdminActionEvent::dispatch(
                [
                    "permissions" => ["schoolAdmin.exam.timetable.update"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" => $currentSchool->id,
                    "feature" => "examTimetableManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $updatedTimetables,
                    "message" => "Exam Timetable Updated",
                ]
            );
            return $updatedTimetables;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "One or more timetable entries you tried to update were not found.",
                404,
                "Timetable Entry Not Found",
                "We could not find a timetable entry for one of the provided IDs. Please check the list and try again.",
                null
            );
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while updating exam timetable entries in bulk.",
                500,
                "Bulk Update Error",
                "A server-side issue prevented the bulk update from completing successfully.",
                null
            );
        }
    }
    public function getExamTimetableStudentIdExamId($currentSchool, $studentId, $examId)
    {
        $timetableEntries = Examtimetable::where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $examId)
            ->with([
                'course',
                'exam.examtype',
                'exam.semester',
                'exam.level',
                'exam.specialty'
            ])
            ->get();

        if ($timetableEntries->isEmpty()) {
            return response()->json([
                "exam"  => null,
                "slots" => []
            ]);
        }

        $exam = $timetableEntries->first()->exam;

        $slots = $timetableEntries->map(function ($entry) {
            $start = Carbon::parse($entry->start_time);
            $end = Carbon::parse($entry->end_time);

            $duration = "N/A";
            if ($start && $end && $end > $start) {
                $diff = $start->diff($end);
                $hours = $diff->h;
                $mins  = $diff->i;

                if ($hours > 0) {
                    $duration = $mins > 0 ? "{$hours}h {$mins}min" : "{$hours}h";
                } else {
                    $duration = "{$mins}min";
                }
            }

            return [
                "id"              => $entry->id,
                "course_title"    => $entry->course?->course_title ?? "N/A",
                "course_code"     => $entry->course?->course_code ?? "N/A",
                "course_credit"   => $entry->course?->credit ?? 0,
                "duration"        => $duration,
                "date"            => $entry->date,
                "start_time"      => Carbon::parse($entry->start_time)->format('H:i'),
                "end_time"        => Carbon::parse($entry->end_time)->format('H:i'),
            ];
        })
            ->sortBy('date')
            ->sortBy('start_time')
            ->values();

        return [
            "exam" => [
                "exam_id"             => $exam->id,
                "exam_name"           => $exam->examtype?->exam_name ?? "Exam",
                "semester"            => $exam->semester?->name ?? "Unknown Semester",
                "semester_id"         => $exam->semester_id,
                "level_name"          => $exam->level?->name ?? "N/A",
                "specialty_name"      => $exam->specialty?->specialty_name ?? "N/A",
                "timetable_published" => (bool) $exam->timetable_published,
                "result_published"    => (bool) $exam->result_published,
            ],
            "slots" => $slots
        ];
    }
}
