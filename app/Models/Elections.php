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
        "title",
        "election_start_date",
        "election_end_date",
        "starting_time",
        "ending_time",
        'school_year_start',
        'school_year_end',
        "is_results_published",
        "school_branch_id",
        'description'
    ];

    public $table = "elections";

    public $incrementing = "false";

    public $keyType = "string";

    public function schoolbranches(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function electionApplication(): HasMany {
         return $this->hasMany(ElectionApplication::class,'election_id');
    }

    public function electionRole(): hasMany{
         return $this->hasMany(ElectionRoles::class);
    }

    public function electionResults(): HasMany {
         return $this->hasMany(ElectionResults::class);
    }

    public function electionVotes(): HasMany {
         return $this->hasMany(ElectionVotes::class);
    }

}
