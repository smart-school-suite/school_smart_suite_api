<?php

namespace App\Services\Asset;

use App\Exceptions\AppException;
use App\Models\Asset\AssetUploadPolicy;
use App\Models\Asset\SchoolEventImage;
use App\Models\SchoolEvent\SchoolEvent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class SchoolEventImageService
{
    public const MAX_IMAGES_PER_EVENT = 5;

    public function uploadImages(SchoolEvent $schoolEvent, array $pictures, string $schoolBranchId): Collection
    {
        $pictures = array_values(array_filter($pictures, fn($picture) => $picture instanceof UploadedFile));

        if (empty($pictures)) {
            return collect();
        }

        $this->ensureBranch($schoolEvent, $schoolBranchId);

        $policy = $this->policy();
        $maxImages = min(self::MAX_IMAGES_PER_EVENT, $policy->max_images_per_owner ?? self::MAX_IMAGES_PER_EVENT);
        $existingCount = $this->currentImageCount($schoolEvent, $schoolBranchId);

        if ($existingCount + count($pictures) > $maxImages) {
            throw new AppException(
                "School event {$schoolEvent->id} would exceed the {$maxImages} image limit.",
                422,
                "Too Many Images",
                "An event can have at most {$maxImages} images. It currently has {$existingCount}, so you can add " . max(0, $maxImages - $existingCount) . " more.",
                null
            );
        }

        foreach ($pictures as $picture) {
            $this->validatePicture($picture, $policy);
        }

        $disk = Storage::disk('r2');
        $uploaded = [];

        try {
            foreach ($pictures as $picture) {
                $uploaded[] = $this->putPicture($disk, $schoolEvent, $picture, $schoolBranchId);
            }

            return DB::transaction(function () use ($schoolEvent, $schoolBranchId, $uploaded, $maxImages) {
                $lockedEvent = $this->lockEvent($schoolEvent, $schoolBranchId);

                if ($this->currentImageCount($lockedEvent, $schoolBranchId) + count($uploaded) > $maxImages) {
                    throw new AppException(
                        "School event {$lockedEvent->id} would exceed the {$maxImages} image limit.",
                        422,
                        "Too Many Images",
                        "An event can have at most {$maxImages} images.",
                        null
                    );
                }

                return collect($uploaded)->map(fn($file) => SchoolEventImage::create([
                    'school_branch_id' => $schoolBranchId,
                    'event_id' => $lockedEvent->id,
                    'object_key' => $file['object_key'],
                    'mime_type' => $file['mime_type'],
                    'size_bytes' => $file['size_bytes'],
                    'width' => $file['width'],
                    'height' => $file['height'],
                    'is_current' => true,
                    'created_at' => now(),
                ]));
            });
        } catch (Throwable $exception) {
            $this->deleteObjects(array_column($uploaded, 'object_key'));
            throw $exception;
        }
    }

    public function removeImages(SchoolEvent $schoolEvent, string $schoolBranchId, array $imageIds): void
    {
        $imageIds = array_values(array_unique(array_filter($imageIds)));

        if (empty($imageIds)) {
            return;
        }

        $this->ensureBranch($schoolEvent, $schoolBranchId);

        $objectKeys = DB::transaction(function () use ($schoolEvent, $schoolBranchId, $imageIds) {
            $lockedEvent = $this->lockEvent($schoolEvent, $schoolBranchId);

            $images = SchoolEventImage::query()
                ->where('school_branch_id', $schoolBranchId)
                ->where('event_id', $lockedEvent->id)
                ->whereIn('id', $imageIds)
                ->get();

            if ($images->count() < count($imageIds)) {
                throw new AppException(
                    "One or more school event images were not found for event {$lockedEvent->id}.",
                    404,
                    "Picture Not Found",
                    "One or more of the selected event images could not be found.",
                    null
                );
            }

            SchoolEventImage::whereIn('id', $images->pluck('id'))->delete();

            return $images->pluck('object_key')->all();
        });

        DB::afterCommit(fn() => $this->deleteObjects($objectKeys));
    }

    public function removeAllImages(SchoolEvent $schoolEvent, string $schoolBranchId): void
    {
        $images = SchoolEventImage::query()
            ->where('school_branch_id', $schoolBranchId)
            ->where('event_id', $schoolEvent->id)
            ->get();

        if ($images->isEmpty()) {
            return;
        }

        SchoolEventImage::whereIn('id', $images->pluck('id'))->delete();

        DB::afterCommit(fn() => $this->deleteObjects($images->pluck('object_key')->all()));
    }

    public function deleteObjects(array $objectKeys): void
    {
        foreach (array_filter($objectKeys) as $objectKey) {
            try {
                Storage::disk('r2')->delete($objectKey);
            } catch (Throwable $cleanupException) {
                report($cleanupException);
            }
        }
    }

    private function putPicture($disk, SchoolEvent $schoolEvent, UploadedFile $picture, string $schoolBranchId): array
    {
        $mimeType = $picture->getMimeType();
        $dimensions = @getimagesize($picture->getRealPath());

        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        };

        $directory = sprintf('schools/%s/school-events/%s/gallery', $schoolBranchId, $schoolEvent->id);

        $objectKey = $disk->putFileAs($directory, $picture, Str::uuid() . '.' . $extension, ['ContentType' => $mimeType]);

        if (!$objectKey) {
            throw new AppException(
                "R2 putFileAs returned false for a school event picture.",
                500,
                "Upload Failed",
                "Failed to upload the school event picture.",
                null
            );
        }

        return [
            'object_key' => $objectKey,
            'mime_type' => $mimeType,
            'size_bytes' => $picture->getSize(),
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
        ];
    }

    private function validatePicture(UploadedFile $picture, AssetUploadPolicy $policy): void
    {
        $mimeType = $picture->getMimeType();
        $allowedMimeTypes = $policy->allowed_mime_types ?? [];

        if (is_string($allowedMimeTypes)) {
            $allowedMimeTypes = json_decode($allowedMimeTypes, true) ?? [];
        }

        $allowedMimeTypes = array_intersect($allowedMimeTypes, ['image/jpeg', 'image/png', 'image/webp']);

        if (!$picture->isValid() || !in_array($mimeType, $allowedMimeTypes, true)) {
            throw new AppException(
                "Invalid picture uploaded for school event. Detected MIME type: {$mimeType}",
                422,
                "Invalid Image Format",
                "Please upload a valid JPEG, PNG, or WebP image.",
                null
            );
        }

        if ($policy->max_file_size_bytes !== null && $picture->getSize() > $policy->max_file_size_bytes) {
            throw new AppException(
                "School event picture exceeds the configured file size limit.",
                422,
                "Image Too Large",
                "Each event image must be " . round($policy->max_file_size_bytes / 1048576, 1) . " MB or smaller.",
                null
            );
        }
    }

    private function policy(): AssetUploadPolicy
    {
        $policy = AssetUploadPolicy::query()
            ->where('asset_type', 'school_event_image')
            ->first();

        if (!$policy || !$policy->is_enabled) {
            throw new AppException(
                "School event image upload policy is missing or disabled.",
                500,
                "Upload Unavailable",
                "Event image uploads are currently unavailable.",
                null
            );
        }

        return $policy;
    }

    private function currentImageCount(SchoolEvent $schoolEvent, string $schoolBranchId): int
    {
        return SchoolEventImage::query()
            ->where('school_branch_id', $schoolBranchId)
            ->where('event_id', $schoolEvent->id)
            ->where('is_current', true)
            ->count();
    }

    private function lockEvent(SchoolEvent $schoolEvent, string $schoolBranchId): SchoolEvent
    {
        $lockedEvent = SchoolEvent::query()
            ->whereKey($schoolEvent->getKey())
            ->lockForUpdate()
            ->first();

        if (!$lockedEvent) {
            throw new AppException(
                "School event not found during image transaction.",
                404,
                "School Event Not Found",
                "The requested school event could not be found.",
                null
            );
        }

        $this->ensureBranch($lockedEvent, $schoolBranchId);

        return $lockedEvent;
    }

    private function ensureBranch(SchoolEvent $schoolEvent, string $schoolBranchId): void
    {
        if ((string) $schoolEvent->school_branch_id !== (string) $schoolBranchId) {
            throw new AppException(
                "School event does not belong to school branch {$schoolBranchId}.",
                422,
                "Invalid School Branch",
                "The school event does not belong to this school branch.",
                null
            );
        }
    }
}
