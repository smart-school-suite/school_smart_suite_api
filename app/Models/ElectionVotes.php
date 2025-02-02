<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ElectionVotes extends Model
{
    use HasFactory;

    protected $fillable = [
        "school_branch_id",
        "election_id",
        "candidate_id",
        "student_id",
        "position_id",
        "voted_at"
    ];

    public $table = "election_votes";

    public $incrementing = "false";

    public $keyType = "string";

    public function schoolbranches(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function election(): BelongsTo {
         return $this->belongsTo(Elections::class, 'election_id');
    }

    public function electionCandidate(): BelongsTo {
         return $this->belongsTo(ElectionCandidates::class, 'candidate_id');
    }

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function electionRole(): BelongsTo {
         return $this->belongsTo(ElectionRoles::class, "position_id");
    }
}
