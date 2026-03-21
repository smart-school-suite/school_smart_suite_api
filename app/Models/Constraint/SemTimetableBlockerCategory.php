<?php

namespace App\Models\Constraint;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SemTimetableBlockerCategory extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'name',
        'key',
        'description',
        'status'
    ];

    public $incrementing = false;
    public $table = "sem_blocker_categories";
    public $keyType = 'string';

    public function blocker(): HasMany
    {
        return $this->hasMany(SemTimetableBlocker::class);
    }
}
