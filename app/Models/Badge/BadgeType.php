<?php

namespace App\Models\Badge;

use Illuminate\Database\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class BadgeType extends Model
{
    protected $fillable = [
         'name',
         'badge_category_id',
         'description',
         'color',
         'icon_code'
    ];

    public $incrementing = false;
    public $table = 'badge_types';
    public $keyType = 'string';

    public function badgeCategory(): BelongsTo {
         return $this->belongsTo(BadgeCategory::class, 'badge_category_id');
    }

}
