<?php

namespace App\Services\Election\Support;

use App\Services\Audience\AudienceResolverService;
use App\Services\Audience\ResolvedAudienceService;
use Illuminate\Support\Arr;

/**
 * Resolves the elections.participants JSON configuration into concrete
 * voter / applicant sets, reusing the announcement audience resolver.
 *
 * Configuration shape (each group is configured independently):
 * {
 *   "voters":     { "school_wide": bool, "student_audience": [...], "teacher_audience": [...],
 *                   "admin_audience": [...], "exclude": { "student_ids": [], "teacher_ids": [], "admin_ids": [] } },
 *   "candidates": { ...same as voters..., "must_be_voter": bool }
 * }
 * Audience blocks use the announcement block format
 * ({ department_ids, specialty_ids, level_ids, individual_ids }).
 */
class ElectionAudienceResolver
{
    public const GROUP_VOTERS = 'voters';
    public const GROUP_CANDIDATES = 'candidates';

    private const EXCLUDE_KEYS = [
        'student_ids' => ElectionParticipantType::STUDENT,
        'teacher_ids' => ElectionParticipantType::TEACHER,
        'admin_ids' => ElectionParticipantType::SCHOOL_ADMIN,
    ];

    public function __construct(private AudienceResolverService $audienceResolver) {}

    /**
     * Normalise a raw participants payload into the stored JSON shape.
     */
    public function normalise(array $config): array
    {
        $out = [];
        foreach ([self::GROUP_VOTERS, self::GROUP_CANDIDATES] as $group) {
            $block = $config[$group] ?? [];
            $out[$group] = array_filter([
                'school_wide' => filter_var($block['school_wide'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'student_audience' => array_values($block['student_audience'] ?? []),
                'teacher_audience' => array_values($block['teacher_audience'] ?? []),
                'admin_audience' => array_values($block['admin_audience'] ?? []),
                'exclude' => Arr::only($block['exclude'] ?? [], array_keys(self::EXCLUDE_KEYS)),
            ], fn($value) => $value !== [] && $value !== null);

            if ($group === self::GROUP_CANDIDATES) {
                $out[$group]['must_be_voter'] = filter_var($block['must_be_voter'] ?? false, FILTER_VALIDATE_BOOLEAN);
            }
        }
        return $out;
    }

    public function isGroupConfigured(array $config, string $group): bool
    {
        $block = $config[$group] ?? [];
        return !empty($block['school_wide'])
            || !empty($block['student_audience'])
            || !empty($block['teacher_audience'])
            || !empty($block['admin_audience']);
    }

    /**
     * Resolve a configuration into [morphClass => [id => ['can_vote' => bool, 'can_apply' => bool]]].
     *
     * @return array{rows: array<string, array<string, array{can_vote: bool, can_apply: bool}>>, missing: array}
     */
    public function resolve(object $currentSchool, array $config): array
    {
        $voters = $this->resolveGroup($currentSchool, $config[self::GROUP_VOTERS] ?? []);
        $applicants = $this->resolveGroup($currentSchool, $config[self::GROUP_CANDIDATES] ?? []);
        $mustBeVoter = (bool) ($config[self::GROUP_CANDIDATES]['must_be_voter'] ?? false);

        $rows = [];
        foreach ($voters['ids'] as $class => $ids) {
            foreach ($ids as $id) {
                $rows[$class][$id] = ['can_vote' => true, 'can_apply' => false];
            }
        }
        foreach ($applicants['ids'] as $class => $ids) {
            foreach ($ids as $id) {
                if ($mustBeVoter && !isset($rows[$class][$id])) {
                    continue;
                }
                $rows[$class][$id] = [
                    'can_vote' => $rows[$class][$id]['can_vote'] ?? false,
                    'can_apply' => true,
                ];
            }
        }

        return [
            'rows' => $rows,
            'missing' => array_merge_recursive($voters['missing'], $applicants['missing']),
        ];
    }

    /**
     * @return array{ids: array<string, array<int, string>>, missing: array}
     */
    private function resolveGroup(object $currentSchool, array $block): array
    {
        if (!$this->isGroupConfigured([self::GROUP_VOTERS => $block], self::GROUP_VOTERS)) {
            return ['ids' => [], 'missing' => []];
        }

        /** @var ResolvedAudienceService $resolved */
        $resolved = $this->audienceResolver->resolve($currentSchool, [
            'school_wide' => $block['school_wide'] ?? false,
            'student_audience' => $block['student_audience'] ?? null,
            'teacher_audience' => $block['teacher_audience'] ?? null,
            'admin_audience' => $block['admin_audience'] ?? null,
        ]);

        $ids = [];
        foreach (ElectionParticipantType::MAP as $key => $class) {
            $classIds = $resolved->idsFor($class);
            $excluded = $this->excludedIdsFor($block, $key);
            $classIds = array_values(array_diff(array_map('strval', $classIds), $excluded));
            if ($classIds) {
                $ids[$class] = $classIds;
            }
        }

        return ['ids' => $ids, 'missing' => $resolved->missing()];
    }

    private function excludedIdsFor(array $block, string $typeKey): array
    {
        $key = array_search($typeKey, self::EXCLUDE_KEYS, true);
        return array_map('strval', $block['exclude'][$key] ?? []);
    }
}
