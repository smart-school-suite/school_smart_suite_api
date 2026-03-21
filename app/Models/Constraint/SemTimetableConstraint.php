<?php

namespace App\Models\Constraint;

use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SemTimetableConstraint extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'name',
        'key',
        'description',
        'constraint_type_id',
        'constraint_category_id',
        'status',
        'is_suggestable',
        'is_blockable'
    ];

    protected $casts = [
        'is_suggestable' => 'boolean',
        'is_blockable' => 'boolean'
    ];
    public $incrementing = false;
    public $table = "sem_constraints";
    public $keyType = 'string';

    public function constraintType(): BelongsTo
    {
        return $this->belongsTo(SemTimetableConstraintType::class, 'constraint_type_id');
    }
    public function constraintCategory(): BelongsTo
    {
        return $this->belongsTo(SemTimetableConstraintCategory::class, 'constraint_category_id');
    }
    public function constraintBlocker(): HasMany
    {
        return $this->hasMany(SemTimetableConstraintBlocker::class);
    }
}
