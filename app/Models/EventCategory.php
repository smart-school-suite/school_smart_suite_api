<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventCategory extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
       'name',
       'status',
       'description'
    ];

    public $incrementing = 'false';
    public $table = 'event_categories';
    public $keyType = 'string';

    public function schoolEvent(): HasMany {
        return $this->hasMany(SchoolEvent::class, 'event_category_id');
    }

}
