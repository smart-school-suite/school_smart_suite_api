<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventTag extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'name',
        'school_branch_id'
    ];

    public $incrementing = 'false';
    public $table = 'event_tags';

    public function schoolBranch(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function schoolEvent(): HasMany {
        return $this->hasMany(SchoolEvent::class, 'tag_id');
    }


}
