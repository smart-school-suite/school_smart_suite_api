<?php

namespace App\Jobs\Import;

use App\Models\Job\SystemJob;
use App\Models\Job\SystemJobDetail;
use App\Models\Schoolbranches;
use App\Services\Helpers\Import\ColumnIndexResolverService;
use App\Services\Helpers\Import\ImportMapRowService;
use App\Services\Helpers\Import\SpreadSheetReadException;
use App\Services\Helpers\Import\SpreadSheetReaderService;
use App\Services\Import\ImportErrorCollector;
use App\Services\Import\ImportErrorReason;
use App\Services\Import\ImportFailedException;
use App\Services\Import\ImportLookups;
use App\Services\Import\ImportRowException;
use App\Services\Import\ImportType;
use App\Services\Import\SystemJobBroadcaster;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

/**
 * Everything an import of a spreadsheet has in common: reading the file,
 * matching its columns, validating and saving each row, reporting progress,
 * grouping the problems by reason and finishing the system job in a state
 * the app can always read back, live event or not.
 *
 * A module's import only says what a row means: its rules, how to turn the
 * cells into ids, and how to save it. See TeacherImportJob for an example.
 *
 * The job runs once ($tries = 1). A row is saved in its own transaction, so a
 * retry would find the rows of the first attempt already there and report
 * them as existing, which is misleading; a failed run is reported instead.
 */
