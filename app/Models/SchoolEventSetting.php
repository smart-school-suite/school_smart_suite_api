<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolEventSetting extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'value',
        'enabled',
        'school_branch_id',
        'event_setting_id'
    ];

    public $incrementing = 'false';
    public $table = 'school_event_settings';
    public $keyType = 'string';

    public function schoolBranch(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function eventSetting(): BelongsTo {
        return $this->belongsTo(EventSetting::class, 'event_setting_id');
    }

}
