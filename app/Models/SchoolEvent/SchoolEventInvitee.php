<?php

namespace App\Models\SchoolEvent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SchoolEventInvitee extends Model
{
    use HasUuids, HasFactory;
    protected $fillable = [
        'actorable_id',
        'actorable_type',
        'school_branch_id',
        'event_id'
    ];

    public $table = "event_invitee";
    public $incrementing = false;
    public $keyType = 'string';

    public function schoolEvent(): BelongsTo
    {
        return $this->belongsTo(SchoolEvent::class, 'event_id');
    }

    public function inviteeable(): MorphTo
    {
        return $this->morphTo(
            name: 'actorable',
            type: 'actorable_type',
            id: 'actorable_id'
        );
    }
}
