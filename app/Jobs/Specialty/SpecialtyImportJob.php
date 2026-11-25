<?php

namespace App\Jobs\Specialty;

use App\Jobs\Import\BaseImportJob;
use App\Models\Specialty;
use App\Services\Import\ImportRowException;
use App\Services\Import\ImportType;
use App\Services\Specialty\SpecialtyService;

/**
 * Creates specialties from a spreadsheet through SpecialtyService, like the
 * "Add specialty" form: fees left empty take the level's fees, and with
 * "Automatically Create Specialties Across All Levels" on, a row creates the
 * specialty at every level of the school. A specialty that already exists
 * (same name, department and level) is reported, not overwritten.
 */
class SpecialtyImportJob extends BaseImportJob
{
    public static function importType(): ImportType
    {
        return ImportType::SPECIALTY;
    }

    protected function requiredColumns(): array
    {
        return ['specialty_name' => 'Specialty Name', 'department_name' => 'Department', 'level' => 'Level'];
    }

    protected function rules(): array
    {
        return [
            'specialty_name' => ['required', 'string', 'max:150'],
            'department_id' => ['required'],
            'level_id' => ['required'],
            'registration_fee' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'school_fee' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function attributes(): array
    {
        return [
            'specialty_name' => 'specialty name',
            'department_id' => 'department',
            'level_id' => 'level',
            'registration_fee' => 'registration fee',
            'school_fee' => 'tuition fee',
        ];
    }

    protected function normalize(array $row): array
    {
        $department = $this->lookups->department($row['department_name'] ?? null);
        if (!empty($row['department_name']) && !$department) {
            throw ImportRowException::notFound(
                'No department with this name at your school. Create the department first, or import departments before specialties.',
                'department_name',
                $row['department_name']
            );
        }

        $levelId = $this->lookups->levelId($row['level'] ?? null);
        if (!empty($row['level']) && !$levelId) {
            $examples = $this->lookups->levels()->sortBy('level')->take(3)->pluck('name')->join(', ');
            throw ImportRowException::notFound(
                "Level not found in your school's level system. Use level names such as {$examples}.",
                'level',
                $row['level']
            );
        }

        return [
            'specialty_name' => $row['specialty_name'] ?? null,
            'department_id' => $department?->id,
            'level_id' => $levelId,
            'registration_fee' => $this->amount($row['registration_fee'] ?? null),
            'school_fee' => $this->amount($row['school_fee'] ?? null),
            'description' => $row['description'] ?? null,
        ];
    }

    protected function duplicateKey(array $row): ?string
    {
        return "{$row['specialty_name']}|{$row['department_id']}|{$row['level_id']}";
    }

    protected function duplicateField(): ?string
    {
        return 'specialty_name';
    }

    protected function importRow(array $row, int $rowNumber): string
    {
        $exists = Specialty::where('school_branch_id', $this->schoolBranchId)
            ->whereRaw('LOWER(specialty_name) = ?', [mb_strtolower(trim($row['specialty_name']))])
            ->where('department_id', $row['department_id'])
            ->where('level_id', $row['level_id'])
            ->exists();

        if ($exists) {
            throw ImportRowException::alreadyExists(
                'A specialty with this name already exists in this department at this level.',
                'specialty_name',
                $row['specialty_name']
            );
        }

        app(SpecialtyService::class)->createSpecialty(
            $row,
            (object) ['id' => $this->schoolBranchId],
            (object) ['id' => $this->systemJob->initiated_by_id]
        );

        return self::OUTCOME_CREATED;
    }

    /** "150,000", "150 000" or "150000.00" -> "150000.00"; text that is not a number is kept for the validator. */
    private function amount(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = preg_replace('/[\s,]/', '', $value);

        return is_numeric($clean) ? $clean : $value;
    }
}
