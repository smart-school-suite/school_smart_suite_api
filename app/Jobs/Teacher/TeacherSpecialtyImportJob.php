<?php

namespace App\Jobs\Teacher;

use App\Jobs\Import\BaseImportJob;
use App\Models\Teacher;
use App\Models\Teacher\TeacherSpecialty;
use App\Services\Import\ImportRowException;
use App\Services\Import\ImportType;

/**
 * Assigns teachers to specialties from a spreadsheet: one row is a teacher
 * (full name or email), a specialty name and, when several specialties share
 * the name, its level.
 *
 * Follows the rules of the Teacher Specialty page: the teacher must be
 * allowed to teach the specialty's level, and an existing assignment is
 * reported rather than repeated.
 */
class TeacherSpecialtyImportJob extends BaseImportJob
{
    public static function importType(): ImportType
    {
        return ImportType::TEACHER_SPECIALTY;
    }

    protected function requiredColumns(): array
    {
        return ['teacher' => 'Teacher Name', 'specialty' => 'Specialty'];
    }

    protected function rules(): array
    {
        return [
            'teacher_name' => ['required'],
            'specialty_name' => ['required'],
        ];
    }

    protected function attributes(): array
    {
        return ['teacher_name' => 'teacher', 'specialty_name' => 'specialty'];
    }

    protected function normalize(array $row): array
    {
        $teacher = $this->lookups->teacher($row['teacher'] ?? null);
        if (!empty($row['teacher']) && !$teacher) {
            throw ImportRowException::notFound('No teacher with this name or email at your school.', 'teacher', $row['teacher']);
        }

        $specialty = $this->lookups->specialty($row['specialty'] ?? null, $row['level'] ?? null);
        if (!empty($row['specialty']) && !$specialty) {
            throw ImportRowException::notFound(
                empty($row['level'])
                    ? 'No specialty with this name at your school.'
                    : 'No specialty with this name at this level at your school.',
                'specialty',
                $row['specialty']
            );
        }

        return [
            'teacher_name' => $row['teacher'] ?? null,
            'specialty_name' => $row['specialty'] ?? null,
            'teacher_id' => $teacher?->id,
            'specialty_id' => $specialty?->id,
            'level_id' => $specialty?->level_id,
        ];
    }

    protected function duplicateKey(array $row): ?string
    {
        return "{$row['teacher_id']}|{$row['specialty_id']}";
    }

    protected function duplicateField(): ?string
    {
        return 'teacher_name';
    }

    protected function importRow(array $row, int $rowNumber): string
    {
        $assigned = TeacherSpecialty::where('school_branch_id', $this->schoolBranchId)
            ->where('teacher_id', $row['teacher_id'])
            ->where('specialty_id', $row['specialty_id'])
            ->exists();

        if ($assigned) {
            throw ImportRowException::alreadyExists('The teacher is already assigned to this specialty.', 'teacher', $row['teacher_name']);
        }

        $teacher = Teacher::where('school_branch_id', $this->schoolBranchId)->findOrFail($row['teacher_id']);

        if (!$teacher->levels()->where('levels.id', $row['level_id'])->exists()) {
            throw ImportRowException::notAllowed(
                "The teacher is not allowed to teach this specialty's level. Add the level to the teacher's teaching levels first.",
                'teacher',
                $row['teacher_name']
            );
        }

        $teacher->specialties()->attach($row['specialty_id'], ['school_branch_id' => $this->schoolBranchId]);

        return self::OUTCOME_CREATED;
    }
}
