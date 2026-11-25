<?php

namespace App\Jobs\Hall;

use App\Jobs\Import\BaseImportJob;
use App\Models\Hall;
use App\Services\Hall\HallService;
use App\Services\Import\ImportRowException;
use App\Services\Import\ImportType;

/**
 * Creates halls from a spreadsheet through HallService, like the "Add hall"
 * form. Hall types come from the type columns (type_1, type_2, ...); a cell
 * may also hold several types separated by "|". A name already used at the
 * school is reported, not overwritten.
 */
class HallImportJob extends BaseImportJob
{
    public static function importType(): ImportType
    {
        return ImportType::HALL;
    }

    protected function requiredColumns(): array
    {
        return ['name' => 'Hall Name', 'capacity' => 'Hall Capacity'];
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'capacity' => ['required', 'integer', 'min:1', 'max:5000'],
            'location' => ['nullable', 'string', 'max:200'],
            'typeIds' => ['required', 'array', 'min:1'],
        ];
    }

    protected function attributes(): array
    {
        return ['name' => 'hall name', 'typeIds' => 'hall type'];
    }

    protected function messages(): array
    {
        return ['typeIds.required' => 'At least one hall type is required (for example Lecture Hall or Exam Hall).'];
    }

    protected function normalize(array $row): array
    {
        $typeIds = [];

        foreach ($row['types'] ?? [] as $type) {
            foreach (preg_split('/[|;]/', (string) ($type['type'] ?? '')) as $name) {
                $name = trim($name);
                if ($name === '') {
                    continue;
                }

                $id = $this->lookups->hallTypeId($name);
                if (!$id) {
                    throw ImportRowException::notFound("Hall type not found. Use {$this->lookups->hallTypeNames()}.", 'type', $name);
                }
                $typeIds[$id] = ['type_id' => $id];
            }
        }

        return [
            'name' => $row['name'] ?? null,
            'capacity' => $row['capacity'] ?? null,
            'location' => $row['location'] ?? null,
            'typeIds' => array_values($typeIds),
        ];
    }

    protected function duplicateKey(array $row): ?string
    {
        return $row['name'];
    }

    protected function duplicateField(): ?string
    {
        return 'name';
    }

    protected function importRow(array $row, int $rowNumber): string
    {
        $exists = Hall::where('school_branch_id', $this->schoolBranchId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($row['name']))])
            ->exists();

        if ($exists) {
            throw ImportRowException::alreadyExists('A hall with this name already exists at this school.', 'name', $row['name']);
        }

        app(HallService::class)->createHall(
            (object) ['id' => $this->schoolBranchId],
            $row,
            (object) ['id' => $this->systemJob->initiated_by_id]
        );

        return self::OUTCOME_CREATED;
    }
}
