<?php

namespace App\Services\Asset;

use App\Exceptions\AppException;
use App\Models\Asset\SchoolAdminAvatar;
use App\Models\Asset\AssetUploadPolicy;
use App\Models\SchoolAdmin;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class SchoolAdminAvatarService
{
    public function uploadAvatar(
        SchoolAdmin $schoolAdmin,
        UploadedFile $picture,
        string $schoolBranchId
    ): SchoolAdminAvatar {
        $disk = Storage::disk('r2');
        $objectKey = null;
        $avatarSaved = false;

        try {
            if (!$picture->isValid()) {
                throw new AppException(
                    "Invalid picture uploaded for school admin: {$picture->getErrorMessage()}",
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
                    "Invalid picture uploaded for school admin. Detected MIME type: {$mimeType}",
                    422,
                    "Invalid Image Format",
                    "Please upload a valid JPEG, PNG, or WebP image.",
                    null
                );
            }

            if ((string) $schoolAdmin->school_branch_id !== (string) $schoolBranchId) {
                throw new AppException(
                    "School admin does not belong to school branch {$schoolBranchId}.",
                    422,
                    "Invalid School Branch",
                    "The school admin does not belong to this school branch.",
                    null
                );
            }

            $policy = AssetUploadPolicy::query()
                ->where('asset_type', 'school_admin_profile_picture')
                ->first();

            if (!$policy || !$policy->is_enabled) {
                throw new AppException(
                    "School admin profile picture upload policy is missing or disabled.",
                    500,
                    "Upload Unavailable",
                    "Profile picture uploads are currently unavailable.",
                    null
                );
            }

            $allowedMimeTypes = $policy->allowed_mime_types ?? [];

            if (is_string($allowedMimeTypes)) {
                $allowedMimeTypes = json_decode($allowedMimeTypes, true) ?? [];
            }

            if (!in_array($mimeType, $allowedMimeTypes, true)) {
                throw new AppException(
                    "Uploaded MIME type {$mimeType} is not allowed by the school admin profile picture policy.",
                    422,
                    "Invalid Image Format",
                    "This image format is not supported.",
                    null
                );
            }

            if ($policy->max_file_size_bytes !== null && $fileSize > $policy->max_file_size_bytes) {
                throw new AppException(
                    "School admin profile picture exceeds the configured file size limit.",
                    422,
                    "Image Too Large",
                    "The image exceeds the maximum permitted file size.",
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
                'schools/%s/school-admins/%s/profile',
                $schoolBranchId,
                $schoolAdmin->id
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
                    "R2 putFileAs returned false for the school admin profile picture.",
                    500,
                    "Upload Failed",
                    "Failed to upload the school admin profile picture.",
                    null
                );
            }

            if (!$disk->exists($objectKey)) {
                throw new AppException(
                    "R2 object {$objectKey} does not exist after upload.",
                    500,
                    "Upload Failed",
                    "Failed to upload the school admin profile picture.",
                    null
                );
            }

            $avatar = DB::transaction(function () use (
                $schoolAdmin,
                $schoolBranchId,
                $objectKey,
                $mimeType,
                $fileSize,
                $width,
                $height,
                $policy
            ) {
                $lockedSchoolAdmin = SchoolAdmin::query()
                    ->where('id', $schoolAdmin->id)
                    ->lockForUpdate()
                    ->first();

                if (!$lockedSchoolAdmin) {
                    throw new AppException(
                        "School admin not found during avatar upload transaction.",
                        404,
                        "School Admin Not Found",
                        "The requested school admin could not be found.",
                        null
                    );
                }

                if ((string) $lockedSchoolAdmin->school_branch_id !== (string) $schoolBranchId) {
                    throw new AppException(
                        "School admin branch mismatch during avatar upload transaction.",
                        422,
                        "Invalid School Branch",
                        "The school admin does not belong to this school branch.",
                        null
                    );
                }

                $currentPicture = SchoolAdminAvatar::query()
                    ->where('school_branch_id', $schoolBranchId)
                    ->where('school_admin_id', $lockedSchoolAdmin->id)
                    ->where('is_current', true)
                    ->lockForUpdate()
                    ->first();

                if ($currentPicture) {
                    if ($policy->max_changes !== null) {
                        $query = SchoolAdminAvatar::query()
                            ->where('school_branch_id', $schoolBranchId)
                            ->where('school_admin_id', $lockedSchoolAdmin->id)
                            ->whereNotNull('replaced_at');

                        if ($policy->period_days !== null) {
                            $query->where('replaced_at', '>=', now()->subDays($policy->period_days));
                        }

                        if ($query->count() >= $policy->max_changes) {
                            throw new AppException(
                                "Profile picture change limit exceeded for school admin ID {$lockedSchoolAdmin->id}.",
                                422,
                                "Change Limit Reached",
                                "You have reached the profile picture change limit. Please try again later.",
                                null
                            );
                        }
                    }

                    $currentPicture->forceFill([
                        'is_current' => false,
                        'replaced_at' => now(),
                    ])->save();
                }

                $avatar = new SchoolAdminAvatar();

                $avatar->forceFill([
                    'id' => (string) Str::uuid(),
                    'school_branch_id' => $schoolBranchId,
                    'school_admin_id' => $lockedSchoolAdmin->id,
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
                "An error occurred while uploading the school admin profile picture: {$e->getMessage()}",
                500,
                "Upload Error",
                "We encountered an issue while uploading the profile picture.",
                null
            );
        }
    }

    public function removeAvatar(
        SchoolAdmin $schoolAdmin,
        string $schoolBranchId
    ): void {
        $objectKey = null;

        try {
            if ((string) $schoolAdmin->school_branch_id !== (string) $schoolBranchId) {
                throw new AppException(
                    "School admin does not belong to school branch {$schoolBranchId}.",
                    422,
                    "Invalid School Branch",
                    "The school admin does not belong to this school branch.",
                    ['school_admin_id' => ['The school admin does not belong to this school branch.']]
                );
            }

            DB::transaction(function () use ($schoolAdmin, $schoolBranchId, &$objectKey) {
                $lockedSchoolAdmin = SchoolAdmin::query()
                    ->whereKey($schoolAdmin->getKey())
                    ->lockForUpdate()
                    ->first();

                if (!$lockedSchoolAdmin) {
                    throw new AppException(
                        "School admin not found during avatar removal transaction.",
                        404,
                        "School Admin Not Found",
                        "The requested school admin could not be found.",
                        null
                    );
                }

                if ((string) $lockedSchoolAdmin->school_branch_id !== (string) $schoolBranchId) {
                    throw new AppException(
                        "School admin branch mismatch during avatar removal transaction for branch {$schoolBranchId}.",
                        422,
                        "Invalid School Branch",
                        "The school admin does not belong to this school branch.",
                        ['school_admin_id' => ['The school admin does not belong to this school branch.']]
                    );
                }

                $currentPicture = SchoolAdminAvatar::query()
                    ->where('school_branch_id', $schoolBranchId)
                    ->where('school_admin_id', $lockedSchoolAdmin->id)
                    ->where('is_current', true)
                    ->lockForUpdate()
                    ->first();

                if (!$currentPicture) {
                    throw new AppException(
                        "No current profile picture found for school admin ID {$lockedSchoolAdmin->id}.",
                        404,
                        "Avatar Not Found",
                        "The school admin does not have a profile picture to remove.",
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
                "An error occurred while removing the school admin profile picture: {$e->getMessage()}",
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
