<?php

namespace App\Models\Badge;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use MongoDB\Laravel\Relations\HasMany;

class BadgeCategory extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'name',
        'status'
    ];

    public $incrementing = false;

    public $table = 'badge_categories';
    public $keyType = 'string';

    public function badgeType(): HasMany
    {
        return $this->hasMany(BadgeType::class);
    }
}
