<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;


class AdditionalFeesCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'status',
        'school_branch_id',
     ];

     public $incrementing = 'false';
     public $table = 'additional_fee_category';
     public $keyType = 'string';

     public function additionalFees(): HasMany {
        return $this->hasMany(AdditionalFees::class);
     }
     protected static function boot()
     {
         parent::boot();

          static::creating(function ($user){
             $uuid = str_replace('-', '', Str::uuid()->toString());
             $user->id = substr($uuid, 0, 10);
          });

     }
}
