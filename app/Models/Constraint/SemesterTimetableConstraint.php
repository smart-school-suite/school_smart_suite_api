<?php

namespace App\Models\Constraint;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SemesterTimetableConstraint extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'name',
        'program_name',
        'code',
        'description',
        'constraint_type_id',
        'constraint_category_id',
        'status'
    ];

    public $incrementing = false;
    public $table = "semester_timetable_constraints";
    public $keyType = 'string';

    public function constraintType(): BelongsTo
    {
        return $this->belongsTo(ConstraintType::class, 'constraint_type_id');
    }
    public function constraintCategory(): BelongsTo
    {
        return $this->belongsTo(ConstraintCategory::class, 'constraint_category_id');
    }
}
