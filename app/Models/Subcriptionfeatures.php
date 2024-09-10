<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subcriptionfeatures extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description'
    ];

    public $incrementing = 'false';
    public $table = 'subcription_features';
    public $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
       
         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });
      
    }

    public function subcription(): BelongsTo {
        return $this->belongsTo(Subcription::class, 'description_id');
    }
}
