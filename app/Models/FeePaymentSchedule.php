<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;

class FeePaymentSchedule extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'title',
        'num_installments',
        'amount',
        'due_date',
        'type',
        'school_branch_id',
        'specialty_id',
        'level_id'
    ];

    protected $cast = [
        'num_installments' => 'integer',
        'amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'tuition_fees';

    public function specialty()
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function level()
    {
        return $this->belongsTo(Educationlevels::class, 'level_id');
    }
}
