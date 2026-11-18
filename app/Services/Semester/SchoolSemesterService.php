<?php

namespace App\Services\Semester;

use App\Constant\Enums\SystemState;
use App\Jobs\DataCreationJob\CreateInstructorAvailabilityJob;
use App\Models\SchoolSemester;
use App\Models\SchoolBranchSetting;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AppException;
use Carbon\Carbon;
use App\Services\ApiResponseService;
use App\Events\Actions\StudentActionEvent;
use App\Jobs\Exam\AutoCreateSemesterExamsJob;
use App\Jobs\JointCourse\CreateJointCourseSemesterJob;
use App\Models\AcademicYear\SchoolAcademicYear;

class SchoolSemesterService
{
    public function __construct(
        private SemesterAutoCreationService $semesterAutoCreation,
    ) {}

    public function createSchoolSemester(array $semesterData, object $currentSchool, $authAdmin)
    {
        $schoolYear = SchoolAcademicYear::where("school_branch_id", $currentSchool->id)
            ->with(['systemAcademicYear', 'specialty.level'])
            ->find($semesterData['school_year_id']);

        if (!$schoolYear) {
            throw new AppException(
                "The specified school academic year was not found. Please verify the school year ID.",
                404,
                "School Year Not Found",
                "We could not find the school academic year associated with the provided ID. Please check and try again.",
                null
            );
        }

        $startDate = Carbon::parse($semesterData['start_date'])->startOfDay();
        $endDate = Carbon::parse($semesterData['end_date'])->startOfDay();

        if ($endDate->lte($startDate)) {
            throw new AppException(
                "The semester end date must be after its start date.",
                400,
                "Invalid Semester Dates",
                "Please make sure the semester ends after it starts.",
                null
            );
        }

        if (
            $startDate->lt(Carbon::parse($schoolYear->start_date)->startOfDay()) ||
            $endDate->gt(Carbon::parse($schoolYear->end_date)->startOfDay())
        ) {
            throw new AppException(
                "The semester dates must fall within the start and end dates of the associated school academic year.",
                400,
                "Invalid Semester Dates",
                "Please ensure that the semester's start and end dates are within the range of the school academic year's start and end dates.",
                null
            );
        }

        $existingSemester = SchoolSemester::where("school_branch_id", $currentSchool->id)
            ->where("semester_id", $semesterData['semester_id'])
            ->where("school_year_id", $schoolYear->id)
            ->exists();

        if ($existingSemester) {
            throw new AppException(
                "This semester already exists for the selected school year.",
                409,
                "Duplicate Semester",
                "You are trying to create a semester that already exists. Please check the details and try again.",
                null
            );
        }

        try {
            // The original and its automatically created copies commit or roll back together.
            [$schoolSemester, $autoCreated] = DB::transaction(function () use ($semesterData, $schoolYear, $currentSchool, $startDate, $endDate) {
                $schoolSemester = new SchoolSemester();
                $schoolSemester->start_date = $startDate->toDateString();
                $schoolSemester->end_date = $endDate->toDateString();
                $schoolSemester->school_year_id = $schoolYear->id;
                $schoolSemester->semester_id = $semesterData["semester_id"];
                $schoolSemester->school_branch_id = $currentSchool->id;
                $schoolSemester->timetable_published = false;
                $schoolSemester->save();

                $autoCreated = $this->semesterAutoCreation->createFor($schoolSemester, $schoolYear);

                return [$schoolSemester, $autoCreated];
            });
        } catch (Throwable $e) {
            Log::error("Failed to create school semester", [
                'school_branch_id' => $currentSchool->id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw new AppException(
                "An error occurred while creating the semester. Please try again.",
                500,
                "Creation Error",
                "We encountered an issue while trying to create the semester. Please try again later.",
                null
            );
        }

        // Follow-up work runs only once the semester is committed, and a failure
        // there must not turn a successful creation into an error response.
        foreach (collect([$schoolSemester])->concat($autoCreated) as $createdSemester) {
            try {
                CreateInstructorAvailabilityJob::dispatch($currentSchool->id, $createdSemester->id);
                CreateJointCourseSemesterJob::dispatch($createdSemester->id, $currentSchool);
                // Creates the semester's CA and Exam when exam.auto_create is on.
                AutoCreateSemesterExamsJob::dispatch($currentSchool->id, $createdSemester->id);
            } catch (Throwable $e) {
                Log::error("School semester {$createdSemester->id} was created but its follow-up jobs failed", [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
        }

        return $schoolSemester->setRelation('autoCreatedSemesters', $autoCreated);
    }
    /**
     * Applies the changes SchoolSemesterUpdatePolicy allows, and returns the
     * updated semester with its rules after the change.
     */
    public function updateSchoolSemester(array $semesterData, object $currentSchool, string $schoolSemesterId, $authAdmin)
    {
        $schoolSemester = $this->findForBranch($currentSchool, $schoolSemesterId);
        $policy = app(SchoolSemesterUpdatePolicy::class);

        DB::transaction(function () use ($schoolSemester, $policy, $semesterData) {
            // Locked so two admins editing at once are checked one after the other.
            $locked = SchoolSemester::lockForUpdate()->find($schoolSemester->id);
            $locked->update($policy->validatedChanges($locked, $semesterData));
        });

        $schoolSemester->refresh();
        StudentActionEvent::dispatch([
            'schoolBranch' => $currentSchool->id,
            'specialtyIds'   => [$schoolSemester->schoolYear?->specialty_id],
            'feature'      => 'semesterUpdate',
            'message'      => 'Semester  Updated',
            'data'         => $schoolSemester,
        ]);

        return [
            ...$schoolSemester->toArray(),
            'update_rules' => $policy->describe($schoolSemester),
        ];
    }

    /**
     * Updates several semesters under the same rules as a single update. All
     * or nothing: one refused change rolls back the whole batch, and the
     * error names the semester. Rows that change nothing are skipped.
     */
    public function bulkUpdateSchoolSemester(array $updateSemesterList, object $currentSchool, $authAdmin)
    {
        $policy = app(SchoolSemesterUpdatePolicy::class);

        return DB::transaction(function () use ($updateSemesterList, $currentSchool, $policy) {
            $result = [];
            foreach ($updateSemesterList as $updateSemester) {
                $schoolSemester = SchoolSemester::where("school_branch_id", $currentSchool->id)
                    ->with('semester')
                    ->lockForUpdate()
                    ->find($updateSemester['school_semester_id'] ?? null);

                if (!$schoolSemester) {
                    throw new AppException(
                        "School semester not found.",
                        404,
                        "Semester Not Found",
                        "One of the semesters no longer exists or belongs to another school branch."
                    );
                }

                try {
                    $changes = $policy->validatedChanges($schoolSemester, $updateSemester, true);
                } catch (AppException $e) {
                    $name = $schoolSemester->semester?->name ?? 'A semester';
                    throw new AppException($e->getMessage(), $e->getCode(), $e->getTitle(), "{$name}: {$e->getDescription()}");
                }

                if ($changes) {
                    $schoolSemester->update($changes);
                }
                $result[] = $schoolSemester;
            }
            return $result;
        });
    }

    public function getUpdateRules(object $currentSchool, string $schoolSemesterId): array
    {
        return app(SchoolSemesterUpdatePolicy::class)->describe($this->findForBranch($currentSchool, $schoolSemesterId));
    }

    private function findForBranch(object $currentSchool, string $schoolSemesterId): SchoolSemester
    {
        $schoolSemester = SchoolSemester::where("school_branch_id", $currentSchool->id)->find($schoolSemesterId);
        if (!$schoolSemester) {
            throw new AppException(
                "The school semester was not found.",
                404,
                "Semester Not Found",
                "The specified school semester does not exist or may have already been deleted."
            );
        }
        return $schoolSemester;
    }

    public function deleteSchoolSemester(string $schoolSemesterId, object $currentSchool, $authAdmin)
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
    public function bulkDeleteSchoolSemester(array $schoolSemesterIds, object $currentSchool, $authAdmin): array
    {
        $deletedSemesters = [];
        $specialtyIds = [];
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
                $specialtyIds[] = $schoolSemester->id;
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
    public function getSchoolSemesters(object $currentSchool)
    {
        $schoolSemesters = SchoolSemester::query()
            ->where('school_branch_id', $currentSchool->id)
            ->select([
                'id',
                'start_date',
                'end_date',
                'semester_id',
                'timetable_published',
                'school_year_id',
            ])
            ->with([
                'schoolYear.specialty:id,specialty_name,level_id',
                'schoolYear.specialty.level:id,level,name',
                'semester:id,name,count',
                'schoolYear.systemAcademicYear:id,name,year_start,year_end',
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

        $today = now()->startOfDay();

        return $schoolSemesters->map(function ($semester) use ($today) {
            $start = $semester->start_date ? Carbon::parse($semester->start_date)->startOfDay() : null;
            $end = $semester->end_date ? Carbon::parse($semester->end_date)->endOfDay() : null;

            $status = null;
            if ($start && $today->lt($start)) {
                $status = SystemState::UPCOMING;
            } elseif ($start && $end && $today->betweenIncluded($start, $end)) {
                $status = SystemState::ONGOING;
            } elseif ($end && $today->gt($end)) {
                $status = SystemState::FINISHED;
            }

            return [
                "id" => $semester->id,
                "start_date" => $start ? $start->format('Y-m-d') : null,
                "end_date" => $end ? $end->format('Y-m-d') : null,
                "school_year" => $semester->schoolYear?->systemAcademicYear?->name,
                "status" => $status,
                "specialty_name" => $semester->schoolYear->specialty?->specialty_name,
                "level_name" => $semester->schoolYear->specialty?->level?->name,
                "level_number" => $semester->schoolYear->specialty?->level?->level,
                "timetable_published" => $semester->timetable_published ? "created" : "not created",
                "semester_name" => $semester->semester?->name ?? $semester->semester?->semester_name,
                "created_at" => $semester->created_at,
                "updated_at" => $semester->updated_at
            ];
        });
    }
    public function getActiveSchoolSemesters(object $currentSchool)
    {
        $schoolSemesters = SchoolSemester::where("school_branch_id", $currentSchool->id)
            ->with(['specialty', 'specialty.level', 'semester', 'studentBatch'])
            ->where("start_date", "<=", Carbon::now())
            ->where("end_date", ">=", Carbon::now())
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
    public function getSchoolSemesterDetail(object $currentSchool, string $semesterId)
    {
        $schoolSemesterDetails = SchoolSemester::with(['schoolYear.specialty.level', 'semester'])
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
    private function getSettingByKey(string $schoolBranchId, string $key)
    {
        return SchoolBranchSetting::where("school_branch_id", $schoolBranchId)
            ->whereHas('settingDefination', fn($query) => $query->where("key", $key))
            ->first();
    }
}
