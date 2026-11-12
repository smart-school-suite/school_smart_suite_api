<?php

namespace App\Models\Election;

use App\Models\Schoolbranches;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tenure rules for a position: duration in academic years, re-election
 * and maximum number of terms.
 */
class ElectionOfficeTerm extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'election_office_terms';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'school_branch_id',
        'election_role_id',
        'duration_academic_years',
        'allow_reelection',
        'maximum_terms',
    ];

    protected $casts = [
        'duration_academic_years' => 'integer',
        'allow_reelection' => 'boolean',
        'maximum_terms' => 'integer',
    ];

    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(ElectionRole::class, 'election_role_id');
    }

    /** Maximum number of terms a single holder may serve (null = unlimited). */
    public function effectiveMaximumTerms(): ?int
    {
        if (!$this->allow_reelection) {
            return 1;
        }
        return $this->maximum_terms;
    }
}
