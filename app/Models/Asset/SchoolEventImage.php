<?php

namespace App\Models\Asset;

use App\Models\Schoolbranches;
use App\Models\SchoolEvent;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolEventImage extends Model
{
    use HasUuids, HasFactory;
    protected $fillable = [
        'object_key',
        'mime_type',
        'size_bytes',
        'height',
        'width',
        'is_current',
        'created_at',
        'replaced_at',
        'school_branch_id',
        'event_id'
    ];

    protected $casts = [
        'is_current' => "boolean",
        'replaced_at' => "datetime",
        'size_bytes' => "integer",
        'height' => 'integer',
        'width' => 'integer',
    ];
    const UPDATED_AT = null;
    public $incrementing = false;
    public $table = "school_event_pics";
    public $keyType = 'string';

    public function schoolEvent(): BelongsTo
    {
        return $this->belongsTo(SchoolEvent::class, 'event_id');
    }

    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }
}
