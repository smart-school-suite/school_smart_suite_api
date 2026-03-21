<?php

namespace App\Models\Constraint;

use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SemTimetableConstraintBlocker extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        "constraint_id",
        "blocker_id"
    ];

    public $incrementing = false;
    public $table = "constraint_blockers";
    public $keyType = 'string';

    public function constraint(): BelongsTo
    {
        return $this->belongsTo(SemTimetableConstraint::class, 'constraint_id');
    }

    public function blocker(): BelongsTo
    {
        return $this->belongsTo(SemTimetableBlocker::class, 'blocker_id');
    }
}
