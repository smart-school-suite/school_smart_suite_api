<?php

namespace App\Models\Constraint;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConstraintType extends Model
{

    use GeneratesUuid;
    protected $fillable = [
        'name',
        'program_name',
        'status',
        'description'
    ];

    public $table = "constraint_types";
    public $incrementing = false;
    public $keyType = 'string';

    public function semesterTimetableConstraint(): HasMany
    {
        return $this->hasMany(SemesterTimetableConstraint::class);
    }
}
