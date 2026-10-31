<?php

namespace App\Models\SchoolEvent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SchoolEventLike extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
      'likeable_type',
      'likeable_id',
      'school_branch_id',
      'event_id'
    ];

    public $incrementing = false;
    public $keyType = 'string';
    public $table = "event_likes";

    public function  schoolEvent(): BelongsTo
    {
        return $this->belongsTo(SchoolEvent::class, 'event_id');
    }

    public function likeable(): MorphTo
    {
        return $this->morphTo(
            name: 'likeable',
            type: 'likeable_type',
            id: 'likeable_id'
        );
    }
}
