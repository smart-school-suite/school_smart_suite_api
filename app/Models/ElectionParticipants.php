<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\GeneratesUuid;
class ElectionParticipants extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'specialty_id',
        'level_id',
        'election_id',
        'school_branch_id'
    ];

    public function Specialty() : BelongsTo {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function level(): BelongsTo {
        return $this->belongsTo(Educationlevels::class, 'level_id');
    }
    public $table = 'election_participants';
    public $incrementing = 'false';
    public $keyType = 'string';

}
