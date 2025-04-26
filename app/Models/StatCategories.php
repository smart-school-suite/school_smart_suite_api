<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class StatCategories extends Model
{
    use HasFactory;

    protected $fillable = [
       'name',
       'description',
       'program_name',
       'status',
    ];

    public $incrementing = 'false';
    public $keyType = 'string';
    protected $table = 'stat_categories';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
        });
    }

}
