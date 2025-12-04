<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\GeneratesUuid;

class EventAudience extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'school_branch_id',
        'audienceable_id',
        'audienceable_type',
        'event_id'
    ];

    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'event_audiences';

    public function schoolEvent(): BelongsTo
    {
        return $this->belongsTo(SchoolEvent::class, 'event_id');
    }

    public function audienceable(): MorphTo
    {
        return $this->morphTo();
    }
}
