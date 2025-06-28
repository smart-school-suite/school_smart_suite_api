<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Installment extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'name',
        'program_name',
        'count',
        'code',
        'status'
    ];

    public $table = 'installments';
    public $incrementing = 'false';
    public $keyType = 'string';

    public function feeScheduleSlot(): HasMany {
        return $this->hasMany(FeeScheduleSlot::class);
    }

}
