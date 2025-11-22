<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Elections extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        "election_type_id",
        "application_start",
        "application_end",
        "voting_start",
        "voting_end",
        'voting_status',
        'application_status',
        'school_year',
        "is_results_published",
        "school_branch_id",
        'status'
    ];

    public $table = "elections";

    public $incrementing = "false";

    public $keyType = "string";

    public function electionType(): BelongsTo {
        return $this->belongsTo(ElectionType::class, 'election_type_id');
    }
    public function pastElectionWinners(): HasMany {
        return $this->hasMany(PastElectionWinners::class);
    }
    public function currentElectionWinners() : BelongsTo {
        return $this->belongsTo(CurrentElectionWinners::class);
    }
    public function schoolbranches(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function electionApplication(): HasMany {
         return $this->hasMany(ElectionApplication::class,'election_id');
    }

    public function electionRole(): hasMany {
         return $this->hasMany(ElectionRoles::class);
    }

    public function electionResults(): HasMany {
         return $this->hasMany(ElectionResults::class);
    }

    public function electionVotes(): HasMany {
         return $this->hasMany(ElectionVotes::class);
    }

    public function electionParticipants(): HasMany {
         return $this->hasMany(ElectionParticipants::class);
    }

}
