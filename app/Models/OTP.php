<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
    use HasFactory;

    protected $fillable = ['token_header', 'otp', 'expires_at', 'used', 'user_id'];
    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'otp';

    public function isExpired()
    {
        return now()->greaterThan($this->expires_at);
    }
    public function scopeValid($query)
    {
        return $query->where('used', false)
                     ->where('expires_at', '>', now());
    }
    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 25);
         });

    }
}
