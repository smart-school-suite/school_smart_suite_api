<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;

class SchoolHallType extends Model
{
    use GeneratesUuid;
    protected $fillable = [
         'hall_type_id',
         "hall_id",
         "school_branch_id"
    ];

    public $incrementing = false;
    public $keyType = 'string';
    public $table = "school_hall_types";

}
