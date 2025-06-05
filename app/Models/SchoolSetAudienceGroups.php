<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Schoolbranches;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolSetAudienceGroups extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'id',
        'name',
        'description',
        'status',
        'school_branch_id'
    ];

    public $table = 'school_set_audience_groups';

    public $incrementing = false;
    public $keyType = 'string';
    public function schoolBranch(): BelongsTo {
        return $this->belongsTo(SchoolBranches::class, 'school_branch_id');
    }

    public function audiences() :HasMany {
        return $this->hasMany(Audiences::class, 'school_set_audience_group_id');
    }

    public function announcementTargetGroup(): HasMany {
        return $this->hasMany(AnnouncementTargetGroup::class, 'school_set_audience_group_id');
    }


}
