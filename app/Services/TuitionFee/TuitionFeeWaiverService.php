<?php

namespace App\Services\TuitionFee;

use App\Models\FeeWaiver;
use Exception;
use Throwable;
use App\Exceptions\AppException;

class TuitionFeeWaiverService
{
    public function createFeeWaiver(array $feeWaiverData, $currentSchool)
    {
        if (strtotime($feeWaiverData['start_date']) > strtotime($feeWaiverData['end_date'])) {
            throw new AppException(
                "The start date cannot be after the end date for the fee waiver period.",
                400,
                "Invalid Date Range 🗓️",
                "Please ensure the start date occurs before or on the end date.",
                null
            );
        }

        $studentId = $feeWaiverData['student_id'] ?? null;
        $specialtyId = $feeWaiverData['specialty_id'] ?? null;
        $levelId = $feeWaiverData['level_id'] ?? null;
        $currentSchoolId = $currentSchool->id;
        $waiverDescription = $feeWaiverData['description'];

        $query = FeeWaiver::where('school_branch_id', $currentSchoolId)
            ->where('student_id', $studentId)
            ->where('level_id', $levelId)
            ->where('specialty_id', $specialtyId);

        if ($query->exists()) {
            throw new AppException(
                "A fee waiver already exists for this exact combination of Student, Level, and Specialty in this school branch.",
                409,
                "Duplicate Fee Waiver 📛",
                "A waiver has already been applied to this student for this specific program and academic period.",
                null
            );
        }

        try {
            $feeWaiver = new FeeWaiver();
            $feeWaiver->start_date = $feeWaiverData['start_date'];
            $feeWaiver->end_date = $feeWaiverData['end_date'];
            $feeWaiver->description = $waiverDescription;
            $feeWaiver->specialty_id = $specialtyId;
            $feeWaiver->level_id = $levelId;
            $feeWaiver->student_id = $studentId;
            $feeWaiver->school_branch_id = $currentSchoolId;

            $feeWaiver->save();

            return $feeWaiver;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to create fee waiver. Description: '{$waiverDescription}'. Error: " . $e->getMessage(),
                500,
                "Waiver Creation Failed 🛑",
                "A system error occurred while trying to save the new fee waiver. Please try again or contact support.",
                null
            );
        }
    }
    public function updateFeeWaiver(array $feeWaiverData, $currentSchool, string $feeWaiverId)
    {
        try {
            $waiverExists = FeeWaiver::where("school_branch_id", $currentSchool->id)->find($feeWaiverId);

            if (!$waiverExists) {
                throw new AppException(
                    "Fee Waiver ID '{$feeWaiverId}' not found for school branch ID '{$currentSchool->id}'.",
                    404, // Not Found
                    "Waiver Not Found 🔎",
                    "The fee waiver record you are trying to update could not be found. It may have been deleted.",
                    null
                );
            }

            $filteredEmptyEntries = array_filter($feeWaiverData);

            $startDate = $filteredEmptyEntries['start_date'] ?? $waiverExists->start_date;
            $endDate = $filteredEmptyEntries['end_date'] ?? $waiverExists->end_date;

            if (strtotime($startDate) > strtotime($endDate)) {
                throw new AppException(
                    "The start date cannot be after the end date for the fee waiver period.",
                    400,
                    "Invalid Date Range 🗓️",
                    "Please ensure the start date occurs before or on the end date.",
                    null
                );
            }

            if (
                isset($filteredEmptyEntries['student_id']) ||
                isset($filteredEmptyEntries['level_id']) ||
                isset($filteredEmptyEntries['specialty_id'])
            ) {
                $studentId = $filteredEmptyEntries['student_id'] ?? $waiverExists->student_id;
                $levelId = $filteredEmptyEntries['level_id'] ?? $waiverExists->level_id;
                $specialtyId = $filteredEmptyEntries['specialty_id'] ?? $waiverExists->specialty_id;

                $query = FeeWaiver::where('school_branch_id', $currentSchool->id)
                    ->where('student_id', $studentId)
                    ->where('level_id', $levelId)
                    ->where('specialty_id', $specialtyId)
                    ->where('id', '!=', $feeWaiverId);

                if ($query->exists()) {
                    throw new AppException(
                        "The updated waiver configuration would duplicate an existing waiver for the same student, level, and specialty.",
                        409,
                        "Duplicate Fee Waiver 📛",
                        "A waiver with this exact combination of Student, Level, and Specialty already exists.",
                        null
                    );
                }
            }

            $waiverExists->update($filteredEmptyEntries);
            return $waiverExists;
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to update fee waiver ID '{$feeWaiverId}'. Error: " . $e->getMessage(),
                500,
                "Waiver Update Failed 🛑",
                "A system error occurred while trying to save the waiver changes. Please try again or contact support.",
                null
            );
        }
    }
    public function deleteFeeWaiver(string $feeWaiverId, $currentSchool)
    {
        $waiverDescription = 'Unknown Waiver';

        try {
            $waiverExists = FeeWaiver::where("school_branch_id", $currentSchool->id)->find($feeWaiverId);

            if (!$waiverExists) {
                throw new AppException(
                    "Fee Waiver ID '{$feeWaiverId}' not found for school branch ID '{$currentSchool->id}'.",
                    404,
                    "Waiver Not Found 🔎",
                    "The fee waiver record you are trying to delete could not be found. It may have already been deleted.",
                    null
                );
            }

            $waiverDescription = $waiverExists->description;

            $waiverExists->delete();

            return $waiverExists;
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            $message = "Failed to delete fee waiver '{$waiverDescription}' (ID: {$feeWaiverId}). Error: " . $e->getMessage();

            if (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                throw new AppException(
                    $message,
                    409,
                    "Deletion Blocked 🔒",
                    "Cannot delete the fee waiver as it is currently linked to an active fee structure or transaction. Please remove all associations first.",
                    null
                );
            }

            throw new AppException(
                $message,
                500,
                "Deletion Failed 🛑",
                "A system error occurred during fee waiver deletion. Please try again or contact support.",
                null
            );
        }
    }
    public function getFeeWaiverByStudent(string $studentId, $currentSchool)
    {
        try {
            $getFeeWaiver = FeeWaiver::where("school_branch_id", $currentSchool->id)
                ->where("student_id", $studentId)
                ->with(['specialty', 'level', 'student'])
                ->get();

            if ($getFeeWaiver->isEmpty()) {
                throw new AppException(
                    "No fee waivers found for student ID '{$studentId}' at school branch ID '{$currentSchool->id}'.",
                    404,
                    "No Waivers Found 🔎",
                    "This student currently has no fee waiver records associated with them.",
                    null
                );
            }

            return $getFeeWaiver;
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to retrieve fee waivers for student ID '{$studentId}'. Error: " . $e->getMessage(),
                500,
                "Data Retrieval Failed 🛑",
                "A system error occurred while trying to load the student's fee waivers. Please try again or contact support.",
                null
            );
        }
    }
    public function getAllFeeWaiver($currentSchool)
    {
        try {
            $getFeeWaiver = FeeWaiver::where("school_branch_id", $currentSchool->id)
                ->with(['specialty', 'level', 'student'])
                ->get();

            if ($getFeeWaiver->isEmpty()) {
                throw new AppException(
                    "No fee waivers found for school branch ID '{$currentSchool->id}'.",
                    404,
                    "No Waivers Found 🔎",
                    "There are currently no fee waiver records defined for your school branch.",
                    null
                );
            }

            return $getFeeWaiver;
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to retrieve all fee waivers. Error: " . $e->getMessage(),
                500,
                "Data Retrieval Failed 🛑",
                "A system error occurred while trying to load the fee waiver list. Please try again or contact support.",
                null
            );
        }
    }
}
