<?php

namespace App\Models\Constraint;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConstraintCategory extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'name',
        'description',
        'program_name',
        'status'
    ];

    public $incrementing = false;
    public $table = "constraint_categories";
    public $keyType = 'string';

    public function semesterTimetableConstraint(): HasMany
    {
        return $this->hasMany(SemesterTimetableConstraint::class);
    }
}
