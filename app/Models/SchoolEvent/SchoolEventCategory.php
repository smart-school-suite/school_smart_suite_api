<?php

namespace App\Models\SchoolEvent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolEventCategory extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'status',
        'description'
    ];

    public $incrementing = false;
    public $keyType = 'string';
    public $table = "event_categories";

    public $casts = [
        'status' => 'boolean'
    ];

    public function schoolEvent(): HasMany
    {
        return $this->hasMany(SchoolEvent::class, 'event_category_id');
    }
}
