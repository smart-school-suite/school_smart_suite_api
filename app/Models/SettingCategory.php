<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SettingCategory extends Model
{
    use GeneratesUuid;
    protected $fillable = [
       'name',
       'decription',
       'key'
    ];

    public $table = 'setting_categories';
    public $incrementing = 'false';
    public $keyType = 'string';

    public function settingDefination(): HasMany {
         return $this->hasMany(SettingDefination::class);
    }

}
