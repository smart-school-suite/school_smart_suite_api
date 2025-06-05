<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementTargetPresetGroup extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
       'preset_group_id',
       'announcement_id',
       'school_branch_id'
    ];

    public $incrementing = false;
    public $table = 'target_preset_groups';
    public $keyType = 'string';

    public function presetGroup(): BelongsTo {
        return $this->belongsTo(PresetAudiences::class, 'preset_group_id');
    }

    public function announcement(): BelongsTo {
        return $this->belongsTo(Announcement::class, 'announcement_id');
    }

    public function schoolBranch(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }



}
