<?php

namespace App\Services\Import;

use App\Exceptions\AppException;
use App\Http\Resources\Job\SystemJobResource;
use App\Jobs\Import\BaseImportJob;
use App\Models\Job\SystemJob;
use App\Models\Job\SystemJobCategory;
use App\Models\Job\SystemJobDetail;
use App\Models\Schoolbranches;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Starts an import: keeps the uploaded file, creates the system job the app
 * tracks, and queues the import job. Every module's import controller goes
 * through here, so they all behave the same.
 */
class ImportJobDispatcher
{
    public const DISK = 'r2';

    /**
     * @param class-string<BaseImportJob> $jobClass
     * @return SystemJobResource The queued job, for the app to track right away.
     *
     * @throws AppException when the file cannot be stored or the job cannot be queued.
     */
    public function dispatch(
        string $jobClass,
        UploadedFile $file,
        array $mapping,
        object $currentSchool,
        object $user,
    ): SystemJobResource {
        $type = $jobClass::importType();
        $filePath = $this->store($file, $type, $currentSchool);

        $systemJob = SystemJob::create([
            'type' => $type->value,
            'context_type' => Schoolbranches::class,
            'context_id' => $currentSchool->id,
            'initiated_by_id' => $user->id,
            'initiated_by_type' => $user::class,
            'category_id' => $this->category($type)->id,
            'queue' => 'database',
            'status' => SystemJob::STATUS_QUEUED,
            'stage' => 'Waiting to start',
            'progress' => 0,
            'started_at' => now(),
        ]);

        $this->recordDetails($systemJob, $currentSchool, $filePath, $mapping, $file);

        try {
            $jobClass::dispatch($systemJob->id, $mapping, $filePath);
        } catch (Throwable $exception) {
            report($exception);
            $systemJob->update([
                'status' => SystemJob::STATUS_FAILED,
                'stage' => 'Import failed',
                'error_code' => 'not_queued',
                'error_message' => 'The import could not be started. Please try again.',
                'finished_at' => now(),
            ]);
            $this->deleteQuietly($filePath);

            throw new AppException(
                "Could not queue {$type->title()}: {$exception->getMessage()}",
                503,
                'Import Not Started',
                'The import could not be started because the server is busy. Please try again in a moment.'
            );
        }

        return SystemJobResource::make($systemJob->fresh('category'));
    }

    private function store(UploadedFile $file, ImportType $type, object $currentSchool): string
    {
        try {
            $path = $file->store($type->storageFolder($currentSchool->id), self::DISK);
        } catch (Throwable $exception) {
            report($exception);
            $path = false;
        }

        if (!$path) {
            throw new AppException(
                "Failed to store the uploaded file for {$type->title()}.",
                500,
                'Upload Failed',
                'The file could not be uploaded. Please check your connection and try again.'
            );
        }

        return $path;
    }

    /** The category the jobs popover of the module filters on; created if a database lacks it. */
    private function category(ImportType $type): SystemJobCategory
    {
        return SystemJobCategory::firstOrCreate(
            ['name' => $type->category()],
            ['description' => "{$type->module()} background jobs", 'status' => 'active']
        );
    }

    /** Audit copy of what was uploaded. The job does not depend on it, so a failure here is only reported. */
    private function recordDetails(SystemJob $systemJob, object $currentSchool, string $filePath, array $mapping, UploadedFile $file): void
    {
        try {
            SystemJobDetail::create([
                'job_id' => $systemJob->id,
                'school_branch_id' => $currentSchool->id,
                'input' => [
                    'file_path' => $filePath,
                    'file_name' => $file->getClientOriginalName(),
                    'mapping' => $mapping,
                ],
                'summary' => null,
                'result' => null,
                'metadata' => [],
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function deleteQuietly(string $filePath): void
    {
        try {
            Storage::disk(self::DISK)->delete($filePath);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
