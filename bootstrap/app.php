<?php

use App\Exceptions\AppException;
use App\Exceptions\AuthException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\IdentifyTenant;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Services\ApiResponseService;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::prefix('api/v1/auth/student')
                ->group(base_path('routes/Auth/Student.php'));

            Route::prefix('api/v1/auth/school-admin')
                ->group(base_path('routes/Auth/SchoolAdmin.php'));

            Route::prefix('api/v1/school-admin')
               ->group(base_path('routes/Notification/SchoolAdminNotification.php'));

            Route::prefix('api/v1/auth/teacher')
                ->group(base_path('routes/Auth/Teacher.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/teacher')
                ->group(base_path('routes/Teacher/Teacher.php'));
            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/resit-candidate')
               ->group(base_path('routes/Exam/ResitExamCandidate.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/school-admin')
                ->group(base_path('routes/SchoolAdmin/SchoolAdmin.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/student')
                ->group(base_path('routes/Student/Student.php'));

            Route::middleware(['auth:sanctum', IdentifyTenant::class])->prefix('api/v1/role')
                ->group(base_path('routes/RoleAndPermission/Role.php'));

            Route::middleware(['auth:sanctum', IdentifyTenant::class])->prefix('api/v1/permission')
                ->group(base_path('routes/RoleAndPermission/Permission.php'));

            Route::prefix('api/v1/school-branch')
                ->group(base_path('routes/School/SchoolBranch.php'));

            Route::prefix('api/v1/school')
                ->group(base_path('routes/School/School.php'));

            Route::prefix('api/v1/country')
                ->group(base_path('routes/Country/Country.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/department')
                ->group(base_path('routes/Department/Department.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/hod')
                ->group(base_path('routes/Department/Hod.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/specialty')
                ->group(base_path('routes/Specialty/Specialty.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/hos')
                ->group(base_path('routes/Specialty/Hos.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/course')
                ->group(base_path('routes/Course/Course.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/parent')
                ->group(base_path('routes/Parent/Parent.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/mark')
                ->group(base_path('routes/Mark/Mark.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/teacher-avialability')
                ->group(base_path('routes/Teacher/TeacherAvialability.php'));

            Route::middleware(['auth:sanctum'])->prefix('api/v1/level')
                ->group(base_path('routes/Level/Level.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/grade')
                ->group(base_path('routes/Grade/Grade.php'));

            Route::middleware(['auth:sanctum'])->prefix('api/v1/grade-category')
                ->group(base_path('routes/Grade/GradeCategory.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/exam')
                ->group(base_path('routes/Exam/Exam.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/exam-timetable')
                ->group(base_path('routes/Exam/ExamTimetable.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/timetable')
                ->group(base_path('routes/Specialty/Timetable.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/school-semester')
                ->group(base_path('routes/Semester/SchoolSemester.php'));

            Route::middleware(['auth:sanctum'])->prefix('api/v1/semester')
                ->group(base_path('routes/Semester/Semester.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/election-role')
                ->group(base_path("routes/Election/ElectionRole.php"));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/election')
                ->group(base_path('routes/Election/Election.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/election-application')
                ->group(base_path("routes/Election/ElectionApplication.php"));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/fee-schedule')
                ->group(base_path('routes/Fee/FeePaymentSchedule.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/fee-waiver')
                ->group(base_path("routes/Fee/FeeWaiver.php"));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/additional-fees')
                ->group(base_path("routes/Fee/AdditionalFee.php"));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/student-batch')
                ->group(base_path("routes/Student/StudentBatch.php"));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/additional-fee-category')
                ->group(base_path("routes/Fee/AdditionalFeeCategory.php"));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/fee-payment')
                ->group(base_path('routes/Fee/FeePayment.php'));

            Route::middleware(['auth:sanctum'])->prefix('api/v1/letter-grade')
                ->group(base_path("routes/Grade/LetterGrade.php"));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/exam-type')
                ->group(base_path("routes/Exam/ExamType.php"));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/expenses-category')
                ->group(base_path("routes/SchoolExpenses/SchoolExpensesCategory.php"));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/school-expenses')
                ->group(base_path("routes/SchoolExpenses/SchoolExpenses.php"));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/resit-timetable')
                ->group(base_path("routes/Resit/ResitTimetable.php"));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/student-resit')
                ->group(base_path("routes/Resit/StudentResit.php"));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/exam-candidate')
                ->group(base_path("routes/Exam/ExamCandidates.php"));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/exam-results')
                ->group(base_path("routes/Exam/ExamResults.php"));

            Route::prefix('api/v1/app-admin')
                ->group(base_path("routes/AppAdmin/AppAdmin.php"));

            Route::middleware(['auth:sanctum'])->prefix('api/v1/auth/app-admin')
                ->group(base_path("routes/Auth/AppAdmin.php"));

            Route::prefix('api/v1/subscription-rate')
                ->group(base_path("routes/Subscription/SubscriptionRates.php"));

            Route::prefix('api/v1/subscription-payment')
                ->group(base_path("routes/Subscription/SubscriptionPayment.php"));

            Route::prefix('api/v1/school-subscription')
                ->group(base_path("routes/Subscription/SchoolSubscription.php"));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/school-event')
                ->group(base_path("routes/Event/SchoolEvent.php"));

            Route::prefix('api/v1/student-promotion')
                ->group(base_path("routes/Student/StudentPromotion.php"));

            Route::prefix('api/v1/teacher-preference')
                ->group(base_path("routes/Teacher/TeacherSpecialtyPerference.php"));

            Route::prefix('api/v1/school-grades')
                ->group(base_path("routes/Grade/SchoolGrades.php"));

            Route::prefix('api/v1/stats')
                ->group(base_path("routes/Stats/FinancialStats.php"));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/academic-stats')
              ->group( base_path('routes/Stats/AcademicStats.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/financial-stats')
            ->group(base_path('routes/Stats/FinancialStats.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/operational-stats')
            ->group(base_path('routes/Stats/OperationalStats.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/resit-exam')
                ->group(base_path("routes/Exam/ResitExam.php"));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/school-group')
                ->group(base_path('routes/Audience/SchoolSetAudienceGroup.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/preset-audience')
                ->group(base_path('routes/Audience/PresetAudience.php'));

            Route::middleware(['auth:sanctum'])->prefix('api/v1/announcement-label')
                ->group(base_path('routes/Annnouncement/AnnouncementLabel.php'));

             Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/announcement-tag')
                ->group(base_path('routes/Annnouncement/AnnouncementTag.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/announcement-category')
                ->group(base_path('routes/Annnouncement/AnnouncementCategory.php'));

            Route::middleware(['auth:sanctum'])->prefix('api/v1/announcement-setting')
                ->group(base_path('routes/Annnouncement/AnnouncementSetting.php'));

             Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/school-announcement-setting')
                ->group(base_path('routes/Annnouncement/SchoolAnnouncementSetting.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/announcement')
             ->group(base_path('routes/Annnouncement/Announcement.php'));

             Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/event-category')
              ->group(base_path('routes/Event/SchoolEventCategory.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/event-tag')
              ->group(base_path('routes/Event/SchoolEventTag.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/event-setting')
             ->group(base_path('routes/Event/EventSetting.php'));

            Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/school-event-setting')
            ->group(base_path('routes/Event/SchoolEventSetting.php'));

            Route::middleware(['auth:sanctum'])->prefix('api/v1/fee-installment')
            ->group(base_path('routes/FeeInstallment/FeeInstallment.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthException $e, Request $request){
               if ($request->expectsJson()) {
                $structuredError = [
                    'title' => $e->getTitle(),
                    'description' => $e->getDescription(),
                    'path' => null,
                ];
                return ApiResponseService::error($e->getMessage(), null, $e->getCode(), $structuredError);
            }
        });
      $exceptions->render(function (AppException $e, Request $request) {
           if ($request->expectsJson()) {
                $structuredError = [
                    'title' => $e->getTitle(),
                    'description' => $e->getDescription(),
                    'path' => $e->getPath(),
                ];
                return ApiResponseService::error($e->getMessage(), null, $e->getCode(), $structuredError);
            }
      });
    })->create();
