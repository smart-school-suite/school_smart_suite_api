<?php

namespace App\Services\SubscriptionPlan;

use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\PlanRecommendation;
use App\Models\SchoolSubscription;
use App\Models\SubscriptionUsage;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Recommends a plan from how the school uses each feature.
 *
 * 1. Usage: for every feature of the current plan, how much is used against
 *    its limit (FeatureUsageService measures what is countable).
 * 2. Triggers: a feature triggers when it crosses its rule's condition. Rules
 *    live in plan_recs / plan_rec_conds (per source plan and feature, with a
 *    priority and copy in plan_rec_copies). Features without a rule trigger at
 *    DEFAULT_THRESHOLD.
 * 3. Need: a triggered feature needs a limit that puts today's usage at or
 *    below TARGET_UTILISATION, so the school has room to grow.
 * 4. Pick: among plans above the current one, the cheapest plan that covers
 *    every need. When none covers all, the plan covering the most
 *    priority-weighted need.
 *
 * With nothing triggered the school is told its plan fits (or to renew when
 * the period ends soon); a school without a subscription gets the most
 * popular plan.
 */
class PlanRecommendationService
{
    /** Share of a limit that triggers a recommendation when no rule says otherwise. */
    public const DEFAULT_THRESHOLD = 0.7;

    /** A recommended plan should leave today's usage at or below this share. */
    public const TARGET_UTILISATION = 0.8;

    /** Days before the end of the period when renewing becomes the advice. */
    public const RENEWAL_WINDOW_DAYS = 30;

    public function __construct(private FeatureUsageService $usageService)
    {
    }

    /**
     * Compact recommendation (kept for GET /school-subscription/recommended-plan).
     */
    public function recommendPlan(object $currentSchool): ?array
    {
        return $this->getPlanInsights($currentSchool)['recommendation'];
    }

    /**
     * Everything the subscription page needs: the current subscription, usage
     * per feature, every plan compared with the current one, and the
     * recommendation with its reasons.
     */
    public function getPlanInsights(object $currentSchool): array
    {
        $countryId = $currentSchool->school->country_id ?? $currentSchool->country_id ?? null;

        $subscription = SchoolSubscription::with(['plan.planFeature.feature', 'plan.country'])
            ->where('school_branch_id', $currentSchool->id)
            ->where('status', 'active')
            ->latest('start_date')
            ->first();

        $countryId ??= $subscription?->country_id;

        $plans = Plan::with(['planFeature.feature', 'country'])
            ->where('status', 'active')
            ->when($countryId, fn($q) => $q->where('country_id', $countryId))
            ->orderBy('price')
            ->get();

        $currentPlan = $subscription?->plan;
        $usage = $currentPlan ? $this->buildUsage($currentSchool->id, $subscription) : collect();
        $popularPlanId = $this->popularPlanId($plans);

        $recommendation = $this->buildRecommendation($subscription, $usage, $plans, $popularPlanId);

        return [
            'subscription'   => $this->subscriptionSummary($subscription),
            'usage'          => $usage->values()->all(),
            'usage_summary'  => $this->usageSummary($usage),
            'plans'          => $this->comparePlans($plans, $currentPlan, $recommendation, $popularPlanId),
            'recommendation' => $recommendation,
        ];
    }

