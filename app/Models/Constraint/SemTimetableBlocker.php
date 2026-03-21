<?php

namespace App\Models\Constraint;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SemTimetableBlocker extends Model
{
    use GeneratesUuid;

    protected $fillable = [
        'name',
        'key',
        'description',
        'status',
        'is_resolvable',
        'sem_blocker_category_id'
    ];

    protected $casts = [
        'is_resolvable' => 'boolean'
    ];
    public $incrementing = false;
    public $table = "sem_blockers";
    public $keyType = 'string';

    public function blockerCategory(): BelongsTo
    {
        return $this->belongsTo(SemTimetableBlockerCategory::class, 'sem_blocker_category_id');
    }
}
