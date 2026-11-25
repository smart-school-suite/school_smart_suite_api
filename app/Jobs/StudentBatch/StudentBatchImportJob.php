<?php

namespace App\Jobs\StudentBatch;

use App\Jobs\Import\BaseImportJob;
use App\Models\Studentbatch;
use App\Services\Import\ImportRowException;
use App\Services\Import\ImportType;
use App\Services\StudentBatch\StudentBatchService;

/**
 * Creates student batches from a spreadsheet through StudentBatchService,
 * like the "Add batch" form. A name already used at the school is reported.
 */
class StudentBatchImportJob extends BaseImportJob
{
    public static function importType(): ImportType
    {
        return ImportType::STUDENT_BATCH;
    }

    protected function requiredColumns(): array
    {
        return ['name' => 'Batch Name', 'description' => 'Description'];
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:1000'],
        ];
    }

    protected function attributes(): array
    {
        return ['name' => 'batch name'];
    }

    protected function normalize(array $row): array
    {
        return ['name' => $row['name'] ?? null, 'description' => $row['description'] ?? null];
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
        $exists = Studentbatch::where('school_branch_id', $this->schoolBranchId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($row['name']))])
            ->exists();

        if ($exists) {
            throw ImportRowException::alreadyExists('A student batch with this name already exists at this school.', 'name', $row['name']);
        }

        app(StudentBatchService::class)->createStudentBatch(
            $row,
            (object) ['id' => $this->schoolBranchId],
            (object) ['id' => $this->systemJob->initiated_by_id]
        );

        return self::OUTCOME_CREATED;
    }
}
