<?php

namespace App\Models\Badge;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use MongoDB\Laravel\Relations\MorphTo;

class UserBadge extends Model
{
    use GeneratesUuid;
    protected $fillable  = [
        'school_branch_id',
        'actorable_id',
        'actorable_type'
    ];

    public $table = 'user_badges';
    public $incrementing = false;
    public $keyType = 'string';


    public function actorable(): MorphTo
    {
        return $this->morphTo();
    }
}