    /**
     * One row per feature of the current plan.
     */
    protected function buildUsage(string $schoolBranchId, SchoolSubscription $subscription): Collection
    {
        $stored = SubscriptionUsage::where('school_branch_id', $schoolBranchId)
            ->where('subscription_id', $subscription->id)
            ->get()
            ->keyBy('feature_plan_id');

        return $subscription->plan->planFeature
            ->filter(fn(PlanFeature $planFeature) => $planFeature->feature && $planFeature->feature->status !== 'inactive')
            ->map(function (PlanFeature $planFeature) use ($stored, $schoolBranchId, $subscription) {
                $feature = $planFeature->feature;
                $type = strtolower($planFeature->type ?? $feature->limit_type ?? 'integer');
                $storedRow = $stored->get($planFeature->id);

                if ($type === 'boolean') {
                    $enabled = $this->isEnabled($planFeature->value);
                    return [
                        'feature_id'  => $feature->id,
                        'key'         => $feature->key,
                        'name'        => $feature->name,
                        'description' => $feature->description,
                        'limit_type'  => 'boolean',
                        'enabled'     => $enabled,
                        'limit'       => null,
                        'used'        => null,
                        'remaining'   => null,
                        'unlimited'   => false,
                        'ratio'       => null,
                        'percent'     => null,
                        'status'      => $enabled ? 'included' : 'locked',
                    ];
                }

                // the plan's limit is the source of truth; the stored copy can be stale
                $limit = $this->limitOf($planFeature->value);
                $used = $this->usageService->resolve(
                    $feature->key,
                    $schoolBranchId,
                    $subscription,
                    (int) ($storedRow?->current_usage ?? 0)
                );
                $unlimited = $limit === null;
                $ratio = $unlimited || $limit <= 0 ? ($used > 0 && !$unlimited ? 1.0 : 0.0) : $used / $limit;

                return [
                    'feature_id'  => $feature->id,
                    'key'         => $feature->key,
                    'name'        => $feature->name,
                    'description' => $feature->description,
                    'limit_type'  => $type,
                    'enabled'     => true,
                    'limit'       => $limit,
                    'used'        => $used,
                    'remaining'   => $unlimited ? null : max(0, $limit - $used),
                    'unlimited'   => $unlimited,
                    'ratio'       => round($ratio, 4),
                    'percent'     => (int) min(100, round($ratio * 100)),
                    'status'      => $this->usageStatus($ratio, $unlimited),
                ];
            })
            ->sortByDesc(fn($row) => $row['ratio'] ?? -1)
            ->values();
    }

    protected function buildRecommendation(
        ?SchoolSubscription $subscription,
        Collection $usage,
        Collection $plans,
        ?string $popularPlanId
    ): ?array {
        if ($plans->isEmpty()) {
            return null;
        }

        if (!$subscription || !$subscription->plan) {
            $plan = $plans->firstWhere('id', $popularPlanId) ?? $plans->first();
            return $this->recommendationPayload('start', $plan, [
                'headline'    => "Start with the {$plan->name}",
                'description' => 'Most schools like yours start here: it covers day-to-day administration with room to grow.',
                'cta_text'    => "Choose {$plan->name}",
                'confidence'  => 'medium',
            ]);
        }

        $currentPlan = $subscription->plan;
        $triggers = $this->triggers($currentPlan, $usage);

        if ($triggers->isEmpty()) {
            return $this->noPressureRecommendation($subscription, $usage);
        }

        $candidates = $plans
            ->filter(fn(Plan $plan) => (float) $plan->price > (float) $currentPlan->price && $plan->id !== $currentPlan->id)
            ->values();

        $top = $triggers->first();

        if ($candidates->isEmpty()) {
            return $this->recommendationPayload('top_plan', $currentPlan, [
                'headline'        => "You're outgrowing even the {$currentPlan->name}",
                'description'     => ($top['limit_type'] === 'boolean'
                    ? "{$top['name']} is not part of your plan."
                    : "{$top['name']} is at {$top['percent']}% of its limit.") . ' Talk to us about custom limits for your school.',
                'cta_text'        => 'Contact sales',
                'priority'        => $top['priority'],
                'trigger_feature' => ['key' => $top['key'], 'name' => $top['name']],
                'reasons'         => $this->reasons($triggers, null),
                'confidence'      => 'high',
            ]);
        }

        [$plan, $coverage] = $this->bestPlanFor($candidates, $triggers);
        $copy = $top['rule_target_plan_id'] === $plan->id ? $top['copy'] : null;
        $names = $triggers->pluck('name')->take(2)->implode(' and ');

        return $this->recommendationPayload('upgrade', $plan, [
            'headline'        => $copy['title'] ?? "Your school is outgrowing the {$currentPlan->name}",
            'description'     => $copy['description']
                ?? "{$names} " . ($triggers->count() > 1 ? 'are' : 'is') . " close to the limit. The {$plan->name} gives you room to grow without interruptions.",
            'cta_text'        => $copy['cta_text'] ?? "Upgrade to {$plan->name}",
            'priority'        => $top['priority'],
            'trigger_feature' => ['key' => $top['key'], 'name' => $top['name']],
            'reasons'         => $this->reasons($triggers, $plan),
            'score'           => (int) round($coverage * 100),
            'confidence'      => $coverage >= 1 && $top['ratio'] >= 0.9 ? 'high' : ($coverage >= 1 ? 'medium' : 'low'),
        ]);
    }

