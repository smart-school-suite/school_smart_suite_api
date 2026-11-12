<?php

namespace App\Models\Election;

use App\Models\Schoolbranches;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Resolved eligibility of a Student / Teacher / Schooladmin for an election.
 * Rows are materialised from the election's participants JSON configuration.
 */
class ElectionParticipant extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'election_participants';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'school_branch_id',
        'election_id',
        'participantable_type',
        'participantable_id',
        'can_vote',
        'can_apply',
    ];

    protected $casts = [
        'can_vote' => 'boolean',
        'can_apply' => 'boolean',
    ];

    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class, 'election_id');
    }

    public function participantable(): MorphTo
    {
        return $this->morphTo();
    }
}
