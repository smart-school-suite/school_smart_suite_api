<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AnnouncementAudience extends Model
{
    protected $fillable = [
        'school_branch_id',
        'audienceable_id',
        'audienceable_type',
        'announcement_id'
    ];

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class, 'announcement_id');
    }

    public function audienceable(): MorphTo
    {
        return $this->morphTo();
    }
}
