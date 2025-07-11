<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\GeneratesUuid;
class HOS extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'school_branch_id',
        'specialty_id',
        'hosable_id',
        'hosable_type'
    ];

    public $incrementing = 'false';
    public $table = 'hos';
    public $keyType = 'string';

    public function hosable()
    {
        return $this->morphTo();
    }

    public function specialty() : BelongsTo {
         return $this->belongsTo(Specialty::class, 'specialty_id');
    }

}
