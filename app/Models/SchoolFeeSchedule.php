<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolFeeSchedule extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'specialty_id',
        'title',
        'deadline_date',
        'amount',
        'school_branch_id'
    ];

    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'schoolfee_schedule';

    public function specialty(): BelongsTo {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

}
