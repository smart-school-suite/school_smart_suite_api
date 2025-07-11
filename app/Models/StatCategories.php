<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatCategories extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
       'name',
       'description',
       'program_name',
       'status',
    ];

    public $incrementing = 'false';
    public $keyType = 'string';
    protected $table = 'stat_categories';

}
