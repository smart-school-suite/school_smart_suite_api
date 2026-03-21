<?php

namespace App\Models\Constraint;

use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SemTimetableConstraintCategory extends Model
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
    public $table = 'constraint_categories';

    public function constraints(): HasMany
    {
        return $this->hasMany(SemTimetableConstraint::class, 'constraint_category_id');
    }
}
