<?php

namespace App\Jobs\Department;

use App\Jobs\Import\BaseImportJob;
use App\Models\Department;
use App\Services\Department\DepartmentService;
use App\Services\Import\ImportRowException;
use App\Services\Import\ImportType;

/**
 * Creates departments from a spreadsheet through DepartmentService, like the
 * "Add department" form. A name already used at the school is reported, not
 * overwritten.
 */
class DepartmentImportJob extends BaseImportJob
{
    public static function importType(): ImportType
    {
        return ImportType::DEPARTMENT;
    }

    protected function requiredColumns(): array
    {
        return ['department_name' => 'Department Name'];
    }

    protected function rules(): array
    {
        return [
            'department_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function attributes(): array
    {
        return ['department_name' => 'department name'];
    }

    protected function normalize(array $row): array
    {
        return [
            'department_name' => $row['department_name'] ?? null,
            'description' => $row['description'] ?? null,
        ];
    }

    protected function duplicateKey(array $row): ?string
    {
        return $row['department_name'];
    }

    protected function duplicateField(): ?string
    {
        return 'department_name';
    }

    protected function importRow(array $row, int $rowNumber): string
    {
        $exists = Department::where('school_branch_id', $this->schoolBranchId)
            ->whereRaw('LOWER(department_name) = ?', [mb_strtolower(trim($row['department_name']))])
            ->exists();

        if ($exists) {
            throw ImportRowException::alreadyExists('A department with this name already exists at this school.', 'department_name', $row['department_name']);
        }

        app(DepartmentService::class)->createDepartment(
            $row,
            (object) ['id' => $this->schoolBranchId],
            (object) ['id' => $this->systemJob->initiated_by_id]
        );

        return self::OUTCOME_CREATED;
    }
}
