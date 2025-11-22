<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class AdditionalFees extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
       'reason',
       'amount',
       'status',
       'school_branch_id',
       'specialty_id',
       'level_id',
       'student_id',
       'additionalfee_category_id'
    ];

    protected $cast = [
         'amount' => 'decimal:2'
    ];
    public $incrementint = 'false';
    public $keyType = 'string';

    public $table = 'additional_fees';

    public function additionalFeeTranctions() : HasMany {
         return $this->hasMany(AdditionalFeeTransactions::class, 'fee_id');
    }
    public function feeCategory() : BelongsTo {
        return $this->belongsTo(AdditionalFeesCategory::class, 'additionalfee_category_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function level()
    {
        return $this->belongsTo(Educationlevels::class, 'level_id');
    }
}
