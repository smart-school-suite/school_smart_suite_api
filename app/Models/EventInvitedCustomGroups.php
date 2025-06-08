<?php

namespace App\Models;

use App\Models\SchoolEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventInvitedCustomGroups extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'school_set_audience_group_id',
        'event_id',
        'school_branch_id'
    ];

    public $table = 'ev_inv_custom_groups';
    public $incrementing = false;
    public $keyType = 'string';

    public function schoolSetAudienceGroup(): BelongsTo {
        return $this->belongsTo(SchoolSetAudienceGroups::class, 'school_set_audience_group_id');
    }

    public function schoolEvent(): BelongsTo {
        return $this->belongsTo(SchoolEvent::class, 'event_id');
    }

    public function schoolBranch(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }
}
