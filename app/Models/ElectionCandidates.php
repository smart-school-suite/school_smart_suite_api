<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectionCandidates extends Model
{
    use HasFactory;

    protected $fillable = [
        "isActive",
        "application_id",
        "school_branch_id",
        "student_id"
    ];

    public $incrementing = "false";
    public $keyType = "string";

    public $table = "election_candidates";

    public function electionResults(): HasMany {
         return $this->hasMany(ElectionResults::class);
    }

    public function student(): BelongsTo {
         return $this->belongsTo(Student::class, 'student_id');
    }

    public function electionVotes(): HasMany {
         return $this->hasMany(ElectionVotes::class);
    }

    public function electionApplication(): BelongsTo {
         return $this->belongsTo(ElectionApplication::class, "application_id");
    }

}
