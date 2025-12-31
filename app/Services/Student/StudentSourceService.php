<?php

namespace App\Services\Student;

use App\Exceptions\AppException;
use App\Models\StudentSource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StudentSourceService
{
    public function createStudentSource($data)
    {
        $existingStudentSource = StudentSource::where('name', $data['name'])->first();

        if ($existingStudentSource) {
            throw new AppException(
                "A student source with the same name already exists",
                409,
                "Duplicate Student Source",
                "{$data['name']} already exists. Please try another name."
            );
        }

        return StudentSource::create($data);
    }

    public function getAllStudentSource()
    {
        return StudentSource::all();
    }

    public function getActiveStudentSource()
    {
        return StudentSource::where('status', 'active')->get();
    }

    public function getStudentSourceById($studentSourceId)
    {
        $studentSource = StudentSource::find($studentSourceId);

        if (!$studentSource) {
            throw new ModelNotFoundException("Student source with ID {$studentSourceId} not found.");
        }

        return $studentSource;
    }

    public function updateStudentSource($updateData, $studentSourceId)
    {
        $studentSource = StudentSource::find($studentSourceId);

        if (!$studentSource) {
            throw new ModelNotFoundException("Student source with ID {$studentSourceId} not found.");
        }

        $cleanData = array_filter($updateData, function ($value) {
            return $value !== '' && $value !== null;
        });

        if (isset($cleanData['name']) && $cleanData['name'] !== $studentSource->name) {
            $existing = StudentSource::where('name', $cleanData['name'])
                ->where('id', '!=', $studentSourceId)
                ->first();

            if ($existing) {
                throw new AppException(
                    "A student source with the name '{$cleanData['name']}' already exists",
                    409,
                    "Duplicate Student Source Name",
                    "Please choose a different name."
                );
            }
        }

        $studentSource->update($cleanData);

        return $studentSource->fresh();
    }

    public function deactivateStudentSource($studentSourceId)
    {
        $studentSource = StudentSource::find($studentSourceId);

        if (!$studentSource) {
            throw new ModelNotFoundException("Student source with ID {$studentSourceId} not found.");
        }

        if ($studentSource->status === 'inactive') {
            throw new AppException(
                "Student source is already inactive",
                400,
                "Already Inactive",
                "This student source has already been deactivated."
            );
        }

        $studentSource->update(['status' => 'inactive']);

        return $studentSource;
    }

    public function activateStudentSource($studentSourceId)
    {
        $studentSource = StudentSource::find($studentSourceId);

        if (!$studentSource) {
            throw new ModelNotFoundException("Student source with ID {$studentSourceId} not found.");
        }

        if ($studentSource->status === 'active') {
            throw new AppException(
                "Student source is already active",
                400,
                "Already Active",
                "This student source is already active."
            );
        }

        $studentSource->update(['status' => 'active']);

        return $studentSource;
    }

    public function deleteStudentSource($studentSourceId)
    {
        $studentSource = StudentSource::find($studentSourceId);

        if (!$studentSource) {
            throw new ModelNotFoundException("Student source with ID {$studentSourceId} not found.");
        }

        $studentSource->delete();

        return true;
    }
}
