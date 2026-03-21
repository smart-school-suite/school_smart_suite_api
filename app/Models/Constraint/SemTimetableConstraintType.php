<?php

namespace App\Models\Constraint;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SemTimetableConstraintType extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'name',
        'key',
        'description',
        'status'
    ];

    public $incrementing = false;
    public $keyType = 'string';
    public $table = 'constraint_types';

    public function constraint(): HasMany
    {
        return $this->hasMany(SemTimetableConstraint::class);
    }
}
