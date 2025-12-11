<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\GeneratesUuid;
class PaymentMethodCategory extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'name',
        'status',
        'description'
    ];

    public $incrementing = false;
    public $table = "payment_method_category";
    public $keyType = "string";

    public function paymentMethod(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }
}
