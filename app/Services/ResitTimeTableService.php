<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Illuminate\Support\Str;
use Exception;
use App\Models\ResitExam;
use App\Models\Resitexamtimetable;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Studentresit;
use Carbon\Carbon;
class ResitTimeTableService
{
    // Implement your logic here

    public function getResitableCoursesByExam($currentSchool, $resitExamId)
    {
        $resitExam = ResitExam::with(['semester'])
            ->where("school_branch_id", $currentSchool->id)
            ->findOrFail($resitExamId);

        $resitableCourses = Studentresit::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $resitExam->specialty_id)
            ->where("level_id", $resitExam->level_id)
            ->with(['courses', 'exam' => function ($query) use ($resitExam) {
                $query->where("semester_id", $resitExam->semester_id);
            }])
            ->get();

        $result = $resitableCourses->pluck('courses')->unique()->values()->toArray();

        return [
            'resit_exam' => $resitExam,
            'resitable_courses' => $result,
        ];
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
    public function createResitTimetable(array $resitTimetableEntries, object $currentSchool, string $resitExamId)
    {
        DB::beginTransaction();
        try {
            if (empty($resitTimetableEntries)) {
                return collect();
            }

            $timetableData = collect($resitTimetableEntries)->map(function ($entry) use ($currentSchool) {
                return [
                    'id' => Str::uuid(),
                    'course_id' => $entry['course_id'],
                    'resit_exam_id' => $entry['resit_exam_id'],
                    'specialty_id' => $entry['specialty_id'],
                    'level_id' => $entry['level_id'],
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

            $exam = ResitExam::findOrFail($resitExamId);
            $exam->timetable_published = true;
            $exam->save();

            DB::commit();
            return true;
        } catch (InvalidArgumentException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function deleteResitTimetable($resitExamId, $currentSchool)
    {
        $timetableEntries = Resitexamtimetable::where("school_branch_id", $currentSchool->id)
            ->where("exam_id", $resitExamId)->get();
        foreach ($timetableEntries as $entry) {
            $entry->delete();
        }
        return $timetableEntries;
    }
    public function updateResitTimetable(array $entries, object $currentSchool, string $resitExamId): Collection
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
