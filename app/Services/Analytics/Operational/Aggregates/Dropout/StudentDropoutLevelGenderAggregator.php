<?php

namespace App\Services\Analytics\Operational\Aggregates\Dropout;

use App\Models\Gender;
use Illuminate\Database\Query\Builder;
use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use App\Models\Educationlevels;

class StudentDropoutLevelGenderAggregator
{
    public function calculate(Builder $query, $filter) {
         if($filter['gender']){
             return $this->byGender($query);
         }
         if($filter['level']){
             return $this->byLevel($query);
         }
    }

    protected function byLevel(Builder $query)
    {
        $levels = Educationlevels::all()->pluck('id');
        $levels->map(function ($levelId) use ($query) {
            $enrollments = (clone $query)
                ->where("level_id", $levelId)
                ->where("kpi", EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS)
                ->sum("value");
            $totalDropout = (clone $query)
                ->where("level_id", $levelId)
                ->where("kpi", EnrollmentAnalyticsKpi::STUDENT_DROPOUT)
                ->sum("value");
            return [
                "level" => Educationlevels::find($levelId)->name ?? "unknown",
                "level_number" => Educationlevels::find($levelId)->level ?? "unknown",
                "total_enrollment" => $enrollments ?? 0,
                "total_dropout" => $totalDropout ?? 0,
                "dropout_rate" => $this->rate($enrollments, $totalDropout)
            ];
        });
    }
    protected function byGender(Builder $query)
    {
        $genders = Gender::all()->pluck('id');
        $genders->map(function ($genderId) use ($query) {
            $enrollments = (clone $query)
                ->where('gender_id', $genderId)
                ->where('kpi', EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS)
                ->sum('value');
            $totalDropout = (clone $query)
                ->where("gender_id", $genderId)
                ->where("kpi", EnrollmentAnalyticsKpi::STUDENT_DROPOUT)
                ->where("value");
            return [
                "gender" => Gender::find($genderId)->name ?? "unknown",
                "gender_id" => $genderId,
                "total_dropout" => $totalDropout ?? 0,
                "total_enrollments" => $enrollments ?? 0,
                "dropout_rate" => $this->rate($enrollments, $totalDropout)
            ];
        });
    }

    protected function rate($enrollments, $totalDropout): float
    {
        if ($totalDropout == 0) {
            return 0.0;
        }

        return round(($totalDropout / $enrollments) * 100, 2);
    }
}
