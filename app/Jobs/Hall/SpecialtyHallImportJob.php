<?php

namespace App\Jobs\Hall;

use App\Jobs\Import\BaseImportJob;
use App\Models\SpecialtyHall;
use App\Services\Hall\SpecialtyHallService;
use App\Services\Import\ImportRowException;
use App\Services\Import\ImportType;

/**
 * Assigns halls to specialties from a spreadsheet: one row is a hall name, a
 * specialty name and, when several specialties share the name, its level.
 * Goes through SpecialtyHallService like the Specialty Hall page, so the
 * halls' and specialties' assignment counts stay right. An existing
 * assignment is reported rather than repeated.
 */
class SpecialtyHallImportJob extends BaseImportJob
{
    public static function importType(): ImportType
    {
        return ImportType::SPECIALTY_HALL;
    }

    protected function requiredColumns(): array
    {
        return ['hall' => 'Hall', 'specialty' => 'Specialty'];
    }

    protected function rules(): array
    {
        return [
            'hall_name' => ['required'],
            'specialty_name' => ['required'],
        ];
    }

    protected function attributes(): array
    {
        return ['hall_name' => 'hall', 'specialty_name' => 'specialty'];
    }

    protected function normalize(array $row): array
    {
        $hall = $this->lookups->hall($row['hall'] ?? null);
        if (!empty($row['hall']) && !$hall) {
            throw ImportRowException::notFound('No hall with this name at your school.', 'hall', $row['hall']);
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
            'hall_name' => $row['hall'] ?? null,
            'specialty_name' => $row['specialty'] ?? null,
            'hall_id' => $hall?->id,
            'specialty_id' => $specialty?->id,
            'level_id' => $specialty?->level_id,
        ];
    }

    protected function duplicateKey(array $row): ?string
    {
        return "{$row['hall_id']}|{$row['specialty_id']}";
    }

    protected function duplicateField(): ?string
    {
        return 'hall_name';
    }

    protected function importRow(array $row, int $rowNumber): string
    {
        $assigned = SpecialtyHall::where('school_branch_id', $this->schoolBranchId)
            ->where('hall_id', $row['hall_id'])
            ->where('specialty_id', $row['specialty_id'])
            ->where('level_id', $row['level_id'])
            ->exists();

        if ($assigned) {
            throw ImportRowException::alreadyExists('The hall is already assigned to this specialty.', 'hall', $row['hall_name']);
        }

        app(SpecialtyHallService::class)->assignHallToSpecialty(
            (object) ['id' => $this->schoolBranchId],
            ['specialty_id' => $row['specialty_id'], 'hallIds' => [['hall_id' => $row['hall_id']]]],
            (object) ['id' => $this->systemJob->initiated_by_id]
        );

        return self::OUTCOME_CREATED;
    }
}
