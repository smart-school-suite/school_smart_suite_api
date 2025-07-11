<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
class PasswordReset extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'email',
        'expires_at',
        'otp'
    ];

    public $table = 'password_resets';
    public $incrementing = 'false';
    public $keyType = 'string';

}
