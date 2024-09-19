<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subcription extends Model
{
    use HasFactory;

    protected $fillable = [
            'name' ,
            'max_number_students' ,
            'monthly_price',
            'yearly_price',
            'description_id' 
    ];

    public $incrementing = 'false';
    public $table = 'subcriptions';
    public $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
       
         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });
      
    }

    public function subfeatures(): BelongsTo {
        return $this->belongsTo(Subcriptionfeatures::class, 'description_id');
    }
}
