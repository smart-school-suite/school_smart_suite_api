<?php

namespace App\Services;

use App\Jobs\DataCreationJob\CreateInstructorAvailabilityJob;
use App\Jobs\DataCreationJob\CreateTeacherAvailabilityJob;
use App\Jobs\NotificationJobs\SendNewSemesterAvialableNotificationJob;
use App\Models\Educationlevels;
use Illuminate\Database\Eloquent\Collection;
use App\Models\FeeSchedule;
use App\Models\SchoolSemester;
use App\Models\Semester;
use App\Models\Specialty;
use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SchoolSemesterService
{
    // Implement your logic here

    public function createSchoolSemester($semesterData, $currentSchool)
    {

        DB::beginTransaction();
        $schoolSemester = new SchoolSemester();
        $schoolSemesterId = Str::uuid();
        $specialty = Specialty::where("school_branch_id", $currentSchool->id)
                                ->with(['level'])
                               ->find($semesterData['specialty_id']);
        $schoolSemester->id = $schoolSemesterId;
        $schoolSemester->start_date = $semesterData["start_date"];
        $schoolSemester->end_date = $semesterData["end_date"];
        $schoolSemester->school_year = $semesterData["school_year"];
        $schoolSemester->semester_id = $semesterData["semester_id"];
        $schoolSemester->specialty_id = $semesterData["specialty_id"];
        $schoolSemester->status = 'active';
        $schoolSemester->student_batch_id = $semesterData["student_batch_id"];
        $schoolSemester->school_branch_id = $currentSchool->id;
        $schoolSemester->timetable_published = false;
        $schoolSemester->save();
        FeeSchedule::create([
               'specialty_id' => $semesterData['specialty_id'],
               'level_id' => $specialty->level_id,
               'school_branch_id' => $currentSchool->id,
               'school_semester_id' => $schoolSemesterId
        ]);
        CreateTeacherAvailabilityJob::dispatch([
            'specialty_id' => $semesterData['specialty_id'],
            'school_branch_id' => $currentSchool->id,
            'school_semester_id' => $schoolSemesterId,
            'level_id' => $specialty->level_id
        ]);
        DB::commit();
        $data = [
             'startDate' => $semesterData['start_date'],
             'endDate' => $semesterData['end_date'],
             'semester' => Semester::find($semesterData['semester_id'])->name,
             'level' => $specialty->level->name,
             'schoolYear' => $semesterData['school_year']
        ];
        SendNewSemesterAvialableNotificationJob::dispatch(
            $semesterData['specialty_id'],
            $currentSchool->id,
            $data
        );
        CreateInstructorAvailabilityJob::dispatch(
            $currentSchool->id,
                       $schoolSemesterId
        );
        return $schoolSemester;
    }
    public function updateSchoolSemester($semesterData, $currentSchool, $schoolSemesterId)
    {
        $schoolSemester = SchoolSemester::where("school_branch_id", $currentSchool->id)->find($schoolSemesterId);
        if (!$schoolSemester) {
            return ApiResponseService::error("School Semester Not Found", null, 404);
        }

        $filteredData = array_filter($semesterData);
        $schoolSemester->update($filteredData);
        return $schoolSemester;
    }
    public function bulkUpdateSchoolSemester(array $updateSemesterList)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($updateSemesterList as $updateSemester) {
                $schoolSemester = SchoolSemester::findOrFail($updateSemester['semester_id']);
                $filteredData = array_filter($updateSemester);
                $schoolSemester->update($filteredData);
                $result[] = [
                    $schoolSemester
                ];
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function deleteSchoolSemester($schoolSemesterId, $currentSchool)
    {
        $schoolSemester = SchoolSemester::where("school_branch_id", $currentSchool->id)->find($schoolSemesterId);
        if (!$schoolSemester) {
            return ApiResponseService::error("School Semester Not Found", null, 404);
        }
        $schoolSemester->delete();
        return $schoolSemester;
    }
    public function bulkDeleteSchoolSemester($schoolSemesterIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($schoolSemesterIds as $schoolSemesterId) {
                $schoolSemester = SchoolSemester::findOrFail($schoolSemesterId['school_semester_id']);
                $schoolSemester->delete();
                $result[] = $schoolSemester;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
      public function getSchoolSemesters($currentSchool)
    {
        $schoolSemesters = SchoolSemester::where("school_branch_id", $currentSchool->id)
            ->select('id', 'start_date', 'end_date', 'school_year', 'status', 'specialty_id', 'semester_id', 'timetable_published', 'student_batch_id')
            ->with([
                'specialty' => function (BelongsTo $belongsTo) {
                    $belongsTo->select('id', 'specialty_name', 'level_id');
                },
                'specialty.level' => function (BelongsTo $belongsTo) {
                    $belongsTo->select('id', 'level', 'name');
                },
                'semester' => function (BelongsTo $belongsTo) {

                    $belongsTo->select('id', 'name',);
                },
            ])
            ->get();

        return $schoolSemesters->map(function ($semester) {
            return [
                "id" => $semester->id,
                "start_date" => $semester->start_date,
                "end_date" => $semester->end_date,
                "school_year" => $semester->school_year,
                "status" => $semester->status,
                "student_batch_id" => $semester->student_batch_id,
                "specialty_id" => $semester->specialty_id,
                "specialty_name" => $semester->specialty->specialty_name ?? null,
                "level_name" => $semester->specialty->level->name ?? null,
                "level" => $semester->specialty->level->level ?? null,
                "level_id" => $semester->specialty->level->id ?? null,
                "semester_id" => $semester->semester_id,
                "timetable_published" => $semester->timetable_published,
                "semester_name" => $semester->semester->name ?? $semester->semester->semester_name ?? null,
            ];
        });
    }
    public function getActiveSchoolSemesters($currentSchool)
    {
        $schoolSemesters = SchoolSemester::where("school_branch_id", $currentSchool->id)
            ->with(['specailty', 'specailty.level', 'semester', 'studentBatch'])
            ->where("status", "active")
            ->get();
        return $schoolSemesters;
    }
    public function getSchoolSemesterDetail($currentSchool, $semesterId)
    {
        $schoolSemesterDetails = SchoolSemester::with(['specailty', 'specailty.level', 'semester', 'studentBatch'])->where("school_branch_id", $currentSchool->id)->find($semesterId);
        return $schoolSemesterDetails;
    }
}
