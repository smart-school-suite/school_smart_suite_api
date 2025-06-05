<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PresetAudiences extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
         'name',
         'description',
         'target',
         'status'
    ];

    public $table = "preset_audiences";

    public $incrementing = false;
    public $keyType = 'string';

    public function announcementTargetPresetGroup(): HasMany {
        return $this->hasMany(AnnouncementTargetPresetGroup::class, 'preset_group_id');
    }
}
