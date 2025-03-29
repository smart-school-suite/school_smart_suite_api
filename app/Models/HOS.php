<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
class HOS extends Model
{
    use HasFactory;

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

    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 25);
         });

    }
}
