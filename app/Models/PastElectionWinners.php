<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PastElectionWinners extends Model
{
    use HasFactory;

    protected $fillable = [
        'total_votes',
        'election_id',
        'election_role_id',
        'student_id',
        'school_branch_id'
    ];

    public $table = 'past_election_winners';
    public $incrementing = 'false';
    public $keyType = 'string';

    public function election(): BelongsTo {
         return $this->belongsTo(Elections::class, 'election_id');
    }

    public function electionRole(): BelongsTo {
        return $this->belongsTo(ElectionRoles::class, 'election_role_id');
    }

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class, 'student_id');
    }
    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });

    }
}
