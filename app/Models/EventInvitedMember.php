<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EventInvitedMember extends Model
{
    use HasFactory;
    protected $fillable = [
        'actorable_type',
        'actorable_id',
        'event_id',
        'school_branch_id',
        'is_liked'
    ];

    public $incrementing = 'false';
    public $table = 'ev_inv_members';
    public $keyType = 'string';

    public function actorable(): MorphTo {
        return $this->morphTo();
    }

    public function schoolEvent(): BelongsTo{
        return $this->belongsTo(SchoolEvent::class, 'event_id');
    }

    public function schoolBranch(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

}
