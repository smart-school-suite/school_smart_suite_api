<?php

namespace App\Models\SchoolEvent;

use App\Models\SchoolEvent\SchoolEvent;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SchoolEventAuthor extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'actorable_id',
        'actorable_type',
        'school_branch_id',
        'event_id',
    ];

    public $incrementing = false;
    public $table = 'event_authors';
    public $keyType = 'string';

    public function  schoolEvent(): BelongsTo
    {
        return $this->belongsTo(SchoolEvent::class, 'event_id');
    }

    public function autorable(): MorphTo
    {
        return $this->morphTo(
            name: 'autorable',
            type: 'actorable_type',
            id: 'actorable_id'
        );
    }
}