    /**
     * Features that cross their rule's condition, highest priority then
     * highest usage first.
     */
    protected function triggers(Plan $currentPlan, Collection $usage): Collection
    {
        $rules = PlanRecommendation::with(['planRecCondition.planRecCopy'])
            ->where('source_plan_id', $currentPlan->id)
            ->where('status', 'active')
            ->get()
            ->keyBy('feature_id');

        return $usage
            ->map(function (array $row) use ($rules) {
                $rule = $rules->get($row['feature_id']);
                $conditions = $rule?->planRecCondition ?? collect();

                $matched = $conditions->isEmpty()
                    ? ($this->defaultConditionMet($row) ? 'default' : null)
                    : $conditions->first(fn($condition) => $this->conditionMet($row, $condition->operator, $this->decode($condition->value)));

                if (!$matched) {
                    return null;
                }

                $copy = $matched === 'default' ? null : $matched->planRecCopy->first();

                return $row + [
                    'priority'            => (int) ($rule?->priority ?? 1),
                    'rule_target_plan_id' => $rule?->target_plan_id,
                    'needed'              => $this->neededLimit($row),
                    'copy'                => $copy ? [
                        'title'       => $copy->title,
                        'description' => $copy->description,
                        'cta_text'    => $copy->cta_text,
                    ] : null,
                ];
            })
            ->filter()
            ->sort(fn($a, $b) => [$b['priority'], $b['ratio'] ?? 1] <=> [$a['priority'], $a['ratio'] ?? 1])
            ->values();
    }

    protected function defaultConditionMet(array $row): bool
    {
        if ($row['limit_type'] === 'boolean') {
            return false;
        }

        return !$row['unlimited'] && ($row['ratio'] ?? 0) >= self::DEFAULT_THRESHOLD;
    }

    protected function conditionMet(array $row, ?string $operator, mixed $value): bool
    {
        $value = is_array($value) ? $value : ['threshold' => $value];

        if ($row['limit_type'] === 'boolean') {
            return $operator === 'enabled' && !empty($value['required']) && !$row['enabled'];
        }

        if ($row['unlimited']) {
            return false;
        }

        $threshold = $value['threshold'] ?? null;
        if (!is_numeric($threshold)) {
            return false;
        }
        $threshold = (float) $threshold;

        return match ($operator) {
            // thresholds are stored either as a share (0.7) or a percentage (70)
            'percentage' => ($row['ratio'] ?? 0) >= ($threshold > 1 ? $threshold / 100 : $threshold),
            '>='         => $row['used'] >= $threshold,
            '>'          => $row['used'] > $threshold,
            'remaining'  => $row['remaining'] !== null && $row['remaining'] <= $threshold,
            default      => false,
        };
    }

    /**
     * The limit a plan needs to bring today's usage down to TARGET_UTILISATION.
     */
    protected function neededLimit(array $row): ?int
    {
        if ($row['limit_type'] === 'boolean') {
            return null;
        }

        $byUtilisation = (int) ceil(max(1, $row['used']) / self::TARGET_UTILISATION);

        return max($byUtilisation, (int) $row['limit'] + 1);
    }

