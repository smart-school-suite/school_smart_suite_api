<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
class AdditionalFees extends Model
{
    use HasFactory;

    protected $fillable = [
       'reason',
       'amount',
       'status',
       'school_branch_id',
       'specialty_id',
       'level_id',
       'student_id',
       'additionalfee_category_id'
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
    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });

    }
}
