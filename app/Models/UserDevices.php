<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
class UserDevices extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'devicesable_id',
        'devicesable_type',
        'device_token',
        'platform',
        'school_branch_id'
    ];

    public $incrementing = 'false';
    public $table = 'user_devices';
    public $keyType = 'string';

    public function deviceable()
    {
        return $this->morphTo();
    }

}
