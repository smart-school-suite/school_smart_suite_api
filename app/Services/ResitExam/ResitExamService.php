<?php

namespace App\Services\ResitExam;

use App\Jobs\DataCreationJob\CreateResitCandidateJob;
use App\Models\ResitExam;
use App\Models\SchoolGradesConfig;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Exceptions\AppException;
use App\Services\ApiResponseService;
use App\Events\Actions\AdminActionEvent;

class ResitExamService
{
    public function updateResitExam($updateData, string $resitExamId, $currentSchool, $authAdmin)
    {
        DB::beginTransaction();
        try {
            $resit = ResitExam::where("school_branch_id", $currentSchool->id)
                ->find($resitExamId);

            if (!$resit) {
                return ApiResponseService::error("Resit Not found", null, 404);
            }

            $resit->update($updateData);
            dispatch(new CreateResitCandidateJob($resit));
            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.resitExam.update"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "resitExamManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $resit,
                    "message" => "Resit Exam Updated",
                ]
            );
            return $resit;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkUpdateResitExam($examUpdateList, $currentSchool, $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($examUpdateList as $examUpdate) {
                $resitExam = ResitExam::where("school_branch_id", $currentSchool->id)
                    ->findOrFail($examUpdate['resit_exam_id']);
                $filterData = array_filter($examUpdate);
                $resitExam->update($filterData);
                $result[] = [
                    $resitExam
                ];
            }
            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.resitExam.update"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "resitExamManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $result,
                    "message" => "Resit Exam Updated",
                ]
            );
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getAllResitExams(object $currentSchool)
    {
        try {
            $resitExams = ResitExam::where("school_branch_id", $currentSchool->id)
                ->with(['specialty', 'level', 'examType', 'semester'])
                ->get();

            if ($resitExams->isEmpty()) {
                throw new AppException(
                    "No resit exams were found for this school branch.",
                    404,
                    "No Resit Exams Found",
                    "The system could not find any resit exams associated with your school branch. Please ensure resit exams have been configured.",
                    null
                );
            }

            return $resitExams;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while retrieving the resit exams.",
                500,
                "Internal Server Error",
                "A server-side issue prevented the resit exams from being retrieved successfully.",
                null
            );
        }
    }
    public function addExamGrading(string $resitExamId, $currentSchool, $gradesConfigId, $authAdmin)
    {
        $gradesConfig = SchoolGradesConfig::where("school_branch_id", $currentSchool->id)->find($gradesConfigId);
        if (!$gradesConfig) {
            return ApiResponseService::error("Exam Grades Configuration Not Found", null, 404);
        }
        $exam = ResitExam::where("school_branch_id", $currentSchool->id)->find($resitExamId);
        if (!$exam) {
            return ApiResponseService::error("Exam Not Found", null, 404);
        }
        $exam->grades_category_id = $gradesConfig->grades_category_id;
        $exam->grading_added = true;
        $exam->save();
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.resitExam.gradeScaleAdd"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "resitExamManagement",
                "authAdmin" => $authAdmin,
                "data" => $exam,
                "message" => "Resit Exam Grade Scale Added",
            ]
        );
        return $exam;
    }
    public function examDetails($currentSchool, string $resitExamId)
    {
        $exam = ResitExam::where("school_branch_id", $currentSchool->id)
            ->with(['examtype', 'semester', 'specialty', 'level',])
            ->find($resitExamId);
        if (!$exam) {
            return ApiResponseService::error("Exam not found", null, 404);
        }
        return $exam;
    }
    public function deleteResitExam($resitExamId, $currentSchool, $authAdmin)
    {
        $resitExam = ResitExam::where("school_branch_id", $currentSchool->id)
            ->findOrFail($resitExamId);
        $resitExam->delete();
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.resitExam.delete"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "resitExamManagement",
                "authAdmin" => $authAdmin,
                "data" => $resitExam,
                "message" => "Resit Exam Deleted",
            ]
        );
        return $resitExam;
    }
    public function bulkDeleteResitExam($resitExamIds, $currentSchool, $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($resitExamIds as $resitExamId) {
                $resitExam = ResitExam::where("school_branch_id", $currentSchool->id)
                    ->findOrFail($resitExamId['resit_exam_id']);
                $result[] = $resitExam;
            }
            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.resitExam.delete"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "resitExamManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $result,
                    "message" => "Resit Exam Deleted",
                ]
            );
            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function bulkAddExamGrading($examGradingList, $currentSchool, $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($examGradingList as $examGrading) {
                $gradesConfig = SchoolGradesConfig::where("school_branch_id", $currentSchool->id)->find($examGradingList['grades_config_Id']);
                if (!$gradesConfig) {
                    return ApiResponseService::error("Exam Grades Configuration Not Found", null, 404);
                }
                $exam = ResitExam::where("school_branch_id", $currentSchool->id)->find($examGrading['resit_exam_id']);
                if (!$exam) {
                    return ApiResponseService::error("Exam Not Found", null, 404);
                }
                $exam->grades_category_id = $gradesConfig->grades_category_id;
                $exam->grading_added = true;
                $exam->save();
                $result[] = [
                    $gradesConfig,
                    $exam,
                ];
            }
            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.resitExam.gradeScaleAdd"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "resitExamManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $exam,
                    "message" => "Resit Exam Grade Scale Added",
                ]
            );
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
