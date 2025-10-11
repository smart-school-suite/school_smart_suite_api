<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\GeneratesUuid;
class VoterStatus extends Model
{
    use HasFactory, GeneratesUuid;
    protected $fillable = [
        'votable_id',
        'votable_type',
        'school_branch_id',
        'election_id',
        'status',
        'position_id',
        'candidate_id'
    ];

    public $keyType = 'string';
    public $table = 'voter_status';

    public $incrementing = 'false';


    public function votableStatus(): MorphTo {
        return $this->morphTo();
    }
}
