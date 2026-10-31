<?php

namespace App\Models\SchoolEvent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolEventTag extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'name',
        'status'
    ];

    public $incrementing = false;
    public $keyType = 'string';
    public $table = 'event_tags';


}
