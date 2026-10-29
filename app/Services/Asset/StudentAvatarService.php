<?php

namespace App\Services\Asset;

use App\Models\Student;
use App\Models\Asset\StudentAvatar;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Exceptions\AppException;
use Throwable;

class StudentAvatarService
{
    public function uploadAvatar(
        Student $student,
        UploadedFile $picture,
        string $schoolBranchId
    ): StudentAvatar {
        $disk = Storage::disk('r2');
        $objectKey = null;
        $avatarSaved = false;
        $maxChanges = 3;
        $periodDays = 30;

        try {
            if (!$picture->isValid()) {
                throw new AppException(
                    "Invalid picture uploaded for student: {$picture->getErrorMessage()}",
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
                    "Invalid picture uploaded for student. Detected MIME type: {$mimeType}",
                    422,
                    "Invalid Image Format",
                    "Please upload a valid JPEG, PNG, or WebP image.",
                    null
                );
            }

            if ((string) $student->school_branch_id !== (string) $schoolBranchId) {
                throw new AppException(
                    "Student does not belong to school branch {$schoolBranchId}.",
                    422,
                    "Invalid School Branch",
                    "The student does not belong to this school branch.",
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
                'schools/%s/students/%s/profile',
                $schoolBranchId,
                $student->id
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
                    "R2 putFileAs returned false for the student profile picture.",
                    500,
                    "Upload Failed",
                    "Failed to upload the student profile picture.",
                    null
                );
            }

            if (!$disk->exists($objectKey)) {
                throw new AppException(
                    "R2 object {$objectKey} does not exist after upload.",
                    500,
                    "Upload Failed",
                    "Failed to upload the student profile picture.",
                    null
                );
            }

            $avatar = DB::transaction(function () use (
                $student,
                $schoolBranchId,
                $objectKey,
                $mimeType,
                $fileSize,
                $width,
                $height,
                $maxChanges,
                $periodDays
            ) {
                $lockedStudent = Student::query()
                    ->whereKey($student->getKey())
                    ->lockForUpdate()
                    ->first();

                if (!$lockedStudent) {
                    throw new AppException(
                        "Student not found during avatar upload transaction.",
                        404,
                        "Student Not Found",
                        "The requested student could not be found.",
                        null
                    );
                }

                if ((string) $lockedStudent->school_branch_id !== (string) $schoolBranchId) {
                    throw new AppException(
                        "Student branch mismatch during avatar upload transaction for branch {$schoolBranchId}.",
                        422,
                        "Invalid School Branch",
                        "The student does not belong to this school branch.",
                        null
                    );
                }

                $currentPicture = StudentAvatar::query()
                    ->where('school_branch_id', $schoolBranchId)
                    ->where('student_id', $lockedStudent->id)
                    ->where('is_current', true)
                    ->lockForUpdate()
                    ->first();

                if ($currentPicture) {
                    $changes = StudentAvatar::query()
                        ->where('school_branch_id', $schoolBranchId)
                        ->where('student_id', $lockedStudent->id)
                        ->whereNotNull('replaced_at')
                        ->where('replaced_at', '>=', now()->subDays($periodDays))
                        ->count();

                    if ($changes >= $maxChanges) {
                        throw new AppException(
                            "Profile picture change limit exceeded for student ID {$lockedStudent->id}.",
                            422,
                            "Change Limit Reached",
                            "You have reached the profile picture change limit. Please try again later.",
                            null
                        );
                    }

                    $currentPicture->forceFill([
                        'is_current' => false,
                        'replaced_at' => now(),
                    ])->save();
                }

                $avatar = new StudentAvatar();

                $avatar->forceFill([
                    'id' => (string) Str::uuid(),
                    'school_branch_id' => $schoolBranchId,
                    'student_id' => $lockedStudent->id,
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
                "An error occurred while uploading the student profile picture: {$e->getMessage()}",
                500,
                "Upload Error",
                "We encountered an issue while uploading the profile picture.",
                null
            );
        }
    }

    public function removeAvatar(
        Student $student,
        string $schoolBranchId
    ): void {
        $objectKey = null;

        try {
            if ((string) $student->school_branch_id !== (string) $schoolBranchId) {
                throw new AppException(
                    "Student does not belong to school branch {$schoolBranchId}.",
                    422,
                    "Invalid School Branch",
                    "The student does not belong to this school branch.",
                    null
                );
            }

            DB::transaction(function () use ($student, $schoolBranchId, &$objectKey) {
                $lockedStudent = Student::query()
                    ->whereKey($student->getKey())
                    ->lockForUpdate()
                    ->first();

                if (!$lockedStudent) {
                    throw new AppException(
                        "Student not found during avatar removal transaction.",
                        404,
                        "Student Not Found",
                        "The requested student could not be found.",
                        null
                    );
                }

                if ((string) $lockedStudent->school_branch_id !== (string) $schoolBranchId) {
                    throw new AppException(
                        "Student branch mismatch during avatar removal transaction for branch {$schoolBranchId}.",
                        422,
                        "Invalid School Branch",
                        "The student does not belong to this school branch.",
                        null
                    );
                }

                $currentPicture = StudentAvatar::query()
                    ->where('school_branch_id', $schoolBranchId)
                    ->where('student_id', $lockedStudent->id)
                    ->where('is_current', true)
                    ->lockForUpdate()
                    ->first();

                if (!$currentPicture) {
                    throw new AppException(
                        "No current profile picture found for student ID {$lockedStudent->id}.",
                        404,
                        "Avatar Not Found",
                        "The student does not have a profile picture to remove.",
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
                "An error occurred while removing the student profile picture: {$e->getMessage()}",
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
