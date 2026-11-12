<?php

namespace App\Models\Election;

use App\Models\Schoolbranches;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A kind of election (e.g. Student Union Government) and its positions.
 */
class ElectionType extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'election_types';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'election_title',
        'description',
        'status',
        'school_branch_id',
    ];

    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function elections(): HasMany
    {
        return $this->hasMany(Election::class, 'election_type_id');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(ElectionRole::class, 'election_type_id');
    }
}
