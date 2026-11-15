<?php

namespace App\Services\ResitTimetable;

use App\Exceptions\AppException;
use App\Models\ResitExam;
use App\Models\ResitTimetable\ResitInvigilator;
use App\Models\ResitTimetable\ResitSessionInvigilator;
use App\Models\Schooladmin;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Who invigilates a resit exam: teachers and school admins, as for exams.
 * Same request and response shapes as InvigilatorService.
 */
class ResitInvigilatorService
{
    protected const TEACHER = 'teacher';
    protected const SCHOOLADMIN = 'school_admin';

    private const MODELS = [
        self::TEACHER => Teacher::class,
        self::SCHOOLADMIN => Schooladmin::class,
    ];

    public function assign(object $currentSchool, array $payload)
    {
        $resitExam = $this->resitExam($currentSchool, $payload['resit_exam_id'] ?? '');
        $prepared = [];
        $seen = [];

        foreach ($payload['invigilators'] ?? [] as $invigilator) {
            $type = strtolower($invigilator['actorable_type'] ?? '');
            $model = self::MODELS[$type] ?? null;
            $actorId = $invigilator['actorable_id'] ?? null;

            if (!$model || !$actorId) {
                throw new AppException(
                    'Invalid invigilator type',
                    400,
                    'Invalid Selection',
                    'One of the selected people cannot be assigned as an invigilator. Please choose a valid teacher or school admin.'
                );
            }

            $key = "{$model}|{$actorId}";
            if (isset($seen[$key])) {
                throw new AppException('Duplicate invigilator in request', 400, 'Duplicate Selection', 'The same person was selected more than once. Please review your selections and try again.');
            }
            $seen[$key] = true;
            $prepared[] = ['actorable_id' => $actorId, 'actorable_type' => $model];
        }

        if (empty($prepared)) {
            throw new AppException('Missing required details', 400, 'Incomplete Request', 'Select at least one person to assign as an invigilator.');
        }

        $teacherIds = collect($prepared)->where('actorable_type', Teacher::class)->pluck('actorable_id');
        $adminIds = collect($prepared)->where('actorable_type', Schooladmin::class)->pluck('actorable_id');
        $valid = [
            ...Teacher::where('school_branch_id', $currentSchool->id)->whereIn('id', $teacherIds)->pluck('id')->all(),
            ...Schooladmin::where('school_branch_id', $currentSchool->id)->whereIn('id', $adminIds)->pluck('id')->all(),
        ];

        foreach ($prepared as $item) {
            if (!in_array($item['actorable_id'], $valid, true)) {
                throw new AppException('Invigilator not found', 404, 'Invigilator Unavailable', "We couldn't find one of the selected people in your school. Please pick people from your school and try again.");
            }
        }

        $existing = ResitInvigilator::where('resit_exam_id', $resitExam->id)
            ->where('school_branch_id', $currentSchool->id)
            ->get(['actorable_id', 'actorable_type'])
            ->map(fn($row) => "{$row->actorable_type}|{$row->actorable_id}")
            ->all();

        foreach ($prepared as $item) {
            if (in_array("{$item['actorable_type']}|{$item['actorable_id']}", $existing, true)) {
                throw new AppException('Invigilator already assigned', 409, 'Already Assigned', 'One of the selected people is already an invigilator for this resit exam. Please choose someone else.');
            }
        }

        $now = now();
        $records = array_map(fn(array $item) => [
            'id' => (string) Str::uuid(),
            'school_branch_id' => $currentSchool->id,
            'resit_exam_id' => $resitExam->id,
            'actorable_id' => $item['actorable_id'],
            'actorable_type' => $item['actorable_type'],
            'created_at' => $now,
            'updated_at' => $now,
        ], $prepared);

        DB::transaction(fn() => ResitInvigilator::insert($records));

        return ResitInvigilator::where('resit_exam_id', $resitExam->id)
            ->where('school_branch_id', $currentSchool->id)
            ->whereIn('actorable_id', array_column($prepared, 'actorable_id'))
            ->get();
    }

