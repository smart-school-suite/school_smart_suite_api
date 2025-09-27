<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnnouncementTag extends Model
{
    use HasFactory, GeneratesUuid;
    protected $fillable = [
       'name',
    ];

    public $incrementing = false;
    public $keyType = 'string';
    public $table = 'tags';

}
