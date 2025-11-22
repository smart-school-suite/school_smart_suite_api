<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PastElectionWinners extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'total_votes',
        'election_id',
        'election_role_id',
        'student_id',
        'election_type_id',
        'school_branch_id'
    ];

    protected $cast = [
         'total_votes' => 'integer'
    ];
    public $table = 'past_election_winners';
    public $incrementing = 'false';
    public $keyType = 'string';

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
}
