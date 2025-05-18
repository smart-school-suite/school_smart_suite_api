<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectionResults extends Model
{
    use HasFactory;

    protected $fillable = [
        'vote_count',
        'election_id',
        'position_id',
        'candidate_id',
        'school_branch_id',
        "election_status",
    ];

    public $table = "elections_results";

    public $incrementing = "false";

    public $keyType = "string";

    public function ElectionRoles(): BelongsTo {
         return $this->belongsTo(ElectionRoles::class, 'position_id');
    }

    public function Elections(): BelongsTo {
         return $this->belongsTo(Elections::class, 'election_id');
    }

    public function schoolbranches(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function electionCandidate(): BelongsTo {
         return $this->belongsTo(ElectionCandidates::class, "candidate_id");
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
