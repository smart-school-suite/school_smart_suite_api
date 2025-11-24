<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\GeneratesUuid;

class AdditionalFeesCategory extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'title',
        'status',
        'description',
        'school_branch_id',
     ];

     public $incrementing = 'false';
     public $table = 'additional_fee_categories';
     public $keyType = 'string';

     public function additionalFees(): HasMany {
        return $this->hasMany(AdditionalFees::class);
     }
}
