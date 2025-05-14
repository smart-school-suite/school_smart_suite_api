<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PermissionCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'status',
        'description'
    ];

    public $incrementing = 'false';
    public $table = 'permission_category';
    public $keyType = 'string';

    public function permission(): HasMany {
        return $this->hasMany(Permission::class);
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->id = (string) Str::uuid();
        });
    }
}
