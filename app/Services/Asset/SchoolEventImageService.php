<?php

namespace App\Services\Asset;

use App\Exceptions\AppException;
use App\Models\Asset\SchoolEventImage;
use App\Models\SchoolEvent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class SchoolEventImageService
{
    public function uploadAvatar(
        SchoolEvent $schoolEvent,
        UploadedFile $picture,
        string $schoolBranchId
    ): SchoolEventImage {
        if (
            !$picture->isValid() ||
            !in_array($picture->getMimeType(), [
                'image/jpeg',
                'image/png',
                'image/webp',
            ], true)
        ) {
            throw new AppException(
                "Invalid picture uploaded for school event.",
                422,
                "Invalid Image Format",
                "Please upload a valid JPEG, PNG, or WebP image.",
                null
            );
        }

        if ($schoolEvent->school_branch_id !== $schoolBranchId) {
            throw new AppException(
                "School event does not belong to school branch {$schoolBranchId}.",
                422,
                "Invalid School Branch",
                "The school event does not belong to this school branch.",
                null
            );
        }

        $disk = Storage::disk('r2');

        $objectKey = sprintf(
            'schools/%s/school-events/%s/gallery/%s.webp',
            $schoolBranchId,
            $schoolEvent->id,
            Str::uuid()
        );

        $uploaded = $disk->put(
            $objectKey,
            file_get_contents($picture->getRealPath()),
            ['ContentType' => $picture->getMimeType()]
        );

        if (!$uploaded) {
            throw new AppException(
                "Failed to upload the school event picture to R2.",
                500,
                "Upload Failed",
                "Failed to upload the school event picture.",
                null
            );
        }

        try {
            return DB::transaction(function () use (
                $schoolEvent,
                $schoolBranchId,
                $objectKey,
                $picture
            ) {
                $lockedEvent = SchoolEvent::query()
                    ->whereKey($schoolEvent->getKey())
                    ->lockForUpdate()
                    ->first();

                if (!$lockedEvent) {
                    throw new AppException(
                        "School event not found during avatar upload transaction.",
                        404,
                        "School Event Not Found",
                        "The requested school event could not be found.",
                        null
                    );
                }

                if ($lockedEvent->school_branch_id !== $schoolBranchId) {
                    throw new AppException(
                        "School event branch mismatch during transaction for ID {$schoolBranchId}.",
                        422,
                        "Invalid School Branch",
                        "The school event does not belong to this school branch.",
                        null
                    );
                }

                return SchoolEventImage::create([
                    'id' => (string) Str::uuid(),
                    'school_branch_id' => $schoolBranchId,
                    'school_event_id' => $lockedEvent->id,
                    'object_key' => $objectKey,
                    'mime_type' => $picture->getMimeType(),
                    'size_bytes' => $picture->getSize(),
                ]);
            });
        } catch (Throwable $exception) {
            try {
                Storage::disk('r2')->delete($objectKey);
            } catch (Throwable $cleanupException) {
                report($cleanupException);
            }

            throw $exception;
        }
    }

    public function removeAvatar(
        SchoolEvent $schoolEvent,
        string $schoolBranchId,
        string $avatarId
    ): void {
        if ($schoolEvent->school_branch_id !== $schoolBranchId) {
            throw new AppException(
                "School event does not belong to school branch {$schoolBranchId}.",
                422,
                "Invalid School Branch",
                "The school event does not belong to this school branch.",
                null
            );
        }

        $objectKey = null;

        try {
            DB::transaction(function () use ($schoolEvent, $schoolBranchId, $avatarId, &$objectKey) {
                $lockedEvent = SchoolEvent::query()
                    ->whereKey($schoolEvent->getKey())
                    ->lockForUpdate()
                    ->first();

                if (!$lockedEvent) {
                    throw new AppException(
                        "School event not found during avatar removal transaction.",
                        404,
                        "School Event Not Found",
                        "The requested school event could not be found.",
                        null
                    );
                }

                if ($lockedEvent->school_branch_id !== $schoolBranchId) {
                    throw new AppException(
                        "School event branch mismatch during transaction for ID {$schoolBranchId}.",
                        422,
                        "Invalid School Branch",
                        "The school event does not belong to this school branch.",
                        null
                    );
                }

                $avatar = SchoolEventImage::query()
                    ->where('school_branch_id', $schoolBranchId)
                    ->where('school_event_id', $lockedEvent->id)
                    ->whereKey($avatarId)
                    ->first();

                if (!$avatar) {
                    throw new AppException(
                        "School event picture not found.",
                        404,
                        "Picture Not Found",
                        "The specified school event picture could not be found.",
                        null
                    );
                }

                $objectKey = $avatar->object_key;

                $avatar->delete();
            });

            if ($objectKey) {
                Storage::disk('r2')->delete($objectKey);
            }
        } catch (Throwable $exception) {
            throw $exception;
        }
    }
}