    public function assigned(object $currentSchool, string $resitExamId)
    {
        $invigilators = ResitInvigilator::with('invigilatable')
            ->where('resit_exam_id', $resitExamId)
            ->where('school_branch_id', $currentSchool->id)
            ->get();

        $shape = fn(ResitInvigilator $invigilator, string $type) => [
            'id' => $invigilator->id,
            'resit_exam_id' => $invigilator->resit_exam_id,
            'actorable_id' => $invigilator->actorable_id,
            'actorable_type' => $type,
            'name' => $invigilator->invigilatable?->name,
            'username' => $invigilator->invigilatable?->username,
            'profile_picture' => $invigilator->invigilatable?->profile_picture,
            'email' => $invigilator->invigilatable?->email,
            'phone' => $invigilator->invigilatable?->phone,
        ];

        return collect([
            'teachers' => $invigilators->where('actorable_type', Teacher::class)->map(fn($i) => $shape($i, self::TEACHER))->values(),
            'school_admins' => $invigilators->where('actorable_type', Schooladmin::class)->map(fn($i) => $shape($i, self::SCHOOLADMIN))->values(),
        ])->reject(fn($group) => $group->isEmpty());
    }

    public function potential(object $currentSchool, string $resitExamId)
    {
        $resitExam = $this->resitExam($currentSchool, $resitExamId);

        $assigned = ResitInvigilator::where('resit_exam_id', $resitExam->id)
            ->where('school_branch_id', $currentSchool->id)
            ->get(['actorable_id', 'actorable_type'])
            ->groupBy('actorable_type')
            ->map(fn($rows) => $rows->pluck('actorable_id')->all());

        $shape = fn($person, string $type) => [
            'actorable_id' => $person->id,
            'actorable_type' => $type,
            'name' => $person->name,
            'username' => $person->username,
            'profile_picture' => $person->profile_picture,
            'email' => $person->email,
        ];

        return collect([
            'teachers' => Teacher::where('school_branch_id', $currentSchool->id)
                ->whereNotIn('id', $assigned[Teacher::class] ?? [])
                ->get()
                ->map(fn($teacher) => $shape($teacher, self::TEACHER))
                ->values(),
            'school_admins' => Schooladmin::where('school_branch_id', $currentSchool->id)
                ->whereNotIn('id', $assigned[Schooladmin::class] ?? [])
                ->get()
                ->map(fn($admin) => $shape($admin, self::SCHOOLADMIN))
                ->values(),
        ])->reject(fn($group) => $group->isEmpty());
    }

    public function remove(object $currentSchool, array $payload): array
    {
        $resitExamId = $payload['resit_exam_id'] ?? null;
        $ids = array_values(array_unique($payload['invigilator_ids'] ?? []));

        $existing = ResitInvigilator::where('resit_exam_id', $resitExamId)
            ->where('school_branch_id', $currentSchool->id)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->all();

        if (empty($ids) || count($existing) !== count($ids)) {
            throw new AppException('Invigilator not assigned', 404, 'Not Assigned', 'One or more of the selected invigilators are not currently assigned to this resit exam.');
        }

        // Someone the active resit timetable relies on can't be removed without a gap in it.
        $inUse = ResitSessionInvigilator::whereIn('invigilator_id', $existing)->exists();
        if ($inUse) {
            throw new AppException(
                'Invigilator used by the active resit timetable',
                422,
                'Invigilator In Use',
                'One of the selected invigilators supervises a resit in the active resit timetable. Generate and activate a new version without them first.'
            );
        }

        DB::transaction(fn() => ResitInvigilator::whereIn('id', $existing)->delete());

        return ['removed' => count($existing)];
    }

    private function resitExam(object $currentSchool, string $resitExamId): ResitExam
    {
        return ResitExam::where('school_branch_id', $currentSchool->id)->find($resitExamId)
            ?? throw new AppException('Resit exam not found', 404, 'Resit Exam Unavailable', "We couldn't find the resit exam you're looking for. Please try again.");
    }
}
