<?php

namespace App\Services\SubscriptionPlan;

use App\Models\SchoolSubscription;
use App\Models\SubscriptionUsage;
use App\Models\PlanFeature;
use App\Models\PlanRecCopy;
use App\Models\PlanRecommendation;
use Illuminate\Support\Facades\Log;

class PlanRecommendationService
{
    public function recommendPlan($currentSchool)
    {
        Log::info('recommendPlan started', ['school_id' => $currentSchool->id]);

        $currentPlan = SchoolSubscription::where("school_branch_id", $currentSchool->id)
            ->where("status", "active")
            ->where("country_id", $currentSchool->school->country_id)
            ->first();

        if (!$currentPlan) {
            Log::warning('No active subscription plan found for school', [
                'school_id' => $currentSchool->id,
                'country_id' => $currentSchool->country_id
            ]);
            return null;
        }

        Log::info('Found current plan', ['plan_id' => $currentPlan->id, 'plan_name' => $currentPlan->plan?->name ?? 'unknown']);

        $currentPlanUsage = SubscriptionUsage::where("school_branch_id", $currentSchool->id)
            ->with(['featurePlan'])
            ->where("subscription_id", $currentPlan->id)
            ->get();

        Log::info('Current plan usage count', ['usage_count' => $currentPlanUsage->count()]);

        $recommendations = PlanRecommendation::with(['planRecCondition', 'feature', 'targetPlan'])
            ->where('source_plan_id', $currentPlan->plan_id)
            ->where('status', "active")
            ->get();

        Log::info('Found recommendations', ['count' => $recommendations->count()]);

        if ($recommendations->isEmpty()) {
            Log::info('No active recommendations found for source plan', ['source_plan_id' => $currentPlan->plan_id]);
            return null;
        }

        Log::info("Recommendation Ids", $recommendations->pluck('id')->toArray());
        $copies = PlanRecCopy::whereIn("plan_rec_cond_id", $recommendations->pluck('id')->toArray())
            ->get();

        Log::info('Found copy entries', ['copy_count' => $copies->count()]);

        $validRecommendations = [];

        foreach ($recommendations as $recommendation) {
            $feature = $recommendation->feature;
            Log::info("Recommendations data", $recommendation->feature->toArray());
            if (!$feature) {
                Log::warning('Recommendation has no feature', ['rec_id' => $recommendation->id]);
                continue;
            }

            $usage = $currentPlanUsage->get($feature->id);

            if (!$usage) {
                Log::info('No usage record for this feature', [
                    'feature_id' => $feature->id,
                    'feature_name' => $feature->name ?? 'unknown',
                    'rec_id' => $recommendation->id
                ]);
                continue;
            }

            $planFeature = PlanFeature::where('plan_id', $currentPlan->plan_id)
                ->where('feature_id', $feature->id)
                ->first();

            if (!$planFeature) {
                Log::warning('No PlanFeature entry for this plan + feature', [
                    'plan_id' => $currentPlan->plan_id,
                    'feature_id' => $feature->id,
                    'rec_id' => $recommendation->id
                ]);
                continue;
            }

            $shouldRecommend = $this->evaluateRecommendation($feature, $usage, $planFeature, $recommendation->conditions);

            Log::debug('Evaluated recommendation', [
                'rec_id' => $recommendation->id,
                'feature_id' => $feature->id,
                'feature_name' => $feature->name ?? 'unknown',
                'limit_type' => $feature->limit_type,
                'should_recommend' => $shouldRecommend,
                'priority' => $recommendation->priority
            ]);

            if ($shouldRecommend) {
                $validRecommendations[] = $recommendation;
            }
        }

        $validRecommendations = collect($validRecommendations)->sortByDesc('priority');

        Log::info('Valid recommendations after evaluation', [
            'valid_count' => $validRecommendations->count(),
            'priorities' => $validRecommendations->pluck('priority')->toArray()
        ]);

        if ($validRecommendations->isNotEmpty()) {
            $topRecommendation = $validRecommendations->first();
            $copy = $copies->where("plan_rec_cond_id", $topRecommendation->id)->first();

            Log::info('Returning top recommendation', [
                'target_plan_id' => $topRecommendation->targetPlan?->id,
                'feature_id' => $topRecommendation->feature?->id,
                'has_copy' => $copy !== null
            ]);

            return [
                'recommended_plan' => $topRecommendation->targetPlan,
                'feature_trigger' => $topRecommendation->feature,
                'priority' => $topRecommendation->priority,
                'title' => $copy?->title ?? null,
                'description' => $copy?->description ?? null,
                'cta_text' => $copy?->cta_text ?? null,
            ];
        }

        Log::info('No valid recommendations found after filtering');
        return null;
    }

    protected function evaluateRecommendation($feature, $usage, $planFeature, $conditions): bool
    {
        foreach ($conditions as $condition) {
            $operator = $condition->operator;
            $value    = json_decode($condition->value, true);

            switch ($feature->limit_type) {
                case 'decimal':
                case 'integer':
                    // usage ratio
                    $used  = $usage->used['current_usage'] ?? 0;
                    $limit = $planFeature->value['limit'] ?? 0;

                    if ($limit <= 0) {
                        break;                    // ← fixed here
                    }

                    $ratio = $used / $limit;

                    if ($operator === 'percentage' && isset($value['threshold'])) {
                        if ($ratio >= $value['threshold']) {
                            return true;
                        }
                    }

                    if ($operator === '>=' && isset($value['threshold'])) {
                        if ($used >= $value['threshold']) {
                            return true;
                        }
                    }
                    break;

                case 'boolean':
                    $enabled = $planFeature->value['enabled'] ?? false;
                    if ($operator === 'enabled' && isset($value['required'])) {
                        if ($value['required'] && !$enabled) {
                            return true;
                        }
                    }
                    break;
            }
        }

        return false;
    }
}
