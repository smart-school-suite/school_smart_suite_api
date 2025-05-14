<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permission extends SpatiePermission
{
    use HasFactory, HasUuids;

    protected $fillable  = [
        'permission_category_id'
    ];

    public function permissionCategory(): BelongsTo {
        return $this->belongsTo(PermissionCategory::class, 'permission_category_id');
    }
    protected $primaryKey = 'uuid';
    public $incrementing = 'false';
    protected $keyType = 'string';
}
