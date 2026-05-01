<?php

namespace App\Services\ExamTimetable;

use App\Models\Exams;
use App\Models\ExamTimetable\ExamInvigilator;
use App\Exceptions\AppException;
use App\Models\ExamTimetable\Invigilator;
use App\Models\Schooladmin;
use App\Models\Teacher;
use Illuminate\Support\Str;
class ExamInvigilatorService
{
    public function assignInvigilators(array $payload, object $currentSchool): array
    {
        $examId = $payload["exam_id"];
        $invigilatorIds = $payload["invigilatorIds"] ?? [];

        if (empty($invigilatorIds)) {
            throw new AppException(
                "no_invigilators_provided",
                400,
                "No invigilators specified",
                "Please provide at least one invigilator ID to assign",
                null
            );
        }

        // Verify exam exists and belongs to this school branch
        $exam = Exams::where('school_branch_id', $currentSchool->id)
            ->where('id', $examId)
            ->first();

        if (!$exam) {
            throw new AppException(
                "exam_not_found",
                404,
                "Exam not found",
                "The specified exam does not exist in this school branch",
                null
            );
        }

        // Check if exam is already in progress or completed
        if (in_array($exam->status, ['in_progress', 'completed'])) {
            throw new AppException(
                "exam_already_started",
                409,
                "Cannot modify invigilators",
                "Invigilators cannot be assigned to exams that are in progress or already completed",
                ['exam_status' => $exam->status]
            );
        }

        // Verify all invigilators exist and belong to this school branch
        $validInvigilators = Invigilator::where('school_branch_id', $currentSchool->id)
            ->whereIn('id', $invigilatorIds)
            ->pluck('id')
            ->toArray();

        $invalidInvigilators = array_diff($invigilatorIds, $validInvigilators);

        if (!empty($invalidInvigilators)) {
            throw new AppException(
                "invalid_invigilators",
                400,
                "Invalid invigilators",
                "Some invigilators do not exist in this school branch",
                ['invalid_ids' => $invalidInvigilators]
            );
        }

        // Check for duplicate assignments
        $existingAssignments = ExamInvigilator::where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $examId)
            ->whereIn('invigilator_id', $invigilatorIds)
            ->pluck('invigilator_id')
            ->toArray();

        $newInvigilators = array_diff($invigilatorIds, $existingAssignments);
        $duplicateInvigilators = array_intersect($invigilatorIds, $existingAssignments);

