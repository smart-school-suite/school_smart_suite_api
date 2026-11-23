<?php

namespace App\Models\Asset;

use App\Models\Schooladmin;
use App\Models\Schoolbranches;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SchoolAdminAvatar extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'object_key',
        'mime_type',
        'size_bytes',
        'height',
        'width',
        'is_current',
        'created_at',
        'replaced_at',
        'school_branch_id',
        'school_admin_id'
    ];

    protected $casts = [
        'is_current' => "boolean",
        'replaced_at' => "datetime",
        'size_bytes' => "integer",
        'height' => 'integer',
        'width' => 'integer',
    ];

    protected $appends = ['url'];

    const UPDATED_AT = null;
    public $incrementing = false;
    public $table = "school_admin_avatars";
    public $keyType = 'string';

    public function schoolAdmin(): BelongsTo
    {
        return $this->belongsTo(Schooladmin::class, 'school_admin_id');
    }

    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function getUrlAttribute(): ?string
    {
        if (!$this->object_key) {
            return null;
        }

        try {
            $disk = Storage::disk('r2');

            if (config('filesystems.disks.r2.url')) {
                return $disk->url($this->object_key);
            }

            return $disk->temporaryUrl(
                $this->object_key,
                now()->addMinutes((int) config('filesystems.disks.r2.signed_url_minutes', 60))
            );
        } catch (Throwable $exception) {
            report($exception);
            return null;
        }
    }
}
