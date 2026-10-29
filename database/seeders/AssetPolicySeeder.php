<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset\AssetUploadPolicy;

class AssetPolicySeeder extends Seeder
{
    public function run(): void
    {
        $policies = [
            [
                'asset_type' => 'student_profile_picture',
                'max_file_size_bytes' => 2097152, // 2 MB
                'allowed_mime_types' => [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ],
                'max_images_per_owner' => 1,
                'max_changes' => 1,
                'period_days' => 30,
                'is_enabled' => true,
            ],
            [
                'asset_type' => 'teacher_profile_picture',
                'max_file_size_bytes' => 2097152, // 2 MB
                'allowed_mime_types' => [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ],
                'max_images_per_owner' => 1,
                'max_changes' => 1,
                'period_days' => 30,
                'is_enabled' => true,
            ],
            [
                'asset_type' => 'school_admin_profile_picture',
                'max_file_size_bytes' => 2097152, // 2 MB
                'allowed_mime_types' => [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ],
                'max_images_per_owner' => 1,
                'max_changes' => 1,
                'period_days' => 30,
                'is_enabled' => true,
            ],
            [
                'asset_type' => 'school_logo',
                'max_file_size_bytes' => 2097152, // 2 MB
                'allowed_mime_types' => [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                    'image/svg+xml',
                ],
                'max_images_per_owner' => 1,
                'max_changes' => 1,
                'period_days' => 30,
                'is_enabled' => true,
            ],
            [
                'asset_type' => 'school_event_image',
                'max_file_size_bytes' => 2097152, // 2 MB
                'allowed_mime_types' => [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ],
                'max_images_per_owner' => 10,
                'max_changes' => null,
                'period_days' => null,
                'is_enabled' => true,
            ],
        ];

        foreach ($policies as $policy) {
            AssetUploadPolicy::updateOrCreate(
                ['asset_type' => $policy['asset_type']],
                $policy
            );
        }
    }
}
