<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;

class HallType extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        "name",
        "key",
        "description",
        "status",
        "background_color",
        "text_color"
    ];

    public $incrementing = false;
    public $table = "hall_types";
    public $keyType = 'string';

    public function halls()
    {
        return $this->belongsToMany(
            Hall::class,
            'school_hall_types',
            'hall_type_id',
            'hall_id'
        )->withTimestamps();
    }
}
