<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolFeeSchedule extends Model
{
    use HasFactory;

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

    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });

    }
}
