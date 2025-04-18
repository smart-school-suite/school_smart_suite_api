<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class UserDevices extends Model
{
    use HasFactory;

    protected $fillable = [
        'devicesable_id',
        'devicesable_type',
        'device_token',
        'platform',
        'app_version',
        'last_used_at'
    ];

    public $incrementing = 'false';
    public $table = 'user_devices';
    public $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
        });
    }
}