    /**
     * Cheapest plan covering every need; otherwise the best partial cover.
     *
     * @return array{0: Plan, 1: float}
     */
    protected function bestPlanFor(Collection $candidates, Collection $triggers): array
    {
        $scored = $candidates->map(function (Plan $plan) use ($triggers) {
            $features = $plan->planFeature->keyBy(fn($pf) => $pf->feature?->key);
            $weight = 0;
            $covered = 0;

            foreach ($triggers as $trigger) {
                $weight += $trigger['priority'];
                $planFeature = $features->get($trigger['key']);
                if (!$planFeature) {
                    continue;
                }

                if ($trigger['limit_type'] === 'boolean') {
                    $covered += $this->isEnabled($planFeature->value) ? $trigger['priority'] : 0;
                    continue;
                }

                $limit = $this->limitOf($planFeature->value);
                $share = $limit === null ? 1 : min(1, $limit / max(1, $trigger['needed']));
                $covered += $trigger['priority'] * $share;
            }

            return ['plan' => $plan, 'coverage' => $weight > 0 ? $covered / $weight : 0];
        });

        $full = $scored->first(fn($row) => $row['coverage'] >= 0.9999);
        $best = $full ?? $scored->sortByDesc('coverage')->first();

        return [$best['plan'], min(1, $best['coverage'])];
    }

    protected function noPressureRecommendation(SchoolSubscription $subscription, Collection $usage): array
    {
        $plan = $subscription->plan;
        $daysLeft = $this->daysLeft($subscription);
        $closest = $usage->first(fn($row) => $row['ratio'] !== null && !$row['unlimited']);

        if ($daysLeft !== null && $daysLeft <= self::RENEWAL_WINDOW_DAYS) {
            return $this->recommendationPayload('renew', $plan, [
                'headline'    => $daysLeft > 0 ? "Your plan ends in {$daysLeft} days" : 'Your plan has ended',
                'description' => "The {$plan->name} still fits how your school works. Renew now to keep every feature running without a gap.",
                'cta_text'    => "Renew {$plan->name}",
                'confidence'  => 'high',
            ]);
        }

        $headroom = $closest
            ? "Your busiest feature, {$closest['name']}, is at {$closest['percent']}% of its limit."
            : 'Every feature has plenty of room.';

        return $this->recommendationPayload('stay', $plan, [
            'headline'    => "The {$plan->name} fits your school today",
            'description' => "{$headroom} We'll suggest an upgrade as soon as you get close to a limit.",
            'cta_text'    => 'Compare plans',
            'confidence'  => 'high',
        ]);
    }

    protected function reasons(Collection $triggers, ?Plan $plan): array
    {
        $features = $plan?->planFeature->keyBy(fn($pf) => $pf->feature?->key) ?? collect();

        return $triggers->map(function (array $trigger) use ($features) {
            $target = $features->get($trigger['key']);
            $isBoolean = $trigger['limit_type'] === 'boolean';
            $targetLimit = $target && !$isBoolean ? $this->limitOf($target->value) : null;

            return [
                'key'               => $trigger['key'],
                'name'              => $trigger['name'],
                'limit_type'        => $trigger['limit_type'],
                'used'              => $trigger['used'],
                'current_limit'     => $trigger['limit'],
                'recommended_limit' => $targetLimit,
                'recommended_unlimited' => $target && !$isBoolean && $targetLimit === null,
                'percent'           => $trigger['percent'],
                'status'            => $trigger['status'],
                'message'           => $isBoolean
                    ? "{$trigger['name']} is not included in your plan."
                    : "{$trigger['used']} of {$trigger['limit']} used ({$trigger['percent']}%).",
            ];
        })->values()->all();
    }

    protected function recommendationPayload(string $type, Plan $plan, array $data): array
    {
        return array_merge([
            'type'            => $type,
            'plan_id'         => $plan->id,
            'plan'            => $this->planSummary($plan),
            // kept for clients of the old payload
            'recommended_plan' => $this->planSummary($plan),
            'headline'        => null,
            'description'     => null,
            'cta_text'        => null,
            'priority'        => null,
            'trigger_feature' => null,
            'reasons'         => [],
            'score'           => null,
            'confidence'      => 'medium',
        ], $data, [
            // legacy keys
            'title' => $data['headline'] ?? null,
        ]);
    }

