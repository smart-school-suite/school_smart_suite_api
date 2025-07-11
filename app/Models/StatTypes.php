<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class StatTypes extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'name',
        'stat_category_id',
        'description',
        'program_name',
        'status',
    ];
    public $incrementing = 'false';
    public $keyType = 'string';
    protected $table = 'stat_types';

}