abstract class BaseImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public const OUTCOME_CREATED = 'created';
    public const OUTCOME_UPDATED = 'updated';

    /** Largest file (in data rows) one import accepts. */
    public const MAX_ROWS = 5000;

    /** Save counters to the database at least this often while rows are processed. */
    private const PERSIST_EVERY_ROWS = 25;
    private const PERSIST_EVERY_SECONDS = 2;

    public $tries = 1;
    public $timeout = 1800;
    public $failOnTimeout = true;

    protected SystemJob $systemJob;
    protected string $schoolBranchId;
    protected ?Schoolbranches $schoolBranch = null;
    protected ImportLookups $lookups;

    private ImportErrorCollector $errors;
    private SystemJobBroadcaster $broadcaster;
    private int $total = 0;
    private int $processed = 0;
    private int $created = 0;
    private int $updated = 0;
    private int $blankRows = 0;
    private float $lastPersistedAt = 0;

    /** @var array<string, int> duplicate key => first row it was seen on */
    private array $seenKeys = [];

    /*
     * Plain properties with defaults (not promoted) and protected, like the
     * import jobs before this class: a job queued by the previous version
     * still unserializes, and reads its mapping from the job details.
     */
    protected string $jobId;
    protected array $mapping = [];
    protected ?string $filePath = null;

    /**
     * @param string $jobId The system_jobs row this run reports to.
     * @param array $mapping Column mapping chosen in the app (standardFields / repeatableGroups).
     * @param string|null $filePath Uploaded file on the r2 disk.
     */
    public function __construct(string $jobId, array $mapping = [], ?string $filePath = null)
    {
        $this->jobId = $jobId;
        $this->mapping = $mapping;
        $this->filePath = $filePath;
    }

    public function jobId(): string
    {
        return $this->jobId;
    }

    /** Which import this is; gives the job its names and category. */
    abstract public static function importType(): ImportType;

    protected function type(): ImportType
    {
        return static::importType();
    }

    /**
     * Save one valid row. Throw ImportRowException for a reason the person
     * can fix (already exists, not allowed...). Runs inside a transaction.
     *
     * @return string self::OUTCOME_CREATED or self::OUTCOME_UPDATED
     */
    abstract protected function importRow(array $row, int $rowNumber): string;

    /**
     * Fields whose column must be chosen when matching columns, as
     * field => label. Checked before any row is read.
     *
     * @return array<string, string>
     */
    protected function requiredColumns(): array
    {
        return [];
    }

    /** Validation rules for a normalized row. */
    protected function rules(): array
    {
        return [];
    }

    /** Readable names for fields in validation messages, e.g. 'gender_id' => 'gender'. */
    protected function attributes(): array
    {
        return [];
    }

    protected function messages(): array
    {
        return [];
    }

    /** Load lookups once (genders, levels...) before the rows are processed. */
    protected function prepare(): void
    {
    }

    /**
     * Turn the mapped cells into what importRow() saves: resolve names to
     * ids, split lists, tidy values. Throw ImportRowException for a value
     * that cannot be resolved.
     */
    protected function normalize(array $row): array
    {
        return $row;
    }

    /**
     * A key identifying the record a row would create. Two rows of the file
     * with the same key are reported as duplicates instead of both being
     * tried. Return null to skip the check for the row.
     */
    protected function duplicateKey(array $row): ?string
    {
        return null;
    }

    /** Field the duplicate key comes from, shown on the duplicate group. */
    protected function duplicateField(): ?string
    {
        return null;
    }

    /** Runs once after all rows were processed, before the job is finished. */
    protected function afterImport(): void
    {
    }

    public function handle(): void
    {
        $systemJob = SystemJob::find($this->jobId);

        if (!$systemJob || $systemJob->isFinished()) {
            return;
        }

        $this->systemJob = $systemJob;
        $this->schoolBranchId = (string) $systemJob->context_id;
        $this->schoolBranch = Schoolbranches::find($this->schoolBranchId);
        $this->lookups = new ImportLookups($this->schoolBranchId);
        $this->errors = new ImportErrorCollector();
        $this->broadcaster = new SystemJobBroadcaster($systemJob);
        $this->loadLegacyInput();

        try {
            $this->run();
        } catch (ImportFailedException $exception) {
            $this->finishFailed($exception->getMessage(), $exception->errorCode);
        } catch (Throwable $exception) {
            $this->finishFailed(
                "The import stopped because of an unexpected error after {$this->processed} of {$this->total} rows. Rows saved before it are kept.",
                'unexpected_error'
            );
            // Rethrown so the queue records it; failed() logs it once.
            throw $exception;
        } finally {
            $this->deleteFile();
        }
    }

    /**
     * Called by the queue when the job dies outside handle(): timeout, worker
     * killed, or an exception handle() rethrew. Makes sure the system job
     * does not stay "processing" forever.
     */
    public function failed(?Throwable $exception): void
    {
        $systemJob = SystemJob::find($this->jobId);

        if (!$systemJob) {
            return;
        }

        if (!$systemJob->isFinished()) {
            $this->systemJob = $systemJob;
            $this->broadcaster = new SystemJobBroadcaster($systemJob);
            // A fresh instance: the counters of the run are only in the
            // database, so they are kept as saved.
            $this->finishFailed(
                'The import stopped before it could finish (it took too long or the server restarted). Rows saved before it stopped are kept; check them before importing the file again.',
                'job_interrupted',
                withCounters: false
            );
            $this->deleteFile();
        }

        if ($exception) {
            $this->logFailure($exception);
        }
    }

    private function run(): void
    {
        $this->updateJob([
            'status' => SystemJob::STATUS_PROCESSING,
            'stage' => 'Reading the file',
            'progress' => 1,
            'started_at' => now(),
            'error_code' => null,
            'error_message' => null,
        ], force: true);

        $rows = $this->readRows();

        $this->total = count($rows);
        $this->updateJob([
            'total_items' => $this->total,
            'processed_items' => 0,
            'successful_items' => 0,
            'failed_items' => 0,
            'stage' => "Importing {$this->type()->recordsLabel()}",
            'progress' => 5,
        ], force: true);

        $this->prepare();

        foreach ($rows as $rowNumber => $row) {
            $this->processRow($row, $rowNumber);
            $this->processed++;
            $this->reportProgress();
        }

        $this->afterImport();
        $this->finish();
    }

    /**
     * Read the file and map each row to fields, keyed by its row number in
     * the file (the header is row 1). Blank rows are left out.
     *
     * @return array<int, array>
     */
    private function readRows(): array
    {
        if (!$this->filePath) {
            throw new ImportFailedException('The uploaded file for this import could not be found. Please upload it again.', 'file_missing');
        }

        try {
            $sheet = app(SpreadSheetReaderService::class)->read($this->filePath);
        } catch (SpreadSheetReadException $exception) {
            throw new ImportFailedException($this->readErrorMessage($exception), 'file_unreadable');
        }

        $this->checkMappedColumns($sheet->header);

        $columnIndexes = ColumnIndexResolverService::resolve($sheet->header, $this->mapping);
        $mapper = app(ImportMapRowService::class);
        $rows = [];

        foreach ($sheet->dataRows as $index => $cells) {
            $mapped = $this->clean($mapper->mapRow($cells->toArray(), $columnIndexes));

            if ($this->isBlank($mapped)) {
                $this->blankRows++;
                continue;
            }

            $rows[$index + 2] = $mapped;
        }

        if ($rows === []) {
            throw new ImportFailedException('The file has no rows to import. Add the data under the header row and try again.', 'file_empty');
        }

        if (count($rows) > self::MAX_ROWS) {
            $max = number_format(self::MAX_ROWS);
            throw new ImportFailedException("The file has " . number_format(count($rows)) . " rows; one import takes at most {$max}. Split the file and import the parts one after the other.", 'file_too_large');
        }

        return $rows;
    }

    private function checkMappedColumns(array $header): void
    {
        $standard = $this->mapping['standardFields'] ?? [];
        $unmapped = collect($this->requiredColumns())
            ->filter(fn($label, $field) => trim((string) ($standard[$field] ?? '')) === '')
            ->values();

        if ($unmapped->isNotEmpty()) {
            throw new ImportFailedException(
                'No column was matched to: ' . $unmapped->implode(', ') . '. Match these columns and import again.',
                'columns_not_mapped'
            );
        }

        $missing = ColumnIndexResolverService::missingColumns($header, $this->mapping);

        if ($missing !== []) {
            $list = collect($missing)->map(fn($column) => "\"{$column}\"")->implode(', ');
            throw new ImportFailedException(
                "These columns are not in the file's header row: {$list}. Check the header row or match the columns again.",
                'columns_missing'
            );
        }
    }

    private function processRow(array $row, int $rowNumber): void
    {
        try {
            $normalized = $this->normalize($row);
        } catch (ImportRowException $exception) {
            $this->errors->addException($rowNumber, $exception);
            return;
        }

        if (!$this->validateRow($normalized, $rowNumber)) {
            return;
        }

        $key = $this->duplicateKey($normalized);
        if ($key !== null) {
            $key = Str::lower(trim($key));
            if (isset($this->seenKeys[$key])) {
                $field = $this->duplicateField();
                $this->errors->add(
                    $rowNumber,
                    ImportErrorReason::DUPLICATE_IN_FILE,
                    'Repeats a row higher up in the file; only the first one was imported.',
                    $field,
                    $field ? Arr::get($normalized, $field) : null
                );
                return;
            }
            $this->seenKeys[$key] = $rowNumber;
        }

        try {
            $outcome = DB::transaction(fn() => $this->importRow($normalized, $rowNumber));
            $outcome === self::OUTCOME_UPDATED ? $this->updated++ : $this->created++;
        } catch (ImportRowException $exception) {
            $this->errors->addException($rowNumber, $exception);
        } catch (Throwable $exception) {
            $this->errors->add(
                $rowNumber,
                ImportErrorReason::UNEXPECTED,
                'Could not be saved because of an unexpected error. Try these rows again; contact support if it keeps happening.'
            );
            Log::warning("{$this->type()->title()} row {$rowNumber} failed", [
                'job_id' => $this->jobId,
                'error' => $exception->getMessage(),
                'file' => $exception->getFile() . ':' . $exception->getLine(),
            ]);
        }
    }

    private function validateRow(array $row, int $rowNumber): bool
    {
        $rules = $this->rules();

        if ($rules === []) {
            return true;
        }

        $validator = Validator::make($row, $rules, $this->messages(), $this->attributes());

        if (!$validator->fails()) {
            return true;
        }

        $failedRules = $validator->failed();

        foreach ($validator->errors()->messages() as $field => $messages) {
            $rulesForField = array_keys($failedRules[$field] ?? []);

            foreach ($messages as $position => $message) {
                $rule = $rulesForField[$position] ?? null;
                $this->errors->add(
                    $rowNumber,
                    $this->reasonForRule($rule),
                    $message,
                    preg_replace('/\.\d+(\.|$)/', '.*$1', $field),
                    Arr::get($row, $field)
                );
            }
        }

        return false;
    }

    private function reasonForRule(?string $rule): string
    {
        return match (true) {
            $rule === null => ImportErrorReason::INVALID_VALUE,
            str_starts_with($rule, 'Required') || $rule === 'Present' || $rule === 'Filled' => ImportErrorReason::MISSING_VALUE,
            $rule === 'Exists' => ImportErrorReason::NOT_FOUND,
            $rule === 'Unique' => ImportErrorReason::ALREADY_EXISTS,
            default => ImportErrorReason::INVALID_VALUE,
        };
    }

    private function reportProgress(): void
    {
        $now = microtime(true);
        $due = $this->processed % self::PERSIST_EVERY_ROWS === 0
            || ($now - $this->lastPersistedAt) >= self::PERSIST_EVERY_SECONDS;

        if (!$due) {
            return;
        }

        $this->updateJob([
            'processed_items' => $this->processed,
            'successful_items' => $this->created + $this->updated,
            'failed_items' => $this->errors->failedRowCount(),
            'progress' => $this->runningProgress(),
            'stage' => "Importing {$this->type()->recordsLabel()} ({$this->processed} of {$this->total})",
        ]);
    }

    /** 5–95% while rows are processed; 100% is kept for the finished job. */
    private function runningProgress(): int
    {
        return $this->total > 0
            ? (int) min(95, 5 + floor(($this->processed / $this->total) * 90))
            : 5;
    }

    private function finish(): void
    {
        $failed = $this->errors->failedRowCount();
        $succeeded = $this->created + $this->updated;
        $label = $this->type()->recordsLabel();

        [$status, $summary] = match (true) {
            $failed === 0 => [
                SystemJob::STATUS_COMPLETED,
                "Imported all {$succeeded} {$label}.",
            ],
            $succeeded === 0 => [
                SystemJob::STATUS_FAILED,
                "None of the {$this->total} rows could be imported. Review the issues, fix the file and import it again.",
            ],
            default => [
                SystemJob::STATUS_COMPLETED_WITH_ISSUES,
                "Imported {$succeeded} of {$this->total} {$label}; {$failed} " . Str::plural('row', $failed) . " need attention.",
            ],
        };

        $this->updateJob([
            'status' => $status,
            'stage' => $summary,
            'progress' => 100,
            'processed_items' => $this->processed,
            'successful_items' => $succeeded,
            'failed_items' => $failed,
            'error_code' => $status === SystemJob::STATUS_FAILED ? 'no_rows_imported' : null,
            'error_message' => $status === SystemJob::STATUS_FAILED ? $summary : null,
            'finished_at' => now(),
            'result' => [
                'summary' => $summary,
                'total' => $this->total,
                'created' => $this->created,
                'updated' => $this->updated,
                'failed' => $failed,
                'blank_rows' => $this->blankRows,
                'error_groups' => $this->errors->groups(),
            ],
        ], force: true);
    }

    private function finishFailed(string $message, string $code, bool $withCounters = true): void
    {
        $base = [
            'status' => SystemJob::STATUS_FAILED,
            'stage' => 'Import failed',
            'progress' => 100,
            'error_code' => $code,
            'error_message' => $message,
            'finished_at' => now(),
        ];

        if (!$withCounters) {
            $result = is_array($this->systemJob->result) ? $this->systemJob->result : [];
            $this->updateJob($base + ['result' => ['summary' => $message] + $result], force: true);
            return;
        }

        $errors = $this->errors;

        $this->updateJob($base + [
            'processed_items' => $this->processed,
            'successful_items' => $this->created + $this->updated,
            'failed_items' => $errors->failedRowCount(),
            'result' => [
                'summary' => $message,
                'total' => $this->total,
                'created' => $this->created,
                'updated' => $this->updated,
                'failed' => $errors->failedRowCount(),
                'blank_rows' => $this->blankRows,
                'error_groups' => $errors->groups(),
            ],
        ], force: true);
    }

    /** Save the job and tell the app. Final updates always broadcast; progress is throttled. */
    private function updateJob(array $attributes, bool $force = false): void
    {
        $this->systemJob->fill($attributes);
        $this->systemJob->updated_at = now();
        $this->systemJob->save();
        $this->lastPersistedAt = microtime(true);

        $this->broadcaster->publish($this->systemJob, $force);
    }

    /** Values from a spreadsheet: numbers as text (677123456.0 -> "677123456"), empty cells as null. */
    private function clean(array $values): array
    {
        return array_map(function ($value) {
            if (is_array($value)) {
                return $this->clean($value);
            }
            if (is_float($value) && floor($value) === $value && abs($value) < PHP_INT_MAX) {
                return (string) (int) $value;
            }
            if (is_int($value) || is_float($value)) {
                return (string) $value;
            }
            if (is_string($value)) {
                $value = trim($value);
                // The reader formats decimals with full float precision
                // ("79.98999999999999" for 79.99); give back the short form.
                if (preg_match('/^-?\d+\.\d{9,}$/', $value)) {
                    return (string) (float) $value;
                }
                return $value === '' ? null : $value;
            }
            return $value;
        }, $values);
    }

    private function isBlank(array $values): bool
    {
        foreach ($values as $value) {
            if (is_array($value) ? !$this->isBlank($value) : ($value !== null && $value !== '')) {
                return false;
            }
        }

        return true;
    }

    private function readErrorMessage(SpreadSheetReadException $exception): string
    {
        return match ($exception->getCode()) {
            404 => 'The uploaded file could not be found. Please upload it again.',
            default => str_contains($exception->getMessage(), 'no data rows')
                ? 'The file has no rows to import. Add the data under the header row and try again.'
                : 'The file could not be read. Make sure it is a CSV, XLSX or XLS file that opens correctly, then upload it again.',
        };
    }

    /** Jobs queued before mapping and file were passed in read them from the job details. */
    private function loadLegacyInput(): void
    {
        if ($this->filePath && $this->mapping !== []) {
            return;
        }

        try {
            $input = SystemJobDetail::where('job_id', $this->jobId)->first()?->input ?? [];
            $this->filePath ??= $input['file_path'] ?? null;
            $this->mapping = $this->mapping ?: ($input['mapping'] ?? []);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function deleteFile(): void
    {
        if (!$this->filePath) {
            return;
        }

        try {
            app(SpreadSheetReaderService::class)->deleteIfExists($this->filePath);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function logFailure(Throwable $exception): void
    {
        Log::error("{$this->type()->title()} [{$this->jobId}] failed", [
            'school_branch_id' => $this->schoolBranchId ?? null,
            'error' => $exception->getMessage(),
            'file' => $exception->getFile() . ':' . $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
