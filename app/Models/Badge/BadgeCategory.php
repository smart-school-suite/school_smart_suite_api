<?php

namespace App\Models\Badge;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BadgeCategory extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'name',
        'key',
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
