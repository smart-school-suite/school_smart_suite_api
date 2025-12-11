<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permission extends SpatiePermission
{
    use HasFactory, HasUuids;

    protected $fillable  = [
        'name',
        'guard_name',
        'permission_category_id',
        'desc_name',
        'desc_text'
    ];

    public function permissionCategory(): BelongsTo {
        return $this->belongsTo(PermissionCategory::class, 'permission_category_id');
    }
    protected $primaryKey = 'uuid';
    public $incrementing = 'false';
    protected $keyType = 'string';
}
