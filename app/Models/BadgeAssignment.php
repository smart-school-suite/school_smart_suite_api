<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
class BadgeAssignment extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'assignable_id',
        'assignable_type',
        'batch_id',
    ];

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'user_batches';

     public function badge()
    {
        return $this->belongsTo(Badge::class);
    }

     public function assignable()
    {
        return $this->morphTo();
    }
}
