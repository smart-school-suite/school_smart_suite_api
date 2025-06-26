<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'election_title',
        'status',
        'description',
        'school_branch_id'
    ];

   public function schoolBranch() : BelongsTo {
       return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
   }

   public function election(): HasMany {
       return $this->hasMany(Elections::class);
   }

   public function pastElectionWinners(): HasMany {
      return $this->hasMany(PastElectionWinners::class);
   }
   public function electionRoles(): HasMany {
      return $this->hasMany(ElectionRoles::class );
   }
    public $incrementing = 'false';
    public $table = 'election_type';
    public $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });

    }
}
