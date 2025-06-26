<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectionRoles extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "description",
        "election_type_id",
        'status',
        "school_branch_id"
    ];

    public $keyType = "string";
    public $incrementing = false;

    public $table = "election_roles";

    public function pastElectionWinners(): HasMany {
        return $this->hasMany(PastElectionWinners::class);
    }
    public function currentElectionWinners(): BelongsTo {
         return $this->belongsTo(CurrentElectionWinners::class);
    }
    public function electionApplication(): HasMany {
         return $this->hasMany(ElectionApplication::class);
    }

    public function electionCandidates(): HasMany {
            return $this->hasMany(ElectionCandidates::class);
    }
    public function schoolbranches(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function electionType(): BelongsTo {
         return $this->belongsTo(ElectionType::class, "election_type_id");
    }

    public function electionResults(): HasMany {
         return $this->hasMany(ElectionResults::class);
    }

    public function electionVotes(): HasMany {
         return $this->hasMany(ElectionVotes::class);
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
