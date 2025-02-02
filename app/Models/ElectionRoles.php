<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectionRoles extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "description",
        "election_id",
        "school_branch_id"
    ];

    public $keyType = "string";
    public $incrementing = false;

    public $table = "election_roles";

    public function electionApplication(): HasMany {
         return $this->hasMany(ElectionApplication::class);
    }

    public function schoolbranches(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function election(): BelongsTo {
         return $this->belongsTo(Elections::class, "election_id");
    }

    public function electionResults(): HasMany {
         return $this->hasMany(ElectionResults::class);
    }

    public function electionVotes(): HasMany {
         return $this->hasMany(ElectionVotes::class);
    }

}
