<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\GeneratesUuid;
class AnnouncementLabel extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
       'name',
       'color',
       'status',
       'description'
    ];

    protected $casts = [
        'color' => 'json',
        'status' => 'boolean'
    ];

    public $incrementing = false;
    public $table = 'labels';
    public $keyType = 'string';

    public function announcement() : HasMany {
        return $this->hasMany(Announcement::class, 'label_id');
    }

}
