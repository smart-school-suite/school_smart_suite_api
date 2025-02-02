<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectionCandidates extends Model
{
    use HasFactory;

    protected $fillable = [
        "election_status",
        "isActive",
        "application_id",
        "school_branch_id"
    ];

    public $incrementing = "false";
    public $keyType = "string";

    public $table = "election_candidates";

    public function electionResults(): HasMany {
         return $this->hasMany(ElectionResults::class);
    }

    public function electionCandidate(): HasMany {
         return $this->hasMany(ElectionCandidates::class);
    }

    public function electionVotes(): HasMany {
         return $this->hasMany(ElectionVotes::class);
    }

}
