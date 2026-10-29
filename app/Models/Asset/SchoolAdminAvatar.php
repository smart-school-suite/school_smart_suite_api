<?php

namespace App\Models\Asset;

use App\Models\Schooladmin;
use App\Models\Schoolbranches;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
