<?php

namespace App\Services\ResitExam;

use App\Models\ResitExam;
use App\Models\ResitCandidates;
use App\Models\GradeScale\SchoolGradeScaleCategory;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Exceptions\AppException;
use App\Events\Actions\AdminActionEvent;
use Carbon\Carbon;

class ResitExamService
{
    public function updateResitExam(string $resitExamId, array $data, object $currentSchool): ResitExam
    {
        try {
            $exam = ResitExam::where("school_branch_id", $currentSchool->id)
                ->find($resitExamId);

            if (!$exam) {
                throw new AppException(
                    "Resit exam not found.",
                    404,
                    "Exam Unavailable",
                    "The requested resit exam could not be found or may have been removed.",
                    null
                );
            }

            $updateData = array_intersect_key($data, array_flip(['start_date', 'end_date', 'max_score']));

            if (empty($updateData)) {
                throw new AppException(
                    "No valid fields provided for update.",
                    422,
                    "Invalid Update Data",
                    "Please provide at least one valid field to update (start date, end date, or maximum score).",
                    null
                );
            }

            $exam->update($updateData);

            return $exam;
        } catch (AppException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new AppException(
                "An unexpected issue occurred.",
                500,
                "Request Failed",
                "We were unable to update the resit exam details. Please try again later.",
                null
            );
        }
    }
    public function bulkUpdateResitExam(array $examUpdateList, object $currentSchool, object $authAdmin)
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
                ->with([
                    'schoolYear.specialty.level',
                    'resitCandidates',
                    'examType.semesters',
                    'examGradeScale',
                    'schoolYear.systemAcademicYear'
                ])
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
    public function examDetails(object $currentSchool, string $resitExamId): ResitExam
    {
        try {
            $exam = ResitExam::where("school_branch_id", $currentSchool->id)
                ->with([
                    'schoolYear.specialty.level',
                    'resitCandidates',
                    'examType.semesters',
                    'examGradeScale',
                    'schoolYear.systemAcademicYear'
                ])
                ->find($resitExamId);

            if (!$exam) {
                throw new AppException(
                    "Requested resit exam could not be found.",
                    404,
                    "Exam Unavailable",
                    "The requested resit exam details are currently unavailable or may have been removed.",
                    null
                );
            }

            if ($exam->start_date && $exam->end_date) {
                $now = Carbon::now();
                $startDate = Carbon::parse($exam->start_date)->startOfDay();
                $endDate = Carbon::parse($exam->end_date)->endOfDay();

                if ($now->lt($startDate)) {
                    $status = 'upcoming';
                } elseif ($now->between($startDate, $endDate)) {
                    $status = 'active';
                } else {
                    $status = 'finished';
                }
            } else {
                $status = 'unscheduled';
            }

            $exam->status = $status;

            return $exam;
        } catch (AppException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new AppException(
                "An unexpected issue occurred.",
                500,
                "Request Failed",
                "We were unable to retrieve the exam details. Please try again later.",
                null
            );
        }
    }
    public function deleteResitExam(string $resitExamId, object $currentSchool): array
    {
        DB::beginTransaction();

        try {
            $resitExam = ResitExam::where("school_branch_id", $currentSchool->id)
                ->find($resitExamId);

            if (!$resitExam) {
                throw new AppException(
                    "Resit exam not found.",
                    404,
                    "Exam Unavailable",
                    "The requested resit exam could not be found or has already been removed.",
                    null
                );
            }

            $resitExamId = $resitExam->id;

            ResitCandidates::where("school_branch_id", $currentSchool->id)
                ->where("resit_exam_id", $resitExamId)
                ->delete();

            $resitExam->delete();

            DB::commit();

            return [
                'id'      => $resitExamId,
                'deleted' => true,
            ];
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw new AppException(
                "An unexpected issue occurred.",
                500,
                "Request Failed",
                "We were unable to delete the resit exam. Please try again later.",
                null
            );
        }
    }
    public function bulkDeleteResitExam(array $resitExamIds, object $currentSchool, object $authAdmin)
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
    public function bulkAddGradeScale(array $examGradingList, object $currentSchool, object $authAdmin): array
    {
        $updatedExams = [];
        DB::beginTransaction();

        try {
            foreach ($examGradingList as $item) {
                $gradeScaleCategoryId = $item['grade_scale_category_id'] ?? null;
                $resitExamId = $item['resit_exam_id'] ?? null;

                $gradeScaleCategory = SchoolGradeScaleCategory::where("school_branch_id", $currentSchool->id)
                    ->with(['schoolGradeScale', 'systemGradeCategory'])
                    ->find($gradeScaleCategoryId);

                if (!$gradeScaleCategory) {
                    throw new AppException(
                        "Invalid grading configuration selection.",
                        404,
                        "Grading Configuration Unavailable",
                        "The selected grading configuration is not available. Please refresh and try again.",
                        null
                    );
                }

                if ($gradeScaleCategory->schoolGradeScale->isEmpty()) {
                    throw new AppException(
                        "Incomplete grading configuration setup.",
                        400,
                        "Incomplete Configuration",
                        "This grading configuration cannot be applied because its setup is incomplete.",
                        null
                    );
                }

                $exam = ResitExam::where("school_branch_id", $currentSchool->id)
                    ->with(['examType'])
                    ->find($resitExamId);

                if (!$exam) {
                    throw new AppException(
                        "Target exam not found.",
                        404,
                        "Exam Unavailable",
                        "One or more selected exams could not be found. Please check your selection.",
                        null
                    );
                }

                if (strtolower($exam->examType->type) !== strtolower($gradeScaleCategory->systemGradeCategory->exam_type)) {
                    throw new AppException(
                        "Incompatible exam and grading configuration types.",
                        422,
                        "Incompatible Selection",
                        "The selected grading configuration cannot be applied to this type of exam.",
                        null
                    );
                }

                if ((float) $exam->max_score !== (float) $gradeScaleCategory->max_score) {
                    throw new AppException(
                        "Score limit mismatch detected.",
                        422,
                        "Score Mismatch",
                        "The maximum score of the exam does not match the configured limit for this grading scale.",
                        null
                    );
                }

                $exam->grades_category_id = $gradeScaleCategory->id;
                $exam->grading_added = true;
                $exam->save();

                $updatedExams[] = [
                    'id'                 => $exam->id,
                    'title'              => $exam->title ?? $exam->name ?? null,
                    'grades_category_id' => $exam->grades_category_id,
                    'grading_added'      => $exam->grading_added,
                ];
            }

            DB::commit();

            return $updatedExams;
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw new AppException(
                "An unexpected issue occurred.",
                500,
                "Request Failed",
                "We were unable to apply the grading configuration to the selected exams. Please try again.",
                null
            );
        }
    }
    public function addGradeScale(string $resitExamId, object $currentSchool, string $gradeScaleCategoryId): ResitExam
    {
        try {
            $gradeScaleCategory = SchoolGradeScaleCategory::where("school_branch_id", $currentSchool->id)
                ->with(['schoolGradeScale', 'systemGradeCategory'])
                ->find($gradeScaleCategoryId);

            if (!$gradeScaleCategory) {
                throw new AppException(
                    "The selected grading configuration was not found.",
                    404,
                    "Grading Configuration Not Found",
                    "We could not find the specified grading configuration for this school. Please verify the ID and try again.",
                    null
                );
            }

            if ($gradeScaleCategory->schoolGradeScale->isEmpty()) {
                throw new AppException(
                    "The selected grading configuration has not been set up completely.",
                    400,
                    "Incomplete Configuration",
                    "You cannot apply an incomplete grading configuration to an exam. Please complete the setup and try again.",
                    null
                );
            }

            $exam = ResitExam::where("school_branch_id", $currentSchool->id)
                ->with(['examType'])
                ->find($resitExamId);

            if (!$exam) {
                throw new AppException(
                    "The exam you are trying to grade was not found.",
                    404,
                    "Exam Not Found",
                    "We could not find the exam with the provided ID for this school. Please verify the ID and try again.",
                    null
                );
            }

            if (strtolower($exam->examType->type) !== strtolower($gradeScaleCategory->systemGradeCategory->exam_type)) {
                throw new AppException(
                    "Mismatched Exam Type and Grading Category",
                    422,
                    "Incompatible Grading Category",
                    "The selected grading configuration type does not match the type of this exam ({$exam->examType->type}).",
                    null
                );
            }

            if ((float) $exam->max_score !== (float) $gradeScaleCategory->max_score) {
                throw new AppException(
                    "Mismatched Maximum Score",
                    422,
                    "Score Limit Mismatch",
                    "The maximum score of the exam ({$exam->max_score}) does not match the maximum score configured for this grading scale ({$gradeScaleCategory->max_score}).",
                    null
                );
            }

            $exam->grades_category_id = $gradeScaleCategory->id;
            $exam->save();


            return $exam;
        } catch (AppException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new AppException(
                $e->getMessage(),
                500,
                "Server Error",
                "An unexpected error occurred while attaching the grading configuration to the resit exam. Please try again later.",
                null
            );
        }
    }
}
