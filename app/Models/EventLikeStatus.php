<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\GeneratesUuid;
class EventLikeStatus extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'event_id',
        'school_branch_id',
        'likeable_id',
        'likeable_type',
        'status'
    ];

    public $incrementing = 'false';
    public $keyType  = 'string';
    public $table = 'event_like_statuses';

    protected $casts = [
         'status' => 'boolean',
    ];

    public function schoolEvent(): BelongsTo {
         return $this->belongsTo(SchoolEvent::class, 'event_id');
    }

    public function likable(): MorphTo {
         return $this->morphTo();
    }
}
