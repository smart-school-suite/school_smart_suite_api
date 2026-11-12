<?php

namespace App\Models\Election;

use App\Models\Schoolbranches;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * An election position (e.g. President) belonging to an election type.
 * Stored in election_roles for backwards compatibility.
 */
class ElectionRole extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'election_roles';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'description',
        'status',
        'seats',
        'election_type_id',
        'school_branch_id',
    ];

    protected $casts = [
        'seats' => 'integer',
    ];

    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function electionType(): BelongsTo
    {
        return $this->belongsTo(ElectionType::class, 'election_type_id');
    }

    public function officeTerm(): HasOne
    {
        return $this->hasOne(ElectionOfficeTerm::class, 'election_role_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(ElectionApplication::class, 'election_role_id');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(ElectionCandidate::class, 'election_role_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ElectionVote::class, 'position_id');
    }

    public function officeHolders(): HasMany
    {
        return $this->hasMany(ElectionOfficeHolder::class, 'election_role_id');
    }

    public function currentOfficeHolders(): HasMany
    {
        return $this->officeHolders()->where('status', ElectionOfficeHolder::STATUS_ACTIVE);
    }
}
