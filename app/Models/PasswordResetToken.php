<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
class PasswordResetToken extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'token',
        'actorable_id',
        'actorable_type',
        'expires_at'
    ];

    public $keyType = 'string';
    public $table = 'password_reset_tokens';
    public $incrementing = false;

    public function isExpired()
    {
        return now()->greaterThan($this->expires_at);
    }

    public function actorable()
    {
        return $this->morphTo();
    }

}