    /**
     * Every plan with its limits compared with the current plan.
     */
    protected function comparePlans(Collection $plans, ?Plan $currentPlan, ?array $recommendation, ?string $popularPlanId): array
    {
        $currentFeatures = $currentPlan?->planFeature->keyBy(fn($pf) => $pf->feature?->key) ?? collect();
        $currentPrice = $currentPlan ? (float) $currentPlan->price : null;
        $recommendedId = in_array($recommendation['type'] ?? null, ['upgrade', 'start'], true)
            ? $recommendation['plan_id']
            : null;

        return $plans->values()->map(function (Plan $plan, int $index) use ($currentFeatures, $currentPlan, $currentPrice, $recommendedId, $popularPlanId) {
            $price = (float) $plan->price;
            $isCurrent = $currentPlan && $plan->id === $currentPlan->id;

            $features = $plan->planFeature
                ->filter(fn($pf) => $pf->feature && $pf->feature->status !== 'inactive')
                ->map(function (PlanFeature $planFeature) use ($currentFeatures) {
                    $feature = $planFeature->feature;
                    $type = strtolower($planFeature->type ?? $feature->limit_type ?? 'integer');
                    $current = $currentFeatures->get($feature->key);

                    if ($type === 'boolean') {
                        $enabled = $this->isEnabled($planFeature->value);
                        $currentEnabled = $current ? $this->isEnabled($current->value) : null;
                        return [
                            'key'        => $feature->key,
                            'name'       => $feature->name,
                            'description' => $feature->description,
                            'limit_type' => 'boolean',
                            'value'      => $enabled,
                            'unlimited'  => false,
                            'change'     => $currentEnabled === null ? null : ($enabled === $currentEnabled ? 'same' : ($enabled ? 'up' : 'down')),
                            'multiplier' => null,
                        ];
                    }

                    $limit = $this->limitOf($planFeature->value);
                    $currentLimit = $current ? $this->limitOf($current->value) : false;

                    return [
                        'key'         => $feature->key,
                        'name'        => $feature->name,
                        'description' => $feature->description,
                        'limit_type'  => $type,
                        'value'       => $limit,
                        'unlimited'   => $limit === null,
                        'change'      => $currentLimit === false ? null : $this->compareLimits($limit, $currentLimit),
                        'multiplier'  => $currentLimit === false || $limit === null || $currentLimit === null || $currentLimit <= 0
                            ? null
                            : round($limit / $currentLimit, 1),
                    ];
                })
                ->values();

            return array_merge($this->planSummary($plan), [
                'tier'           => $index + 1,
                'monthly_price'  => round($price / 12, 2),
                'is_current'     => $isCurrent,
                'is_recommended' => $plan->id === $recommendedId,
                'is_popular'     => $plan->id === $popularPlanId,
                'action'         => $isCurrent ? 'current' : ($currentPrice === null ? 'subscribe' : ($price > $currentPrice ? 'upgrade' : 'downgrade')),
                'price_delta'    => $currentPrice === null ? null : round($price - $currentPrice, 2),
                'upgrades'       => $features->where('change', 'up')->count(),
                'features'       => $features->all(),
            ]);
        })->all();
    }

    /**
     * The plan most active subscriptions use; the middle plan when none do.
     */
    protected function popularPlanId(Collection $plans): ?string
    {
        if ($plans->isEmpty()) {
            return null;
        }

        $counts = SchoolSubscription::whereIn('plan_id', $plans->pluck('id'))
            ->where('status', 'active')
            ->selectRaw('plan_id, count(*) as total')
            ->groupBy('plan_id')
            ->pluck('total', 'plan_id');

        if ($counts->filter()->isNotEmpty()) {
            return (string) $counts->sortDesc()->keys()->first();
        }

        return $plans->values()->get(intdiv($plans->count() - 1, 2))?->id;
    }

