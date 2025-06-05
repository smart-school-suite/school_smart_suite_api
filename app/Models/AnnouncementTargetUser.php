<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AnnouncementTargetUser extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
      'actorable_type',
      'actorable_id',
      'announcement_id',
      'school_branch_id'
    ];

    public $incrementing = false;
    public $keyType = 'string';
    public $table = 'target_users';

    public function announcement(): BelongsTo {
        return $this->belongsTo(Announcement::class, 'announcement_id');
    }

    public function actorable(): MorphTo {
        return $this->morphTo();
    }

    public function schoolBranch(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

}
