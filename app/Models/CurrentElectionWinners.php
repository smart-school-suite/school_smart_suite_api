<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurrentElectionWinners extends Model
{
    use HasFactory;

    protected $fillable = [
        'total_votes',
        'election_type_id',
        'election_role_id',
        'student_id',
        'school_branch_id'
    ];

    public function electionType(): BelongsTo {
        return $this->belongsTo(ElectionType::class, 'election_type_id');
    }

    public function electionRole(): BelongsTo {
        return $this->belongsTo(ElectionRoles::class, 'election_role_id');
    }

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class, 'student_id');
    }
    public $table = '';
    public $incrementing = 'false';
    public $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });

    }
}
