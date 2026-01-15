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

    public function types()
    {
        return $this->belongsToMany(HallType::class, 'school_hall_types')
            ->using(SchoolHallType::class)
            ->withPivot(['id', 'school_branch_id'])
            ->withTimestamps();
    }
}
