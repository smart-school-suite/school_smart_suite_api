<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
class ActivationCodeUsage extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'school_branch_id',
        'activation_code_id',
        'country_id',
        'activated_at',
        'expires_at',
        'meta',
        'actorable_id',
        'actorable_type'
    ];

    public $incrementing = 'false';
    public $table = 'activation_code_usages';
    public $keyType = 'string';

    protected $casts = [
         "activated_at" => "datetime",
         "expires_at" => "datetime"
    ];
    public function actorable(): MorphTo
    {
        return $this->morphTo();
    }

    public function country(): BelongsTo {
         return $this->belongsTo(Country::class, 'country_id');
    }

    public function activationCode(): BelongsTo {
         return $this->belongsTo(ActivationCode::class, 'activation_code_id');
    }
}
