<?php

namespace App\Services\Asset;

use App\Exceptions\AppException;
use App\Models\Asset\TeacherAvatar;
use App\Models\Teacher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class TeacherAvatarService
{
    public function uploadAvatar(
        Teacher $teacher,
        UploadedFile $picture,
        string $schoolBranchId
    ): TeacherAvatar {
        $disk = Storage::disk('r2');
        $objectKey = null;
        $avatarSaved = false;
        $maxChanges = 3;
        $periodDays = 30;

        try {
            if (!$picture->isValid()) {
                throw new AppException(
                    "Invalid picture uploaded for teacher: {$picture->getErrorMessage()}",
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
                    "Invalid picture uploaded for teacher. Detected MIME type: {$mimeType}",
                    422,
                    "Invalid Image Format",
                    "Please upload a valid JPEG, PNG, or WebP image.",
                    null
                );
            }

            if ((string) $teacher->school_branch_id !== (string) $schoolBranchId) {
                throw new AppException(
                    "Teacher does not belong to school branch {$schoolBranchId}.",
                    422,
                    "Invalid School Branch",
                    "The teacher does not belong to this school branch.",
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

            $directory = sprintf(
                'schools/%s/teachers/%s/profile',
                $schoolBranchId,
                $teacher->id
            );

            $fileName = Str::uuid() . '.' . $extension;

            $objectKey = $disk->putFileAs(
                $directory,
                $picture,
                $fileName,
                ['ContentType' => $mimeType]
            );

            if (!$objectKey) {
                throw new AppException(
                    "R2 putFileAs returned false for the teacher profile picture.",
                    500,
                    "Upload Failed",
                    "Failed to upload the teacher profile picture.",
                    null
                );
            }

            if (!$disk->exists($objectKey)) {
                throw new AppException(
                    "R2 object {$objectKey} does not exist after upload.",
                    500,
                    "Upload Failed",
                    "Failed to upload the teacher profile picture.",
                    null
                );
            }

            $avatar = DB::transaction(function () use (
                $teacher,
                $schoolBranchId,
                $objectKey,
                $mimeType,
                $fileSize,
                $width,
                $height,
                $maxChanges,
                $periodDays
            ) {
                $lockedTeacher = Teacher::query()
                    ->whereKey($teacher->getKey())
                    ->lockForUpdate()
                    ->first();

                if (!$lockedTeacher) {
                    throw new AppException(
                        "Teacher not found during avatar upload transaction.",
                        404,
                        "Teacher Not Found",
                        "The requested teacher could not be found.",
                        null
                    );
                }

                if ((string) $lockedTeacher->school_branch_id !== (string) $schoolBranchId) {
                    throw new AppException(
                        "Teacher branch mismatch during avatar upload transaction for branch {$schoolBranchId}.",
                        422,
                        "Invalid School Branch",
                        "The teacher does not belong to this school branch.",
                        null
                    );
                }

                $currentPicture = TeacherAvatar::query()
                    ->where('school_branch_id', $schoolBranchId)
                    ->where('teacher_id', $lockedTeacher->id)
                    ->where('is_current', true)
                    ->lockForUpdate()
                    ->first();

                $changes = TeacherAvatar::query()
                    ->where('school_branch_id', $schoolBranchId)
                    ->where('teacher_id', $lockedTeacher->id)
                    ->whereNotNull('replaced_at')
                    ->where('replaced_at', '>=', now()->subDays($periodDays))
                    ->count();

                if ($changes >= $maxChanges) {
                    throw new AppException(
                        "Profile picture change limit exceeded for teacher ID {$lockedTeacher->id}.",
                        422,
                        "Change Limit Reached",
                        "You have reached the profile picture change limit. Please try again later.",
                        null
                    );
                }

                if ($currentPicture) {
                    $currentPicture->forceFill([
                        'is_current' => false,
                        'replaced_at' => now(),
                    ])->save();
                }

                $avatar = new TeacherAvatar();

                $avatar->forceFill([
                    'id' => (string) Str::uuid(),
                    'school_branch_id' => $schoolBranchId,
                    'teacher_id' => $lockedTeacher->id,
                    'object_key' => $objectKey,
                    'mime_type' => $mimeType,
                    'size_bytes' => $fileSize,
                    'width' => $width,
                    'height' => $height,
                    'is_current' => true,
                ])->save();

                return $avatar;
            });

            $avatarSaved = true;

            return $avatar;
        } catch (AppException $e) {
            if ($objectKey && !$avatarSaved) {
                try {
                    $disk->delete($objectKey);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $e;
        } catch (Throwable $e) {
            if ($objectKey && !$avatarSaved) {
                try {
                    $disk->delete($objectKey);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw new AppException(
                "An error occurred while uploading the teacher profile picture: {$e->getMessage()}",
                500,
                "Upload Error",
                "We encountered an issue while uploading the profile picture.",
                null
            );
        }
    }

    public function removeAvatar(
        Teacher $teacher,
        string $schoolBranchId
    ): void {
        $objectKey = null;

        try {
            if ((string) $teacher->school_branch_id !== (string) $schoolBranchId) {
                throw new AppException(
                    "Teacher does not belong to school branch {$schoolBranchId}.",
                    422,
                    "Invalid School Branch",
                    "The teacher does not belong to this school branch.",
                    null
                );
            }

            DB::transaction(function () use ($teacher, $schoolBranchId, &$objectKey) {
                $lockedTeacher = Teacher::query()
                    ->whereKey($teacher->getKey())
                    ->lockForUpdate()
                    ->first();

                if (!$lockedTeacher) {
                    throw new AppException(
                        "Teacher not found during avatar removal transaction.",
                        404,
                        "Teacher Not Found",
                        "The requested teacher could not be found.",
                        null
                    );
                }

                if ((string) $lockedTeacher->school_branch_id !== (string) $schoolBranchId) {
                    throw new AppException(
                        "Teacher branch mismatch during avatar removal transaction for branch {$schoolBranchId}.",
                        422,
                        "Invalid School Branch",
                        "The teacher does not belong to this school branch.",
                        null
                    );
                }

                $currentPicture = TeacherAvatar::query()
                    ->where('school_branch_id', $schoolBranchId)
                    ->where('teacher_id', $lockedTeacher->id)
                    ->where('is_current', true)
                    ->lockForUpdate()
                    ->first();

                if (!$currentPicture) {
                    throw new AppException(
                        "No current profile picture found for teacher ID {$lockedTeacher->id}.",
                        404,
                        "Avatar Not Found",
                        "The teacher does not have a profile picture to remove.",
                        null
                    );
                }

                $objectKey = $currentPicture->object_key;

                $currentPicture->forceFill([
                    'is_current' => false,
                    'replaced_at' => now(),
                ])->save();
            });
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "An error occurred while removing the teacher profile picture: {$e->getMessage()}",
                500,
                "Removal Error",
                "We encountered an issue while removing the profile picture.",
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
