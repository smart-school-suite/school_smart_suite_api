<?php

namespace App\SchoolBranchSetting\Registry;

use App\SchoolBranchSetting\Handlers\Election\ElectionVoteTieAdministrativeVoteHandler;
use App\SchoolBranchSetting\Handlers\Election\ElectionVoteTieHighestGpaHandler;
use App\SchoolBranchSetting\Handlers\Exam\AutoCreateExamHandler;
use App\SchoolBranchSetting\Handlers\Exam\CaDefaultScoreHandler;
use App\SchoolBranchSetting\Handlers\Exam\ExamDefaultScoreHandler;
use App\SchoolBranchSetting\Handlers\Exam\ResitDefaultScoreHandler;
use App\SchoolBranchSetting\Handlers\GradeScale\AllowedLetterGradeHandler;
use App\SchoolBranchSetting\Handlers\GradeScale\MaxGpaHandler;
use App\SchoolBranchSetting\Handlers\GradeScale\PassingGpaHandler;
use App\SchoolBranchSetting\Handlers\Graduation\GraduationFeeHandler;
use App\SchoolBranchSetting\Handlers\Graduation\GraduationGeneralBillingFee;
use App\SchoolBranchSetting\Handlers\Graduation\LevelGraduationFeeBillingHandler;
use App\SchoolBranchSetting\Handlers\Graduation\LevelGraduationFeeHandler;
use App\SchoolBranchSetting\Handlers\Level\LevelRegistrationFeeHandler;
use App\SchoolBranchSetting\Handlers\Level\LevelSpecialtyAutoCreateHandler;
use App\SchoolBranchSetting\Handlers\Level\LevelStartHandler;
use App\SchoolBranchSetting\Handlers\Level\LevelTuitionFeeHandler;
use App\SchoolBranchSetting\Handlers\Promotion\ChargeStudentRegistrationFeeHandler;
use App\SchoolBranchSetting\Handlers\Promotion\MaxAdditionalFeeDebtHandler;
use App\SchoolBranchSetting\Handlers\Promotion\MaxCarryOverHandler;
use App\SchoolBranchSetting\Handlers\Promotion\MaxResitHandler;
use App\SchoolBranchSetting\Handlers\Promotion\MaxTuitionFeeDebtHandler;
use App\SchoolBranchSetting\Handlers\Promotion\MinAverageGpaHandler;
use App\SchoolBranchSetting\Handlers\Resit\ResitGeneralBillingFeeHandler;
use App\SchoolBranchSetting\Handlers\Resit\ResitGeneralBillingHandler;
use App\SchoolBranchSetting\Handlers\Resit\ResitLevelBillingFeeHandler;
use App\SchoolBranchSetting\Handlers\Resit\ResitLevelBillingHandler;
use App\SchoolBranchSetting\Handlers\Semester\AutoCreateAcrossSwitchHandler;
use App\SchoolBranchSetting\Handlers\Semester\AutoCreateLevelsHandler;
use App\SchoolBranchSetting\Handlers\Semester\AutoCreateModeHandler;
use App\SchoolBranchSetting\Handlers\Semester\AutoCreateRequireStudentsHandler;
use App\SchoolBranchSetting\Handlers\SemesterTimetable\IgnoreTeacherPreferenceHandler;
use App\SchoolBranchSetting\Handlers\SemesterTimetable\RespectTeacherPreferenceHandler;
use App\SchoolBranchSetting\Handlers\SemesterTimetable\SchoolDayHoursHandler;
use App\SchoolBranchSetting\Handlers\SemesterTimetable\TimetablePeriodDurationHandler;

class SettingUpdateRegistry
{
  public function handlers(): array {
      return [

          'promotion.max_tuition_fee_debt' => MaxTuitionFeeDebtHandler::class,
          'promotion.max_additional_fee_debt' => MaxAdditionalFeeDebtHandler::class,
          'promotion.max_carry_overs' => MaxCarryOverHandler::class,
          'promotion.min_gpa' => MinAverageGpaHandler::class,
          'promotion.bill_student_registration_fee' => ChargeStudentRegistrationFeeHandler::class,
          'promotion.max_resits' => MaxResitHandler::class,
          //

          'resitFee.generalBilling' => ResitGeneralBillingHandler::class,
          'resitFee.levelBilling' =>  ResitLevelBillingHandler::class,
          'resitFee.generalBillingFee' => ResitGeneralBillingFeeHandler::class,
          'resitFee.levelBillingFee' => ResitLevelBillingFeeHandler::class,
          //

          'grade.max_gpa' => MaxGpaHandler::class,
          'grade.passing_gpa' => PassingGpaHandler::class,
          'grade.allowed_letter_grades' => AllowedLetterGradeHandler::class,
          //

          'graduation.fee_enabled' => GraduationFeeHandler::class,
          'graduation.fee' => GraduationGeneralBillingFee::class,
          'graduation.level_graduation_fee_enabled' => LevelGraduationFeeHandler::class,
          'graduation.level_graduation_fee' => LevelGraduationFeeBillingHandler::class,
          //

          'level.start' => LevelStartHandler::class,
          'specialties.auto_generate_levels' => LevelSpecialtyAutoCreateHandler::class,
          'level.registration_fees' => LevelRegistrationFeeHandler::class,
          'level.tuition_fees' => LevelTuitionFeeHandler::class,
          //

          'exam.auto_create' => AutoCreateExamHandler::class,
          'exam.ca_default_max_score' => CaDefaultScoreHandler::class,
          'exam.exam_default_max_score' => ExamDefaultScoreHandler::class,
          'exam.resit_exam_default_max_score' => ResitDefaultScoreHandler::class,

          //

          'semester.auto_create_mode' => AutoCreateModeHandler::class,
          'semester.auto_create_levels' => AutoCreateLevelsHandler::class,
          'semester.auto_create_across_specialties' => AutoCreateAcrossSwitchHandler::class,
          'semester.auto_create_across_all_levels' => AutoCreateAcrossSwitchHandler::class,
          'semester.auto_create_require_students' => AutoCreateRequireStudentsHandler::class,

          //
          'timetable.respect_teacher_preference' => RespectTeacherPreferenceHandler::class,
          'timetable.ignore_teacher_preference' => IgnoreTeacherPreferenceHandler::class,
          'timetable.period_duration' => TimetablePeriodDurationHandler::class,
          'timetable.school_day_start' => SchoolDayHoursHandler::class,
          'timetable.school_day_end' => SchoolDayHoursHandler::class,

          //
          'election_tie_breaker.highest_gpa_winner' => ElectionVoteTieHighestGpaHandler::class,
          'election_tie_breaker.highest_admin_votes' => ElectionVoteTieAdministrativeVoteHandler::class,

      ];
  }
}
