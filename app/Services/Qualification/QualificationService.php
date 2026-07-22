<?php

namespace App\Services\Qualification;
use App\Models\Qualification;
use App\Exceptions\AppException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class QualificationService
{
    public function getAllQualifications(): Collection
    {
        return Qualification::orderBy('name')->get();
    }
    public function getActiveQualifications(): Collection
    {
        return Qualification::where('status', 'active')
            ->orderBy('name')
            ->get();
    }
    private function getQualificationById(string $id): Qualification
    {
        try {
            return Qualification::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "qualification_not_found",
                404,
                "Qualification Not Found",
                "The requested qualification could not be found."
            );
        }
    }
    public function getQualificationDetails(string $id): array
    {
        $qualification = $this->getQualificationById($id);

        return [
            'id' => $qualification->id,
            'name' => $qualification->name,
            'abbreviation' => $qualification->abbreviation,
            'level' => $qualification->level,
            'status' => $qualification->status,
            'created_at' => $qualification->created_at,
            'updated_at' => $qualification->updated_at,
        ];
    }
    public function createQualification(array $data): Qualification
    {
        try {
            return Qualification::create($data);
        } catch (\Exception $e) {
            throw new AppException(
                "qualification_creation_failed",
                500,
                "Creation Failed",
                "Failed to create the qualification. Please try again."
            );
        }
    }
    public function updateQualification(string $id, array $data): Qualification
    {
        $qualification = $this->getQualificationById($id);

        try {
            $qualification->update($data);
            return $qualification->fresh();
        } catch (\Exception $e) {
            throw new AppException(
                "qualification_update_failed",
                500,
                "Update Failed",
                "Failed to update the qualification. Please try again."
            );
        }
    }
    public function deleteQualification(string $id): bool
    {
        $qualification = $this->getQualificationById($id);

        try {
            return $qualification->delete();
        } catch (\Exception $e) {
            throw new AppException(
                "qualification_deletion_failed",
                500,
                "Deletion Failed",
                "Failed to delete the qualification. Please try again."
            );
        }
    }
    public function activateQualification(string $id): Qualification
    {
        $qualification = $this->getQualificationById($id);

        if ($qualification->status === 'active') {
            throw new AppException(
                "qualification_already_active",
                409,
                "Already Active",
                "This qualification is already active."
            );
        }

        try {
            $qualification->update(['status' => 'active']);
            return $qualification->fresh();
        } catch (\Exception $e) {
            throw new AppException(
                "qualification_activation_failed",
                500,
                "Activation Failed",
                "Failed to activate the qualification. Please try again."
            );
        }
    }
    public function deactivateQualification(string $id): Qualification
    {
        $qualification = $this->getQualificationById($id);

        if ($qualification->status === 'inactive') {
            throw new AppException(
                "qualification_already_inactive",
                409,
                "Already Inactive",
                "This qualification is already inactive."
            );
        }

        try {
            $qualification->update(['status' => 'inactive']);
            return $qualification->fresh();
        } catch (\Exception $e) {
            throw new AppException(
                "qualification_deactivation_failed",
                500,
                "Deactivation Failed",
                "Failed to deactivate the qualification. Please try again."
            );
        }
    }
}
