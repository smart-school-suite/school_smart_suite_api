<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\GeneratesUuid;
class Feepayment extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'student_id',
        'fee_name',
        'school_branch_id',
        'amount'
    ];

    public $keyType = 'string';
    public $table = 'fee_payment_transactions';
    public $incrementing = 'false';

    public function school(): BelongsTo {
        return $this->belongsTo(School::class);
    }

    public function schoolbranches(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class);
    }

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class);
    }

}
