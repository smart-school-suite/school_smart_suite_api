<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
class OTP extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = ['token_header', 'otp', 'expires_at', 'used', 'actorable_id',
        'actorable_type'];
    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'otp';

    public function actorable()
    {
        return $this->morphTo();
    }
    public function isExpired()
    {
        return now()->greaterThan($this->expires_at);
    }
    public function scopeValid($query)
    {
        return $query->where('used', false)
                     ->where('expires_at', '>', now());
    }
}
