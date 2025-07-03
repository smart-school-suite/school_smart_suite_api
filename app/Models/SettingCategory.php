<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\GeneratesUuid;
class SettingCategory extends Model
{
    use GeneratesUuid;
    protected $fillable = [
       'status',
       'title'
    ];

    public $table = 'setting_categories';
    public $incrementing = 'false';
    public $keyType = 'string';

    public function appSetting(): HasMany {
        return $this->hasMany(AppSetting::class);
    }

}
