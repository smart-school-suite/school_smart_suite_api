<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class StatTypes extends Model
{
    use HasFactory;

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
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
        });
    }

}
