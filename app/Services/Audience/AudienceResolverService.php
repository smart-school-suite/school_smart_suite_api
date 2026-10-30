<?php

namespace App\Services\Audience;

use App\Models\Schooladmin;
use App\Models\Schoolbranches;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;

class AudienceResolverService
{

    public function resolve(object $currentSchool, array $data): ResolvedAudienceService
    {
        if (filter_var($data['school_wide'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return $this->resolveSchoolWide($currentSchool);
        }

        $result = new ResolvedAudienceService();

        $admins   = AudienceCriteriaService::fromBlocks($data['admin_audience'] ?? null);
        $teachers = AudienceCriteriaService::fromBlocks($data['teacher_audience'] ?? null);
        $students = AudienceCriteriaService::fromBlocks($data['student_audience'] ?? null);

        $this->resolveInto($result, $this->adminQuery($currentSchool, $admins), $admins);
        $this->resolveInto($result, $this->teacherQuery($currentSchool, $teachers), $teachers);
        $this->resolveInto($result, $this->studentQuery($currentSchool, $students), $students);

        return $result;
    }

    private function resolveSchoolWide(object $currentSchool): ResolvedAudienceService
    {
        $result = new ResolvedAudienceService();
        $branch = $this->getAllAudience($currentSchool);

        $result->add(Schooladmin::class, $branch->schooladmin->pluck('id')->all());
        $result->add(Teacher::class, $branch->teacher->pluck('id')->all());
        $result->add(Student::class, $branch->student->pluck('id')->all());

        return $result;
    }

    private function getAllAudience(object $currentSchool): Schoolbranches
    {
        return Schoolbranches::where("id", $currentSchool->id)
            ->with([
                'student:id,school_branch_id',
                'teacher:id,school_branch_id',
                'schooladmin:id,school_branch_id',
            ])
            ->firstOrFail();
    }

    public function adminQuery(object $currentSchool, AudienceCriteriaService $criteria): Builder
    {
        $query = $this->baseQuery(Schooladmin::class, $currentSchool);
        $table = $query->getModel()->getTable();

        if (empty($criteria->individualIds)) {
            return $this->none($query);
        }

        return $query->whereIn("{$table}.id", $criteria->individualIds);
    }

    public function teacherQuery(object $currentSchool, AudienceCriteriaService $criteria): Builder
    {
        $query = $this->baseQuery(Teacher::class, $currentSchool);

        if ($criteria->isEmpty()) {
            return $this->none($query);
        }

        $table = $query->getModel()->getTable();

        return $query->where(function (Builder $q) use ($criteria, $table) {
            if (!empty($criteria->individualIds)) {
                $q->orWhereIn("{$table}.id", $criteria->individualIds);
            }


            if ($criteria->hasSpecialtyScope()) {
                $q->orWhereHas('specialty', function ($sq) use ($criteria) {
                    $this->constrainSpecialty($sq, $criteria);
                });
            }
        });
    }

    public function studentQuery(object $currentSchool, AudienceCriteriaService $criteria): Builder
    {
        $query = $this->baseQuery(Student::class, $currentSchool);

        if ($criteria->isEmpty()) {
            return $this->none($query);
        }

        $table = $query->getModel()->getTable();

        return $query->where(function (Builder $q) use ($criteria, $table) {
            if (!empty($criteria->individualIds)) {
                $q->orWhereIn("{$table}.id", $criteria->individualIds);
            }

            if (!empty($criteria->specialtyIds)) {
                $q->orWhereIn("{$table}.specialty_id", $criteria->specialtyIds);
            }

            if (!empty($criteria->departmentIds) || !empty($criteria->levelIds)) {
                $q->orWhereHas('specialty', function ($sq) use ($criteria) {
                    $specialtyTable = $sq->getModel()->getTable();

                    $sq->where(function ($w) use ($criteria, $specialtyTable) {
                        if (!empty($criteria->departmentIds)) {
                            $w->orWhereIn("{$specialtyTable}.department_id", $criteria->departmentIds);
                        }
                        if (!empty($criteria->levelIds)) {
                            $w->orWhereIn("{$specialtyTable}.level_id", $criteria->levelIds);
                        }
                    });
                });
            }
        });
    }

    protected function baseQuery(string $modelClass, object $currentSchool): Builder
    {
        $table = (new $modelClass())->getTable();

        return $modelClass::query()->where("{$table}.school_branch_id", $currentSchool->id);
    }

    private function none(Builder $query): Builder
    {
        return $query->whereRaw('1 = 0');
    }

    private function constrainSpecialty($specialtyQuery, AudienceCriteriaService $criteria): void
    {
        $specialtyTable = $specialtyQuery->getModel()->getTable();

        $specialtyQuery->where(function ($w) use ($criteria, $specialtyTable) {
            if (!empty($criteria->specialtyIds)) {
                $w->orWhereIn("{$specialtyTable}.id", $criteria->specialtyIds);
            }
            if (!empty($criteria->departmentIds)) {
                $w->orWhereIn("{$specialtyTable}.department_id", $criteria->departmentIds);
            }
            if (!empty($criteria->levelIds)) {
                $w->orWhereIn("{$specialtyTable}.level_id", $criteria->levelIds);
            }
        });
    }

    private function resolveInto(ResolvedAudienceService $result, Builder $query, AudienceCriteriaService $criteria): void
    {
        $model = $query->getModel();

        if ($criteria->isEmpty()) {
            $result->add(get_class($model), [], []);
            return;
        }

        $ids = $query->pluck($model->getTable() . '.' . $model->getKeyName())->all();

        $missing = array_values(array_diff(
            array_map('strval', $criteria->individualIds),
            array_map('strval', $ids)
        ));

        $result->add(get_class($model), $ids, $missing);
    }
}
