<?php

namespace App\Models\Asset;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetUploadPolicy extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'asset_type',
        'max_file_size_bytes',
        'allowed_mime_types',
        'max_images_per_owner',
        'max_changes',
        'period_days',
        'is_enabled'
    ];

    protected $casts = [
        'max_file_size_bytes' => "integer",
        'max_images_per_owner' => 'integer',
        'max_changes' => "integer",
        'period_days' => "integer",
        'is_enabled' => 'boolean',
        'allowed_mime_types' => 'array',
    ];
    public $incrementing = false;
    public $keyType = 'string';
    public $table = 'asset_upload_policies';


}
