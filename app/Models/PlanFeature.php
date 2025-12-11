<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanFeature extends Model
{
    use GeneratesUuid;

    protected $fillable = [
        'feature_id',
        'plan_id',
        'country_id',
        'value',
        'type',
        'default'
    ];

    public $table = 'plan_features';
    public $incrementing = false;
    public $keyType = 'string';

    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value, array $attributes) => $this->castByType($value, $attributes['type'] ?? null),
            set: fn(mixed $value) => $this->prepareForStorage($value, $this->type),
        );
    }

    protected function default(): Attribute
    {
        return Attribute::make(
            get: fn(?string $default, array $attributes) => $this->castByType($default, $attributes['type'] ?? null),
            set: fn(mixed $default) => $this->prepareForStorage($default, $this->type),
        );
    }

    private function castByType(?string $jsonValue, ?string $type): mixed
    {
        if ($jsonValue === null) {
            return null;
        }

        $decoded = json_decode($jsonValue, true);

        if ($type === null) {
            return $decoded;
        }

        return match (strtolower($type)) {
            'boolean' => (bool) $decoded,
            'integer' => (int) $decoded,
            'decimal' => (float) $decoded,
            default   => $decoded,
        };
    }

    private function prepareForStorage(mixed $value, ?string $type): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function subscriptionUsage(): HasMany
    {
        return $this->hasMany(SchoolSubscription::class);
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class, 'feature_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
