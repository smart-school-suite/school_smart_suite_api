<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Traits\GeneratesUuid;
class HOD extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'school_branch_id',
        'department_id',
        'hodable_id',
        'hodable_type'
    ];

    public $incrementing = 'false';
    public $table = 'hod';
    public $keyType = 'string';

    public function hodable()
    {
        return $this->morphTo();
    }
    public function department() : BelongsTo {
        return $this->belongsTo(Department::class, 'department_id');
    }

    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 25);
         });

    }
}
