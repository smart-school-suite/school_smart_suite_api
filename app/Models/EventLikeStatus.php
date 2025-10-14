<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EventLikeStatus extends Model
{
    protected $fillable = [
        'event_id',
        'school_branch_id',
        'likable_id',
        'likable_type',
        'status'
    ];

    public $incrementing = 'false';
    public $keyType  = 'string';
    public $table = 'event_like_statuses';

    public function schoolEvent(): BelongsTo {
         return $this->belongsTo(SchoolEvent::class, 'event_id');
    }

    public function likable(): MorphTo {
         return $this->morphTo();
    }
}
