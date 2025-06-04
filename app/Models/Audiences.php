<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Audiences extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'id',
        'audienceable_id',
        'audienceable_type',
        'school_set_audience_group_id'
    ];

    public $table = 'audiences';

    public $incrementing = false;
    public $keyType = 'string';

    public function schoolSetAudienceGroup() : BelongsTo {
         return $this->belongsTo(SchoolSetAudienceGroups::class, 'school_set_audience_group_id');
    }

    public function audienceable(): MorphTo {
        return $this->morphTo();
    }

}
