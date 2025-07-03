<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolBranchAppSetting extends Model
{
    use GeneratesUuid;
    protected $fillable = [
      'app_settings_id',
      'school_branch_id',
      'boolean_value',
      'decimal_value',
      'integer_value'
    ];

    public $incrementing = 'false';
    public $table = 'school_branch_app_settings';
    public $keyType = 'string';

    public function appSetting(): BelongsTo {
        return $this->belongsTo(AppSetting::class, 'app_settings_id');
    }

}
