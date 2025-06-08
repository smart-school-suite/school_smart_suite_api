<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\GeneratesUuid;
class EventInvitedPresetGroup extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'preset_group_id',
        'event_id',
        'school_branch_id'
    ];

    public $incrementing = 'false';
    public $table = 'ev_inv_preset_groups';
    public $keyType = 'string';

    public function schoolEvent(): BelongsTo {
        return $this->belongsTo(SchoolEvent::class, 'event_id');
    }

    public function presetGroup(): BelongsTo {
        return $this->belongsTo(PresetAudiences::class, 'preset_group_id');
    }

    public function schoolBranch(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }
}
