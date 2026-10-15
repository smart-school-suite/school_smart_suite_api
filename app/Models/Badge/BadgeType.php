<?php

namespace App\Models\Badge;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BadgeType extends Model
{
    use GeneratesUuid;
    protected $fillable = [
         'name',
         'key',
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
