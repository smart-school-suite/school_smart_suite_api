<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\GeneratesUuid;
class CurrentElectionWinners extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'total_votes',
        'election_type_id',
        'election_role_id',
        'student_id',
        'school_branch_id',
        'election_id'
    ];

    public function election(): BelongsTo {
         return $this->belongsTo(Elections::class, 'election_id');
    }
    public function electionType(): BelongsTo {
        return $this->belongsTo(ElectionType::class, 'election_type_id');
    }

    public function electionRole(): BelongsTo {
        return $this->belongsTo(ElectionRoles::class, 'election_role_id');
    }

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class, 'student_id');
    }
    public $table = 'current_election_winners';
    public $incrementing = 'false';
    public $keyType = 'string';

}
