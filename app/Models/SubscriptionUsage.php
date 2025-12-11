<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\GeneratesUuid;

class SubscriptionUsage extends Model
{
    use GeneratesUuid;

    protected $fillable = [
        'limit',
        'current_usage',
        'subscription_id',
        'feature_plan_id',
        'school_branch_id',
        'limit_type'
    ];

    public $incrementing = false;
    public $table = 'subscription_usage';
    public $keyType = 'string';

    protected function limitType(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value ? strtolower($value) : null,
            set: fn(?string $value) => $value ? strtolower($value) : null,
        );
    }

    protected function limit(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value, array $attributes) => $this->fromJsonStorage($value, $attributes['limit_type'] ?? null),
            set: fn(mixed $value) => $this->toJsonStorage($value),
        );
    }

    protected function currentUsage(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value === null ? 0 : (int) json_decode($value, true),
            set: fn(int $value) => json_encode((int) $value, JSON_UNESCAPED_UNICODE),
        );
    }

    private function fromJsonStorage(?string $json, ?string $type): mixed
    {
        if ($json === null) {
            return null;
        }

        $value = json_decode($json, true);

        return match (strtolower($type ?? '')) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'decimal' => (float) $value,
            default   => $value,
        };
    }

    private function toJsonStorage(mixed $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function schoolSubscription(): BelongsTo
    {
        return $this->belongsTo(SchoolSubscription::class, 'subscription_id');
    }

    public function featurePlan(): BelongsTo
    {
        return $this->belongsTo(PlanFeature::class, 'feature_plan_id');
    }

    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }
}
