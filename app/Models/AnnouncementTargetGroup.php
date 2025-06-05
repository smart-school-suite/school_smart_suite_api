<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementTargetGroup extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
      'school_set_audience_group_id',
      'announcement_id',
      'school_branch_id'
    ];

    public $incrementing = false;
    public $keyType = 'string';
    public $table = 'target_groups';

    public function schoolSetAudienceGroup(): BelongsTo {
        return $this->belongsTo(SchoolSetAudienceGroups::class, 'school_set_audience_group_id');
    }

    public function announcement(): BelongsTo {
        return $this->belongsTo(Announcement::class, 'announcement_id');
    }

    public function schoolBranch(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }


}
