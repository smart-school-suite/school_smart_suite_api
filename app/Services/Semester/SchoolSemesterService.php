<?php

namespace App\Services\Semester;
use App\Jobs\DataCreationJob\CreateExamJob;
use App\Jobs\DataCreationJob\CreateInstructorAvailabilityJob;
use App\Jobs\DataCreationJob\CreateTeacherAvailabilityJob;
use App\Jobs\NotificationJobs\SendNewSemesterAvialableNotificationJob;
use App\Models\FeeSchedule;
use App\Models\SchoolSemester;
use App\Models\Semester;
use App\Models\Specialty;
use App\Models\Studentbatch;
use App\Models\SchoolBranchSetting;
use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AppException;
use Carbon\Carbon;
use App\Services\ApiResponseService;
class SchoolSemesterService
{
        public function createSchoolSemester($semesterData, $currentSchool)
    {
        try {
            DB::beginTransaction();

            $schoolSemester = new SchoolSemester();
            $schoolSemesterId = Str::uuid();

            $specialty = Specialty::where("school_branch_id", $currentSchool->id)
                ->with(['level'])
                ->findorFail($semesterData['specialty_id']);

            $existingSemester = SchoolSemester::where("school_branch_id", $currentSchool->id)
                ->where("specialty_id", $semesterData['specialty_id'])
                ->where("semester_id", $semesterData['semester_id'])
                ->where("school_year", $semesterData['school_year'])
                ->where("student_batch_id", $semesterData['student_batch_id'])
                ->first();

            if ($existingSemester) {
                throw new AppException(
                    "A semester with the same specialty, semester, and school year already exists.",
                    409,
                    "Duplicate Semester",
                    "You are trying to create a semester that already exists. Please check the details and try again.",
                    null
                );
            }

            $schoolSemester->id = $schoolSemesterId;
            $schoolSemester->start_date = $semesterData["start_date"];
            $schoolSemester->end_date = $semesterData["end_date"];

            $now = now();
            $startDate = Carbon::parse($semesterData['start_date']);

            if ($startDate->isPast()) {
                $schoolSemester->status = 'active';
            } else {
                $schoolSemester->status = 'pending';
            }

            $schoolSemester->school_year = $semesterData["school_year"];
            $schoolSemester->semester_id = $semesterData["semester_id"];
            $schoolSemester->specialty_id = $semesterData["specialty_id"];
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

            $autoCreateExamSetting = $this->getSettingByKey($currentSchool->id, "exam.auto_create");
            if ($autoCreateExamSetting->value === true) {
                CreateExamJob::dispatch([
                    'specialty_id' => $semesterData['specialty_id'],
                    'student_batch_id' => $semesterData['student_batch_id'],
                    'semester_id' => $semesterData['semester_id'],
                    'level_id' => $specialty->level_id,
                    'school_year' => $semesterData['school_year']
                ], $currentSchool->id);
            }

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
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new AppException(
                "The specified specialty was not found. Please verify the specialty ID.",
                404,
                "Specialty Not Found",
                "We could not find the specialty associated with the provided ID. Please check and try again.",
                null
            );
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "An error occurred while creating the semester. Please try again.",
                500,
                "Creation Error",
                "We encountered an issue while trying to create the semester. Error: " . $e->getMessage(),
                null
            );
        }
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
                $schoolSemester = SchoolSemester::findOrFail($updateSemester['school_semester_id']);
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
        $schoolSemester = SchoolSemester::where("school_branch_id", $currentSchool->id)
            ->find($schoolSemesterId);
        if (!$schoolSemester) {
            throw new AppException(
                "The school semester you are trying to delete was not found.",
                404,
                "Semester Not Found",
                "The specified school semester does not exist or may have already been deleted.",
                null
            );
        }
        $schoolSemester->delete();
        return $schoolSemester;
    }
    public function bulkDeleteSchoolSemester(array $schoolSemesterIds): array
    {
        $deletedSemesters = [];

        try {
            DB::beginTransaction();

            foreach ($schoolSemesterIds as $schoolSemesterId) {
                $id = $schoolSemesterId['school_semester_id'] ?? null;
                if (!$id) {
                    throw new AppException(
                        "An invalid semester ID was provided in the list.",
                        400,
                        "Invalid Input",
                        "One or more semester IDs were malformed or missing from the request.",
                        null
                    );
                }

                $schoolSemester = SchoolSemester::findOrFail($id);
                $schoolSemester->delete();
                $deletedSemesters[] = $schoolSemester;
            }

            DB::commit();

            return $deletedSemesters;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new AppException(
                "A semester you tried to delete was not found. Please verify the IDs and try again.",
                404,
                "Semester Not Found",
                "We could not find one or more semesters associated with the provided IDs. Please check the list and try again.",
                null
            );
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "An unexpected error occurred while deleting the semesters. Please try again.",
                500,
                "Deletion Error",
                "We encountered an issue while trying to delete the semesters. This may be due to a server problem.",
                null
            );
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

        if ($schoolSemesters->isEmpty()) {
            throw new AppException(
                "No school semesters found for this school branch.",
                404,
                "No Semesters Found",
                "There are no school semesters available. Please create a semester to get started.",
                "/semesters"
            );
        }

        return $schoolSemesters->map(function ($semester) {
            return [
                "id" => $semester->id,
                "start_date" => $semester->start_date,
                "end_date" => $semester->end_date,
                "school_year" => $semester->school_year,
                "status" => $semester->status,
                "student_batch_id" => $semester->student_batch_id,
                'student_batch' => Studentbatch::find($semester->student_batch_id)->name,
                "specialty_id" => $semester->specialty_id,
                "specialty_name" => $semester->specialty->specialty_name ?? null,
                "level_name" => $semester->specialty->level->name ?? null,
                "level" => $semester->specialty->level->level ?? null,
                "level_id" => $semester->specialty->level->id ?? null,
                "semester_id" => $semester->semester_id,
                "timetable_published" => $semester->timetable_published ? "created" : "not created",
                "semester_name" => $semester->semester->name ?? $semester->semester->semester_name ?? null,
            ];
        });
    }
    public function getActiveSchoolSemesters($currentSchool)
    {
        $schoolSemesters = SchoolSemester::where("school_branch_id", $currentSchool->id)
            ->with(['specialty', 'specialty.level', 'semester', 'studentBatch'])
            ->where("status", "active")
            ->get();
        if ($schoolSemesters->isEmpty()) {
            throw new AppException(
                "No active school semesters found for this school branch.",
                404,
                "No Active Semesters Found",
                "There are no active school semesters available. Please create and activate a semester to get started.",
                "/semesters"
            );
        }
        return $schoolSemesters;
    }
    public function getSchoolSemesterDetail($currentSchool, $semesterId)
    {
        $schoolSemesterDetails = SchoolSemester::with(['specialty', 'specialty.level', 'semester', 'studentBatch'])
            ->where("school_branch_id", $currentSchool->id)
            ->find($semesterId);
        if ($schoolSemesterDetails === null) {
            throw new AppException(
                "The school semester you are trying to access was not found.",
                404,
                "Semester Not Found",
                "The specified school semester does not exist or may have been deleted.",
                null
            );
        }
        return $schoolSemesterDetails;
    }
    private function getSettingByKey($schoolBranchId, $key)
    {
        return SchoolBranchSetting::where("school_branch_id", $schoolBranchId)
            ->whereHas('settingDefination', fn($query) => $query->where("key", $key))
            ->first();
    }
}
