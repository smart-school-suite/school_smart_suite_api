<?php

namespace App\Services\Asset;

use App\Exceptions\AppException;
use App\Models\Asset\SchoolLogo;
use App\Models\School;
use App\Models\Schoolbranches;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class SchoolLogoService
{
    public function uploadLogo(
        School $school,
        UploadedFile $picture,
        string $schoolBranchId
    ): SchoolLogo {
        $disk = Storage::disk('r2');
        $objectKey = null;
        $logoSaved = false;
        $maxChanges = 3;
        $periodDays = 30;

        try {
            if (!$picture->isValid()) {
                throw new AppException(
                    "Invalid logo uploaded for school: {$picture->getErrorMessage()}",
                    422,
                    "Invalid Image Format",
                    "Please upload a valid JPEG, PNG, or WebP image.",
                    null
                );
            }

            $mimeType = $picture->getMimeType();
            $fileSize = $picture->getSize();

            if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                throw new AppException(
                    "Invalid logo uploaded for school. Detected MIME type: {$mimeType}",
                    422,
                    "Invalid Image Format",
                    "Please upload a valid JPEG, PNG, or WebP image.",
                    null
                );
            }

            $branchBelongsToSchool = Schoolbranches::query()
                ->where('id', $schoolBranchId)
                ->where('school_id', $school->getKey())
                ->exists();

            if (!$branchBelongsToSchool) {
                throw new AppException(
                    "School branch {$schoolBranchId} does not belong to school {$school->getKey()}.",
                    422,
                    "Invalid School Branch",
                    "This school branch does not belong to the school.",
                    null
                );
            }

            $dimensions = @getimagesize($picture->getRealPath());
            $width = $dimensions[0] ?? null;
            $height = $dimensions[1] ?? null;

            $extension = match ($mimeType) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            };

            $directory = sprintf('schools/%s/logo', $school->getKey());

            $fileName = Str::uuid() . '.' . $extension;

            $objectKey = $disk->putFileAs(
                $directory,
                $picture,
                $fileName,
                ['ContentType' => $mimeType]
            );

            if (!$objectKey) {
                throw new AppException(
                    "R2 putFileAs returned false for the school logo.",
                    500,
                    "Upload Failed",
                    "Failed to upload the school logo.",
                    null
                );
            }

            if (!$disk->exists($objectKey)) {
                throw new AppException(
                    "R2 object {$objectKey} does not exist after upload.",
                    500,
                    "Upload Failed",
                    "Failed to upload the school logo.",
                    null
                );
            }

            $logo = DB::transaction(function () use (
                $school,
                $objectKey,
                $mimeType,
                $fileSize,
                $width,
                $height,
                $maxChanges,
                $periodDays
            ) {
                $lockedSchool = School::query()
                    ->whereKey($school->getKey())
                    ->lockForUpdate()
                    ->first();

                if (!$lockedSchool) {
                    throw new AppException(
                        "School not found during logo upload transaction.",
                        404,
                        "School Not Found",
                        "The requested school could not be found.",
                        null
                    );
                }

                $currentLogo = SchoolLogo::query()
                    ->where('school_id', $lockedSchool->getKey())
                    ->where('is_current', true)
                    ->lockForUpdate()
                    ->first();

                $changes = SchoolLogo::query()
                    ->where('school_id', $lockedSchool->getKey())
                    ->whereNotNull('replaced_at')
                    ->where('replaced_at', '>=', now()->subDays($periodDays))
                    ->count();

                if ($changes >= $maxChanges) {
                    throw new AppException(
                        "Logo change limit exceeded for school ID {$lockedSchool->getKey()}.",
                        422,
                        "Change Limit Reached",
                        "You have reached the school logo change limit. Please try again later.",
                        null
                    );
                }

                if ($currentLogo) {
                    $currentLogo->forceFill([
                        'is_current' => false,
                        'replaced_at' => now(),
                    ])->save();
                }

                $logo = new SchoolLogo();

                $logo->forceFill([
                    'id' => (string) Str::uuid(),
                    'school_id' => $lockedSchool->getKey(),
                    'object_key' => $objectKey,
                    'mime_type' => $mimeType,
                    'size_bytes' => $fileSize,
                    'width' => $width,
                    'height' => $height,
                    'is_current' => true,
                ])->save();

                return $logo;
            });

            $logoSaved = true;

            return $logo;
        } catch (AppException $e) {
            if ($objectKey && !$logoSaved) {
                try {
                    $disk->delete($objectKey);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $e;
        } catch (Throwable $e) {
            if ($objectKey && !$logoSaved) {
                try {
                    $disk->delete($objectKey);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw new AppException(
                "An error occurred while uploading the school logo: {$e->getMessage()}",
                500,
                "Upload Error",
                "We encountered an issue while uploading the school logo.",
                null
            );
        }
    }

    public function removeLogo(
        School $school,
        string $schoolBranchId
    ): void {
        $objectKey = null;

        try {
            $branchBelongsToSchool = Schoolbranches::query()
                ->where($schoolBranchId)
                ->where('school_id', $school->getKey())
                ->exists();

            if (!$branchBelongsToSchool) {
                throw new AppException(
                    "School branch {$schoolBranchId} does not belong to school {$school->getKey()}.",
                    422,
                    "Invalid School Branch",
                    "This school branch does not belong to the school.",
                    null
                );
            }

            DB::transaction(function () use ($school, &$objectKey) {
                $lockedSchool = School::query()
                    ->whereKey($school->getKey())
                    ->lockForUpdate()
                    ->first();

                if (!$lockedSchool) {
                    throw new AppException(
                        "School not found during logo removal transaction.",
                        404,
                        "School Not Found",
                        "The requested school could not be found.",
                        null
                    );
                }

                $currentLogo = SchoolLogo::query()
                    ->where('school_id', $lockedSchool->getKey())
                    ->where('is_current', true)
                    ->lockForUpdate()
                    ->first();

                if (!$currentLogo) {
                    throw new AppException(
                        "No current logo found for school ID {$lockedSchool->getKey()}.",
                        404,
                        "Logo Not Found",
                        "The school does not have a logo to remove.",
                        null
                    );
                }

                $objectKey = $currentLogo->object_key;

                $currentLogo->forceFill([
                    'is_current' => false,
                    'replaced_at' => now(),
                ])->save();
            });
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "An error occurred while removing the school logo: {$e->getMessage()}",
                500,
                "Removal Error",
                "We encountered an issue while removing the school logo.",
                null
            );
        }

        if ($objectKey) {
            try {
                Storage::disk('r2')->delete($objectKey);
            } catch (Throwable $cleanupException) {
                report($cleanupException);
            }
        }
    }
}
