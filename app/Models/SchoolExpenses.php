<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolExpenses extends Model
{
    use HasFactory;

    protected $fillable = [
        'expenses_category_id',
        'school_branch_id',
        'date',
        'amount',
        'description'
    ];

    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'school_expenses';

    public function schoolbranch(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }
    
    public function schoolexpensescategory(): HasMany {
        return $this->hasMany(Schoolexpensescategory::class, 'expenses_category_id');
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
