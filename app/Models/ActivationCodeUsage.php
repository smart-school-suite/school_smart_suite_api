<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivationCodeUsage extends Model
{
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

    public function actorable(): MorphTo
    {
        return $this->morphTo();
    }

    public function country(): BelongsTo {
         return $this->belongsTo(Country::class, 'country_id');
    }
}
