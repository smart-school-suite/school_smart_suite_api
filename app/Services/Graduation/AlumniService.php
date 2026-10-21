<?php

namespace App\Services\Graduation;

use App\Models\Promotion\Alumni;
use App\Exceptions\AppException;
use App\Models\Specialty;
use App\Models\Educationlevels;
use Illuminate\Support\Collection;
use App\Models\SchoolBranchSetting;
use Illuminate\Support\Facades\DB;
class AlumniService
{
    public function getAlumni(object $currentSchool)
    {
        $alumni = Alumni::where('school_branch_id', $currentSchool->id)
            ->with([
                'graduationRecord.batch',
                'graduationRecord.specialty.level',
                'graduationRecord.promotionRun.schoolYear',
                'graduationRecord.student',
            ])
            ->get();

        if ($alumni->isEmpty()) {
            throw new AppException(
                "Alumni Records Not Found",
                404,
                "Resource Not Found",
                "No alumni records found for the specified school branch."
            );
        }

        return $alumni;

        }
    public function getAlumnusDetails(object $currentSchool, string $alumniId)
    {
        $alumnus = Alumni::where('school_branch_id', $currentSchool->id)
            ->with([
                'graduationRecord.batch',
                'graduationRecord.specialty.level',
                'graduationRecord.promotionRun',
                'graduationRecord.student',
            ])
            ->where('id', $alumniId)
            ->first();

        if (!$alumnus) {
            throw new AppException(
                "Alumnus Not Found",
                404,
                "Resource Not Found",
                "Alumnus record not found."
            );
        }

        return $alumnus;
    }
    public function reinstateAlumnus(object $currentSchool, string $alumnusId)
    {
        return DB::transaction(function () use ($currentSchool, $alumnusId) {
            $alumnus = Alumni::where('school_branch_id', $currentSchool->id)
                ->where('id', $alumnusId)
                ->with([
                    'graduationRecord.batch',
                    'graduationRecord.specialty.level',
                    'graduationRecord.promotionRun',
                    'graduationRecord.student',
                ])
                ->lockForUpdate()
                ->first();

            if (!$alumnus) {
                throw new AppException(
                    "Alumnus Not Found",
                    404,
                    "Resource Not Found",
                    "Alumnus record not found."
                );
            }

            $graduationRecord = $alumnus->graduationRecord;

            if (!$graduationRecord || !$graduationRecord->student) {
                throw new AppException(
                    "Student Information Not Found",
                    404,
                    "Resource Not Found",
                    "Associated student record could not be found."
                );
            }

            $levelSettings = $this->levelSetting($currentSchool->id);
            $levels = Educationlevels::whereHas(
                "levelType",
                fn($query) =>
                $query->where("program_name", $levelSettings->value)
            )->get();

            $specialty = $graduationRecord->specialty;
            $nextSpecialty = $this->getNextSpecialty($specialty, $levels);

            if (!$nextSpecialty) {
                throw new AppException(
                    "Next Specialty Not Found",
                    400,
                    "Action Cannot Be Processed",
                    "Unable to determine the next specialty for reinstatement."
                );
            }

            $graduationRecord->update([
                "status" => "reinstated"
            ]);

            $student = $graduationRecord->student;
            $student->update([
                'specialty_id' => $nextSpecialty->id
            ]);

            $alumnus->delete();

            return $student;
        });
    }
    private function levelSetting(string $schoolBranchId)
    {
        return  SchoolBranchSetting::where("school_branch_id", $schoolBranchId)
            ->with(['settingDefination'])
            ->whereHas('settingDefination', function ($query) {
                $query->where("key", "level.start");
            })->first();
    }
    private function getNextSpecialty(Specialty $specialty, Collection $levels): ?Specialty
    {
        $currentLevel = (int) $specialty->level->level;

        $nextLevelNumber = $levels
            ->pluck('level')
            ->map(fn($level) => (int) $level)
            ->filter(fn($level) => $level > $currentLevel)
            ->sort()
            ->first();

        if ($nextLevelNumber === null) {
            return null;
        }

        $nextEducationLevel = $levels->first(
            fn($level) => (int) $level->level === $nextLevelNumber
        );

        return Specialty::where("school_branch_id", $specialty->school_branch_id)
            ->where("name", $specialty->name)
            ->where("level_id", $nextEducationLevel->id)
            ->first();
    }
}
