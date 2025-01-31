<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Permission extends SpatiePermission
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'uuid';
    public $incrementing = 'false';
    protected $keyType = 'string';
}
