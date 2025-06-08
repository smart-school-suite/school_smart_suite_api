<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EventAuthor extends Model
{
    use HasFactory;

    protected $fillable = [
        'authorable_id',
        'authorable_type',
        'event_id',
        'school_branch_id'
    ];

    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'event_author';

    public function actorable(): MorphTo {
        return $this->morphTo();
    }

    public function schoolEvent(): BelongsTo {
        return $this->belongsTo(SchoolEvent::class, 'event_id');
    }
}