        // Bulk insert new assignments
        $assignmentsToCreate = [];
        foreach ($newInvigilators as $invigilatorId) {
            $assignmentsToCreate[] = [
                "id" => Str::uuid()->toString(),
                'invigilator_id' => $invigilatorId,
                'school_branch_id' => $currentSchool->id,
                'exam_id' => $examId,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        if (!empty($assignmentsToCreate)) {
            ExamInvigilator::insert($assignmentsToCreate);
        }



        return [
            'success' => true,
            'assigned_count' => count($newInvigilators),
            'duplicate_count' => count($duplicateInvigilators),
            'total_requested' => count($invigilatorIds),
            'message' => count($newInvigilators) > 0
                ? "Successfully assigned " . count($newInvigilators) . " invigilator(s)"
                : "No new invigilators were assigned"
        ];
    }
    public function removeAssignedInvigilators(array $payload, object $currentSchool): array
    {
        $examInvigilatorIds = $payload["exam_invigilator_ids"] ?? [];
        $examId = $payload["exam_id"] ?? null;

        if (empty($examInvigilatorIds)) {
            throw new AppException(
                "no_assignments_provided",
                400,
                "No assignments specified",
                "Please provide at least one exam invigilator assignment ID to remove",
                null
            );
        }

        // Verify exam exists if exam_id is provided
        if ($examId) {
            $exam = Exams::where('school_branch_id', $currentSchool->id)
                ->where('id', $examId)
                ->first();

            if (!$exam) {
                throw new AppException(
                    "exam_not_found",
                    404,
                    "Exam not found",
                    "The specified exam does not exist in this school branch",
                    null
                );
            }

            // Check if exam is already in progress or completed
            if (in_array($exam->status, ['in_progress', 'completed'])) {
                throw new AppException(
                    "exam_already_started",
                    409,
                    "Cannot modify invigilators",
                    "Invigilators cannot be removed from exams that are in progress or already completed",
                    ['exam_status' => $exam->status]
                );
            }
        }

        // Build the query
        $query = ExamInvigilator::where('school_branch_id', $currentSchool->id)
            ->whereIn('id', $examInvigilatorIds);

        if ($examId) {
            $query->where('exam_id', $examId);
        }

        // Get the assignments to be deleted for logging
        $assignmentsToDelete = $query->get();

        if ($assignmentsToDelete->isEmpty()) {
            throw new AppException(
                "assignments_not_found",
                404,
                "Assignments not found",
                "None of the specified exam invigilator assignments exist in this school branch",
                ['requested_ids' => $examInvigilatorIds]
            );
        }

        $foundIds = $assignmentsToDelete->pluck('id')->toArray();
        $notFoundIds = array_diff($examInvigilatorIds, $foundIds);

        // Perform deletion
        $deletedCount = ExamInvigilator::where('school_branch_id', $currentSchool->id)
            ->whereIn('id', $foundIds)
            ->delete();


        return [
            'success' => true,
            'removed_count' => $deletedCount,
            'not_found_count' => count($notFoundIds),
            'message' => "Successfully removed {$deletedCount} invigilator assignment(s)"
        ];
    }
    public function getPotAssignableInvigilators(object $currentSchool, string $examId): array
    {
        // Verify exam exists and belongs to this school branch
        $exam = Exams::where('school_branch_id', $currentSchool->id)
            ->where('id', $examId)
            ->first();

        if (!$exam) {
            throw new AppException(
                "exam_not_found",
                404,
                "Exam not found",
                "The specified exam does not exist in this school branch",
                null
            );
        }

        // Get already assigned invigilator IDs for this exam
        $assignedInvigilatorIds = ExamInvigilator::where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $examId)
            ->pluck('invigilator_id')
            ->toArray();

        // Get all invigilators that are not yet assigned to this exam
        $availableInvigilators = Invigilator::where('school_branch_id', $currentSchool->id)
            ->whereNotIn('id', $assignedInvigilatorIds)
            ->with(['invigilatable'])
            ->get();

        return $availableInvigilators->map(function ($invigilator) {
            $invigilatable = $invigilator->invigilatable;

            return [
                'invigilator_id' => $invigilator->id,
                'actor_id' => $invigilatable->id,
                'name' => $invigilatable->name,
                'profile_picture' => $invigilatable->profile_picture,
                'type' => $invigilatable instanceof Teacher ? 'teacher' : 'school_admin',
                'email' => $invigilatable->email ?? null,
                'employee_id' => $invigilatable->employee_id ?? null,
            ];
        })->values()->toArray();
    }
    public function getInvigilatorsExamId(object $currentSchool, string $examId, array $authUser): array
    {
        $authenticatedUser = $authUser['authUser'] ?? null;
        $authUserId = $authenticatedUser?->id;
        $authUserType = $authenticatedUser ? get_class($authenticatedUser) : null;

        $exam = Exams::where('school_branch_id', $currentSchool->id)
            ->where('id', $examId)
            ->first();

        if (!$exam) {
            throw new AppException(
                "exam_not_found",
                404,
                "Exam not found",
                "The specified exam does not exist in this school branch",
                null
            );
        }

        $examInvigilators = ExamInvigilator::where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $examId)
            ->with(['invigilator.invigilatable'])
            ->get();

        if ($examInvigilators->isEmpty()) {
            return [];
        }

        return $examInvigilators->map(function ($examInvigilator) use ($authUserId, $authUserType) {
            $invigilator = $examInvigilator->invigilator;

            if (!$invigilator || !$invigilator->invigilatable) {
                return null;
            }

            $invigilatable = $invigilator->invigilatable;
            $invigilatableType = get_class($invigilatable);

            // Determine type for response
            $type = match ($invigilatableType) {
                Teacher::class => 'teacher',
                Schooladmin::class => 'school_admin',
                default => 'unknown'
            };

            return [
                'exam_invigilator_id' => $examInvigilator->id,
                'invigilator_id' => $invigilator->id,
                'actor_id' => $invigilatable->id,
                'name' => $invigilatable->name,
                'profile_picture' => $invigilatable->profile_picture,
                'type' => $type,
                'me' => ($authUserType === $invigilatableType && $authUserId === $invigilatable->id)
            ];
        })->filter()
            ->values()
            ->toArray();
    }
}