    protected function usageSummary(Collection $usage): array
    {
        $numeric = $usage->filter(fn($row) => $row['ratio'] !== null && !$row['unlimited']);
        $busiest = $numeric->first();

        return [
            'features'        => $usage->count(),
            'at_risk'         => $numeric->whereIn('status', ['warning', 'critical', 'exceeded'])->count(),
            'exceeded'        => $numeric->where('status', 'exceeded')->count(),
            'average_percent' => $numeric->isEmpty() ? 0 : (int) round($numeric->avg('percent')),
            'busiest'         => $busiest ? ['key' => $busiest['key'], 'name' => $busiest['name'], 'percent' => $busiest['percent']] : null,
        ];
    }

    protected function subscriptionSummary(?SchoolSubscription $subscription): ?array
    {
        if (!$subscription) {
            return null;
        }

        $start = $subscription->start_date ? Carbon::parse($subscription->start_date) : null;
        $end = $subscription->end_date ? Carbon::parse($subscription->end_date) : null;
        $daysLeft = $this->daysLeft($subscription);
        $daysTotal = $start && $end ? max(1, (int) $start->diffInDays($end)) : null;

        return [
            'id'          => $subscription->id,
            'status'      => $subscription->status,
            'start_date'  => $start?->toDateString(),
            'end_date'    => $end?->toDateString(),
            'days_left'   => $daysLeft,
            'days_total'  => $daysTotal,
            'elapsed_percent' => $daysTotal && $daysLeft !== null
                ? (int) min(100, max(0, round((1 - $daysLeft / $daysTotal) * 100)))
                : null,
            'renewal_due' => $daysLeft !== null && $daysLeft <= self::RENEWAL_WINDOW_DAYS,
            'plan'        => $subscription->plan ? $this->planSummary($subscription->plan) : null,
        ];
    }

    protected function planSummary(Plan $plan): array
    {
        return [
            'id'          => $plan->id,
            'key'         => $plan->key,
            'name'        => $plan->name,
            'description' => $plan->description,
            'price'       => (float) $plan->price,
            'currency'    => $plan->country->currency ?? null,
            'country_id'  => $plan->country_id,
            'max_plan'    => (bool) $plan->max_plan,
        ];
    }

    protected function daysLeft(SchoolSubscription $subscription): ?int
    {
        if (!$subscription->end_date) {
            return null;
        }

        return (int) max(0, now()->startOfDay()->diffInDays(Carbon::parse($subscription->end_date)->startOfDay(), false));
    }

    protected function usageStatus(float $ratio, bool $unlimited): string
    {
        return match (true) {
            $unlimited     => 'unlimited',
            $ratio >= 1    => 'exceeded',
            $ratio >= 0.9  => 'critical',
            $ratio >= self::DEFAULT_THRESHOLD => 'warning',
            default        => 'healthy',
        };
    }

    protected function compareLimits(?int $limit, ?int $currentLimit): string
    {
        if ($limit === $currentLimit) {
            return 'same';
        }
        if ($limit === null) {
            return 'up';
        }
        if ($currentLimit === null) {
            return 'down';
        }

        return $limit > $currentLimit ? 'up' : 'down';
    }

    /**
     * A numeric limit, or null for unlimited (negative values or no limit).
     */
    protected function limitOf(mixed $value): ?int
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }

        if (is_array($value)) {
            if (!empty($value['unlimited'])) {
                return null;
            }
            $value = $value['limit'] ?? $value['value'] ?? null;
        }

        if (!is_numeric($value) || $value < 0) {
            return null;
        }

        return (int) $value;
    }

    protected function isEnabled(mixed $value): bool
    {
        if (is_array($value)) {
            return (bool) ($value['enabled'] ?? $value['value'] ?? false);
        }

        return (bool) $value;
    }

    protected function decode(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }
}
