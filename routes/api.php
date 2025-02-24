<?php

use App\Http\Controllers\Auth\Edumanage\CreateAppAdminController;
use App\Http\Controllers\Auth\Edumanage\LoginAppAdminController;
use App\Http\Controllers\Auth\Edumanage\LogoutAppAdminController;
use App\Http\Controllers\Auth\Parent\ChangePasswordController;
use App\Http\Controllers\Auth\Parent\CreateParentController;
use App\Http\Controllers\Auth\Parent\GetAuthParentController;
use App\Http\Controllers\Auth\Parent\LoginController;
use App\Http\Controllers\Auth\Parent\LogoutController;
use App\Http\Controllers\Auth\Parent\PasswordResetController as ParentResetPasswordController;
use App\Http\Controllers\Auth\SchoolAdmin\CreatesSchoolAdminController;
use App\Http\Controllers\Auth\SchoolAdmin\GetAuthSchoolAdminController;
use App\Http\Controllers\Auth\SchoolAdmin\LoginSchoolAdminController;
use App\Http\Controllers\Auth\SchoolAdmin\LogoutSchoolAdminController;
use App\Http\Controllers\Auth\Student\ChangePasswordController as StudentChangePasswordController;
use App\Http\Controllers\Auth\Student\CreateStudentController;
use App\Http\Controllers\Auth\Student\GetAuthStudentController;
use App\Http\Controllers\Auth\Student\LoginStudentController;
use App\Http\Controllers\Auth\Student\LogoutStudentController;
use App\Http\Controllers\Auth\Student\ResetPasswordController;
use App\Http\Controllers\Auth\Teacher\ChangePasswordController as TeacherChangePasswordController;
use App\Http\Controllers\Auth\Teacher\CreateTeacherController;
use App\Http\Controllers\Auth\Teacher\GetAuthTeacherController;
use App\Http\Controllers\Auth\Teacher\LoginTeacherController;
use App\Http\Controllers\Auth\Teacher\LogoutTeacherController;
use App\Http\Controllers\Auth\Teacher\ResetPasswordController as TeacherResetPasswordController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EducationLevelsController;
use App\Http\Controllers\EdumanageAdminController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\ExamsController;
use App\Http\Controllers\ExamTimeTableController;
use App\Http\Controllers\ExamTypecontroller;
use App\Http\Controllers\GradesController;
use App\Http\Controllers\InstructorAvailabilityController;
use App\Http\Controllers\LetterGradecontroller;
use App\Http\Controllers\MarksController;
use App\Http\Controllers\ParentsController;
use App\Http\Controllers\ReportCardGenerationcontroller;
use App\Http\Controllers\SchoolAdminController;
use App\Http\Controllers\SchoolBranchesController;
use App\Http\Controllers\ExpensesCategorycontroller;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\SchoolsController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\SchoolSemesterController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\StudentBatchcontroller;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentPerformanceReportController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TimeTableController;
use App\Http\Controllers\FeePaymentController;
use App\Http\Controllers\Auth\Edumanage\ChangeAppAdminPassword;
use App\Http\Controllers\Auth\Edumanage\GetAuthAppAdminController;
use App\Http\Controllers\Auth\SchoolAdmin\validateOtpController;
use App\Http\Controllers\StudentPromotionController;
use App\Http\Controllers\StudentResitController;
use App\Http\Controllers\ResitTimeTableController;
use App\Http\Controllers\TeacherSpecailtyPreferenceController;
use App\Http\Controllers\SchoolSubscriptionController;
use App\Http\Controllers\RatesCardController;
use App\Http\Controllers\SubscriptionPaymentController;
use App\Http\Controllers\ElectionsController;
use App\Http\Controllers\ElectionApplicationController;
use App\Http\Controllers\ElectionRolesController;
use App\Http\Controllers\ElectionResultsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReportAnalytics\FinancialreportController;
use App\Http\Controllers\StudentAdditionalFeesController;
use App\Http\Controllers\FeePaymentScheduleController;
use App\Http\Controllers\AdditionalFeeCategoryController;
use App\Http\Controllers\FeeWaiverController;
use App\Http\Controllers\HodController;
use App\Http\Controllers\HosController;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\Limitstudents;
use App\Http\Middleware\LimitSchoolAdmin;
use App\Http\Middleware\LimitParents;
use App\Http\Middleware\LimitTeachers;
use App\Http\Controllers\Auth\Edumanage\PasswordResetController as AppAdminPasswordResetController;
use App\Http\Controllers\Auth\Edumanage\ValidateOtpController as VaidateAppAdminOtpController;
use App\Http\Controllers\Auth\SchoolAdmin\PasswordResetController as ResetSchoolAdminPasswordController;
use App\Http\Controllers\Auth\Student\ResetPasswordController as ResetStudentPasswordController;
use App\Http\Controllers\Auth\Teacher\ResetPasswordController as ResetTeacherPasswordController;
use App\Http\Controllers\Auth\Parent\PasswordResetController as ResetParentPasswordController;
use App\Http\Controllers\Auth\Parent\ValidateOtpController as ParentValidateOtpController;
use App\Http\Controllers\Auth\Teacher\ValidateOtpController as TeacherValidateOtpController;
use App\Http\Controllers\Auth\Student\ValidateOtpController as StudentValidateOtpController;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;



Route::prefix('api/v1/app-admin')->group(function () {
    Route::post('/create-admin', [CreateAppAdminController::class, 'create_edumanage_admin']);
    Route::post('/loginAppAdmin', [LoginAppAdminController::class, 'login_edumanage_admin']);
    Route::middleware('auth:sanctum')->post('/logout-admin', [LogoutAppAdminController::class, 'logout_eduadmin']);
    Route::get('/get-all-admins', [EdumanageAdminController::class, 'get_all_eduamage_admins']);
    Route::middleware('auth:sanctum')->post('/change-password', [ChangeAppAdminPassword::class, 'change_edumanageadmin_password']);
    Route::delete('/delete-admin/{edumanage_admin_id}', [EdumanageAdminController::class, 'delete_edumanage_admin']);
    Route::middleware('auth:sanctum')->get('/auth-edumanage-admin', [GetAuthAppAdminController::class, 'get_authenticated_eduamanageadmin']);
    Route::put('/update-admin/{edumanage_admin_id}', [EdumanageAdminController::class, 'update_edumanage_admin']);
    Route::post('/resetPassword', [AppAdminPasswordResetController::class, 'reset_password']);
    Route::post('/validatePasswordResetOtp', [AppAdminPasswordResetController::class, 'verify_otp']);
    Route::post('/updatePassword', [AppAdminPasswordResetController::class, 'ChangeAppAdminPasswordUnAuthenticated']);
    Route::post('/validateLoginOtp', [VaidateAppAdminOtpController::class, 'verify_otp']);
    Route::post('/requestNewOtp', [VaidateAppAdminOtpController::class, 'request_another_code']);
});

Route::prefix('api/v1/parent')->group(function () {
    Route::post('/login', [LoginController::class, 'login_parent']);
    Route::middleware('auth:sanctum')->post('/change-password', [ChangePasswordController::class, 'change_parent_password']);
    Route::middleware('auth:sanctum')->post('/logout', [LogoutController::class, 'logout_parent']);
    Route::middleware('auth:sanctum')->post('/auth-parent', [GetAuthParentController::class, 'get_authenticated_parent']);
    Route::middleware([IdentifyTenant::class, LimitParents::class,  'auth:sanctum'])->post('/create-parent', [CreateParentController::class, 'create_parent']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->delete('/delete-parent/{parent_id}', [ParentsController::class, 'delete_parent_with_scope']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->put('/update-parent/{parent_id}', [ParentsController::class, 'update_parent_with_scope']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/get-parents-no-relations', [ParentsController::class, 'get_all_parents_within_a_School_without_relations']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/get-parents-with-relations', [ParentsController::class, 'get_all_parents_with_relations_without_scope']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/parent-details/{parent_id}', [ParentsController::class, 'get_parent_details']);
    Route::post('/resetPassword', [ResetParentPasswordController::class, 'reset_password']);
    Route::post('/validatePasswordResetOtp', [ResetParentPasswordController::class, 'verify_otp']);
    Route::post('/updatePassword', [ResetParentPasswordController::class, 'ChangeParentPasswordUnAuthenticated']);
    Route::post('/validateLoginOtp', [ParentValidateOtpController::class, 'verify_otp']);
    Route::post('/requestNewOtp', [ParentValidateOtpController::class, 'request_another_code']);
});

Route::prefix('api/v1/student')->group(function () {
    Route::post('/login', [LoginStudentController::class, 'login_student']);
    Route::middleware('auth:sanctum')->post('/logout', [LogoutStudentController::class, 'logout_parent']);
    Route::middleware('auth:sanctum')->post('/change-password', [StudentChangePasswordController::class, 'change_student_password']);
    Route::middleware('auth:sanctum')->post('/auth-student', [GetAuthStudentController::class, 'get_authenticated_student']);
    Route::middleware([IdentifyTenant::class, Limitstudents::class, 'auth:sanctum'])->post('/create-student', [CreateStudentController::class, 'create_student']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/generate-report-card/{student_id}/{level_id}/{exam_id}', [ReportCardGenerationcontroller::class, 'generate_student_report_card']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum', 'role:schoolSuperAdmin', 'permission:view student'])->get('/get-students', [StudentController::class, 'get_all_students_in_school']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/student-details/{student_id}', [StudentController::class, 'student_details']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->put('/update-student/{student_id}', [StudentController::class, 'update_student_scoped']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->delete('/delete-student/{student_id}', [StudentController::class, 'delete_Student_Scoped']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post('/promote-student', [studentpromotionController::class, 'promote_student_to_another_class']);
    Route::post('/resetPassword', [ResetStudentPasswordController::class, 'reset_password']);
    Route::post('/validatePasswordResetOtp', [ResetStudentPasswordController::class, 'verify_otp']);
    Route::post('/updatePassword', [ResetStudentPasswordController::class, 'ChangeAppAdminPasswordUnAuthenticated']);
    Route::post('/validateLoginOtp', [StudentValidateOtpController::class, 'verify_otp']);
    Route::post('/requestNewOtp', [StudentValidateOtpController::class, 'request_another_code']);
});

Route::prefix('api/v1/school-admin')->group(function () {
    Route::post('/login', [LoginSchoolAdminController::class, 'login_school_admin']);
    Route::post('/verify-otp', [validateOtpController::class, 'verify_otp']);
    Route::post("/request-otp", [validateOtpController::class, "request_another_code"]);
    Route::post('/register/super-admin', [SchoolAdminController::class, 'create_School_admin_on_sign_up']);
    Route::middleware('auth:sanctum')->post('/logout', [LogoutSchoolAdminController::class, 'logout_school_admin']);
    Route::middleware('auth:sanctum')->get('/auth-school-admin', [GetAuthSchoolAdminController::class, 'get_authenticated_school_admin']);
    Route::middleware([IdentifyTenant::class, LimitSchoolAdmin::class, 'auth:sanctum',])->post('/create-school-admin', [CreatesSchoolAdminController::class, 'create_school_admin']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->put('/update-school-admin/{school_admin_id}', [SchoolAdminController::class, 'update_school_admin']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->delete('/delete-school-admin/{school_admin_id}', [SchoolAdminController::class, 'delete_school_admin']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->get('/get-all-school-admins', [SchoolAdminController::class, 'get_all_school_admins_scoped']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/school-admin/details/{school_admin_id}', [SchoolAdminController::class, 'school_admin_details']);
    Route::post('/resetPassword', [ResetSchoolAdminPasswordController::class, 'reset_password']);
    Route::post('/validatePasswordResetOtp', [ResetSchoolAdminPasswordController::class, 'verify_otp']);
    Route::post('/updatePassword', [ResetSchoolAdminPasswordController::class, 'ChangeShoolAdminPasswordUnAuthenticated']);
});

Route::prefix('api/v1/teacher')->group(function () {
    Route::post('/login', [LoginTeacherController::class, 'login_teacher']);
    Route::middleware('auth:sanctum')->post('/change-password', [TeacherChangePasswordController::class, 'change_teacher_password']);
    Route::middleware('auth:sanctum')->post('/logout', [LogoutTeacherController::class, 'logout_teacher']);
    Route::middleware('auth:sanctum')->get('/auth-teacher', [GetAuthTeacherController::class, 'get_authenticated_teacher']);
    Route::middleware([IdentifyTenant::class, LimitTeachers::class,  'auth:sanctum',])->post('/create-teacher', [createTeacherController::class, 'create_teacher']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->delete('/delete-teacher/{teacher_id}', [TeacherController::class, 'delete_teacher_scoped']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->put('/update-teacher/{teacher_id}', [TeacherController::class, 'update_teacher_data_scoped']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->get('/get-all-teachers', [TeacherController::class, 'get_all_teachers_not_scoped']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->get('/teacher-details/{teacher_id}', [TeacherController::class, 'get_teacher_details']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->get('/get-teachers-with-relations', [TeacherController::class, 'get_all_teachers_with_relations_scoped']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->get('/get-teacher-timetable/{teacher_id}', [TeacherController::class, 'get_my_timetable']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post('/add-specailty-preference/{teacherId}', [TeacherController::class, 'assignTeacherSpecailtyPreference']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get("/teacher-specailty-preference/{teacherId}", [TeacherSpecailtyPreferenceController::class, 'getTeacherSpecailtyPreference']);
    Route::post('/resetPassword', [ResetTeacherPasswordController::class, 'reset_password']);
    Route::post('/validatePasswordResetOtp', [ResetTeacherPasswordController::class, 'verify_otp']);
    Route::post('/updatePassword', [ResetTeacherPasswordController::class, 'ChangeTeacherPasswordUnAuthenticated']);
    Route::post('/validateLoginOtp', [TeacherValidateOtpController::class, 'verify_otp']);
    Route::post('/requestNewOtp', [TeacherValidateOtpController::class, 'request_another_code']);
});

Route::prefix('api/v1/permissions')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-permission', [PermissionController::class, 'createPermission']);
    Route::middleware(['auth:sanctum'])->get("/get-permissions", [PermissionController::class, "getPermission"]);
    Route::middleware(['auth:sanctum'])->delete("/delete-permission/{permissionId}", [PermissionController::class, 'deletePermission']);
    Route::middleware(['auth:sanctum'])->put('/update-permission/{permissionId}', [PermissionController::class, "updatePermission"]);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->get('/get-schooladmin/permissions/{schoolAdminId}', [PermissionController::class, "getSchoolAdminPermissions"]);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->post('/grant-schoolAdmin-permissions/{schoolAdminId}', [PermissionController::class, 'givePermissionToSchoolAdmin']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->post("/revoke-schoolAdmin-permissions/{schoolAdminId}", [PermissionController::class, 'revokePermission']);
});

Route::prefix('api/v1/roles')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-role', [RoleController::class, 'createRole']);
    Route::middleware(['auth:sanctum'])->get('/get-roles', [RoleController::class, 'getRoles']);
    Route::middleware(['auth:sanctum'])->delete('/delete-roles/{roleId}', [RoleController::class, 'updateRole']);
    Route::middleware(['auth:sanctum'])->put('/update-role/{roleId}', [RoleController::class, 'updateRole']);
    Route::middleware(['auth:sanctum'])->post('/assign-role/{schoolAdminId}', [RoleController::class, 'assignRoleSchoolAdmin']);
    Route::middleware(['auth:sanctum'])->post('/remove-role/{schoolAdminId}', [RoleController::class, 'removeRoleSchoolAdmin']);
});




Route::prefix('api/v1/school')->group(function () {
    Route::post('/register', [SchoolsController::class, 'register_school_to_edumanage']);
    Route::middleware(['auth:sanctum'])->put('/update_school', [SchoolsController::class, 'update_school']);
    Route::middleware(['auth:sanctum'])->delete('/delete-school/{school_id}', [SchoolsController::class, 'delete_school']);
});

Route::prefix('api/v1/school-branch')->group(function () {
    Route::post('/register', [SchoolBranchesController::class, 'create_school_branch']);
    Route::middleware(['auth:sanctum'])->delete('/delete-branch/{branch_id}', [SchoolBranchesController::class, 'delete_school_branch']);
    Route::middleware(['auth:sanctum'])->put('/update-branch/{branch_id}', [SchoolBranchesController::class, 'update_school_branch']);
    Route::middleware([IdentifyTenant::class,])->get('/my-school-branches', [SchoolBranchesController::class, 'get_all_school_branches_scoped']);
    Route::get('/school-branches', [SchoolBranchesController::class, 'get_all_schoool_branches']);
});

Route::prefix('api/v1/country')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-country', [CountryController::class, 'create_country']);
    Route::get('/countries', [CountryController::class, 'get_all_countries']);
    Route::middleware(['auth:sanctum'])->delete('/delete-country/{country_id}', [CountryController::class, 'delete_country']);
    Route::middleware(['auth:sanctum'])->put('/update-country/{country_id}', [CountryController::class, 'update_country']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/department')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-department', [DepartmentController::class, 'create_school_department']);
    Route::middleware(['auth:sanctum'])->get('/my-departments', [DepartmentController::class, 'get_all_school_department_with_school_branches']);
    Route::middleware(['auth:sanctum'])->get('/department-details/{department_id}', [DepartmentController::class, 'department_details']);
    Route::middleware(['auth:sanctum'])->put('/update-department', [DepartmentController::class, 'update_school_department']);
    Route::middleware(['auth:sanctum'])->delete('/delete-department/{department_id}', [DepartmentController::class, 'delete_school_department']);
    Route::middleware(['auth:sanctum'])->get('/get-hods', [HodController::class, 'getHods']);
    Route::middleware(['auth:sanctum'])->delete("/delete-hod/{hodId}", [HodController::class, 'removeHod']);
    Route::middleware(['auth:sanctum'])->post('/assign-hod', [HodController::class, 'assignHeadOfDepartment']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/course')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-course', [CoursesController::class, 'create_course']);
    Route::middleware(['auth:sanctum'])->delete('/delete-course/{course_id}', [CoursesController::class, 'delete_course']);
    Route::middleware(['auth:sanctum'])->put('/update-course/{course_id}', [CoursesController::class, 'update_course']);
    Route::middleware(['auth:sanctum'])->get('/my-courses', [CoursesController::class, 'get_all_courses_with_no_relation']);
    Route::middleware(['auth:sanctum'])->get('/course-details/{course_id}', [CoursesController::class, 'courses_details']);
    Route::middleware(['auth:sanctum'])->get('/my-courses/{specialty_id}/{semester_id}', [CoursesController::class, 'get_specialty_level_semester_courses']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/specialty')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-specialty', [SpecialtyController::class, 'create_school_speciality']);
    Route::middleware(['auth:sanctum'])->delete('/delete-specialty/{specialty_id}', [SpecialtyController::class, 'delete_school_specialty']);
    Route::middleware(['auth:sanctum'])->put('/update-specialty/{specialty_id}', [SpecialtyController::class, 'update_school_specialty']);
    Route::middleware(['auth:sanctum'])->get('/my-specialties', [SpecialtyController::class, 'get_all_tenant_School_specailty_scoped']);
    Route::middleware(['auth:sanctum'])->get('/specialty-details/{specialty_id}', [SpecialtyController::class, 'specialty_details']);
    Route::middleware(['auth:sanctum'])->post("/assign-hos", [HosController::class, 'assignHeadOfSpecialty']);
    Route::middleware(['auth:sanctum'])->get('/get-assigned-hos', [HosController::class, 'getHeadOfSpecialty']);
    Route::middleware(['auth:sanctum'])->delete("/remove-hos", [HosController::class, 'removeHeadOfSpecialty']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/marks')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/add-student-mark', [MarksController::class, 'add_student_mark']);
    Route::middleware(['auth:sanctum'])->put('/update-student-mark/{mark_id}', [MarksController::class, 'update_student_mark_scoped']);
    Route::middleware(['auth:sanctum'])->delete('/delete-student-mark/{mark_id}', [MarksController::class, 'delete_mark_of_student_scoped']);
    Route::middleware(['auth:sanctum'])->get('/scores-exam/{student_id}/{exam_id}', [MarksController::class, 'get_all_student_marks']);
    Route::middleware(['auth:sanctum'])->get("/scores-exam/student", [MarksController::class, 'get_all_student_scores']);
    Route::middleware(['auth:sanctum'])->get("/score-details/{mark_id}", [MarksController::class, 'get_exam_score_details']);
    Route::middleware(['auth:sanctum'])->get("/accessed-courses/{exam_id}/{student_id}", [MarksController::class, "get_exam_score_associated_data"]);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/teacher-avialability')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-availability', [InstructorAvailabilityController::class, 'create_availability']);
    Route::middleware(['auth:sanctum'])->delete('/delete-availability/{availabilty_id}', [InstructorAvailabilityController::class, 'delete_scoped_teacher_availability']);
    Route::middleware(['auth:sanctum'])->put('/update-availability/{availability_id}', [InstructorAvailabilityController::class, 'update_teacher_avialability']);
    Route::middleware(['auth:sanctum'])->get('/teacher-avialability/{availability_id}', [InstructorAvailabilityController::class, 'getteacherAvialability']);
});

Route::prefix('api/v1/levels')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-level', [EducationLevelsController::class, 'create_education_levels']);
    Route::middleware(['auth:sanctum'])->put('/update-level/{level_id}', [EducationLevelsController::class, 'update_education_levels']);
    Route::middleware(['auth:sanctum'])->delete('/delete-level/{level_id}', [EducationLevelsController::class, 'delete_education_levels']);
    Route::middleware(['auth:sanctum'])->get('/education-levels', [EducationLevelsController::class, 'get_all_education_leves']);
});


Route::middleware([IdentifyTenant::class])->prefix('api/v1/grades')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-grade', [GradesController::class, 'makeGradeForExamScoped']);
    Route::middleware(['auth:sanctum'])->get('/grades-for-exams', [GradesController::class, 'get_all_grades_scoped']);
    Route::middleware(['auth:sanctum'])->put('/update-grade/{grade_id}', [GradesController::class, 'update_grades_scoped']);
    Route::middleware(['auth:sanctum'])->delete('/delete-grade/{grade_id}', [GradesController::class, 'delete_grades_scoped']);
});


Route::middleware([IdentifyTenant::class])->prefix('api/v1/exams')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-exam', [ExamsController::class, 'create_exam_scoped']);
    Route::middleware(['auth:sanctum'])->put('/update-exam/{exam_id}', [ExamsController::class, 'update_exam_scoped']);
    Route::middleware(['auth:sanctum'])->get('/getexams', [ExamsController::class, 'get_all_exams']);
    Route::middleware(['auth:sanctum'])->get('/exam-details/{exam_id}', [ExamsController::class, 'get_exam_details']);
    Route::middleware(['auth:sanctum'])->delete('/delete-exams/{exam_id}', [ExamsController::class, 'delete_school_exam']);
    Route::middleware(['auth:sanctum'])->get('/letter-grades/{exam_id}', [ExamsController::class, 'associateWeightedMarkWithLetterGrades']);
    Route::middleware(['auth:sanctum'])->get("/accessed_exams/{student_id}", [ExamsController::class, "get_accessed_exams"]);
});


Route::middleware([IdentifyTenant::class])->prefix('api/v1/exam-timetable')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-timetable', [ExamTimeTableController::class, 'create_exam_timetable']);
    Route::middleware(['auth:sanctum'])->put('/update-exam-time-table/{examtimetable_id}', [ExamTimeTableController::class, 'update_exam_time_table_scoped']);
    Route::middleware(['auth:sanctum'])->get('/generate-timetable/{level_id}/{specialty_id}', [ExamTimeTableController::class, 'generate_time_table_for_specialty']);
    Route::middleware(['auth:sanctum'])->delete('/delete/exam-time-table/{examtimetable_id}', [ExamTimeTableController::class, 'delete_exam_time_table_scoped']);
    Route::middleware(['auth:sanctum'])->get('/course-data/{exam_id}', [ExamTimeTableController::class, 'prepareExamTimeTableData']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/time-table')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-timetable', [TimeTableController::class, 'create_time_slots_scoped']);
    Route::middleware(['auth:sanctum'])->put('/update-timetable/{timetable_id}', [TimeTableController::class, 'update_time_table_record_scoped']);
    Route::middleware(['auth:sanctum'])->delete('/delete-timetable/{timetable_id}', [TimeTableController::class, 'delete_timetable_scoped']);
    Route::middleware(['auth:sanctum'])->get('/generate-timetable', [TimeTableController::class, 'generate_time_table_scoped']);
    Route::middleware(['auth:sanctum'])->get('/timetable-details/{entry_id}', [TimeTableController::class, 'get_timetable_details']);
    Route::middleware(['auth:sanctum'])->get('/instructor-availability/{semester_id}/{specialty_id}', [TimetableController::class, 'get_instructor_availability']);
});

Route::prefix('api/v1/semester')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-semester', [SemesterController::class, 'create_semester']);
    Route::middleware(['auth:sanctum'])->delete('/delete-semester/{semester_id}', [SemesterController::class, 'delete_semester']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->get('/semesters', [SemesterController::class, 'get_all_semesters']);
    Route::middleware(['auth:sanctum'])->put('/update-semester/{semester_id}', [SemesterController::class, 'update_semester']);
});

Route::prefix('api/v1/school-semesters')->group(function () {
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->post('/create-school-semester', [SchoolSemesterController::class, 'createSchoolSemester']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->put("/update-school-semester/{schoolSemesterId}", [SchoolSemesterController::class, 'updateSchoolSemester']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->get("/school-semeters", [SchoolSemesterController::class, 'getSchoolSemester']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->get("/delete-school-semeter/{schoolSemesterId}", [SchoolSemesterController::class, 'deleteSchoolSemester']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/event')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-event', [EventsController::class, 'create_school_evenT']);
    Route::middleware(['auth:sanctum'])->put('/update-event/{event_id}', [EventsController::class, 'update_school_event']);
    Route::middleware(['auth:sanctum'])->delete('/delete-event/{event_id}', [EventsController::class, 'delete_school_event']);
    Route::middleware(['auth:sanctum'])->get('/school-events', [EventsController::class, 'get_all_events']);
    Route::middleware(['auth:sanctum'])->get("/school-event/details/{event_id}", [EventsController::class, "event_details"]);
});




Route::prefix('api/v1/exam-type')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-exam-type', [ExamTypecontroller::class, 'create_exam_type']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->get('/exam_types', [ExamTypecontroller::class, 'get_all_exam_type']);
    Route::middleware(['auth:sanctum'])->delete('/exam-type/{exam_id}', [ExamTypecontroller::class, 'delete_exam_type']);
    Route::middleware(['auth:sanctum'])->put('/update-exam-type/{exam_id}', [ExamTypecontroller::class, 'update_exam_type']);
});

Route::prefix('api/v1/letter-grade')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-letter-grade', [LetterGradecontroller::class, 'create_letter_grade']);
    Route::middleware(['auth:sanctum'])->get('/get-letter-grades', [LetterGradecontroller::class, 'get_all_letter_grades']);
    Route::middleware(['auth:sanctum'])->delete('/delete-letter-grade/{letter_grade_id}', [LetterGradecontroller::class, 'delete_letter_grade']);
    Route::middleware(['auth:sanctum'])->put('/update-letter-grate/{letter_grade_id}', [LetterGradecontroller::class, 'update_letter_grade']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/grades-analytics')->group(function () {
    Route::middleware(['auth:sanctum'])->get('/get-risky-subjects', [StudentPerformanceReportController::class, 'high_risk_course_tracking']);
    Route::middleware(['auth:sanctum'])->post('/calculate-desired-gpa', [StudentPerformanceReportController::class, 'calculate_desired_gpa']);
});

Route::prefix('api/v1/subcription')->group(function () {
    Route::post('/subscribe', [SchoolSubscriptionController::class, 'subscribe']);
    Route::get('/subscribed-schools', [SchoolSubscriptionController::class, 'get_all_subscribed_schools']);
    Route::get('/subscription-details/{subscription_id}', [SchoolSubscriptionController::class, 'subcription_details']);
    Route::post('/create-rate', [RatesCardController::class, 'create_rates']);
    Route::put('/update-rate', [RatesCardController::class, 'update_rates_card']);
    Route::delete('/delete-rate/{rate_id}', [RatesCardController::class, 'delete_rate']);
    Route::get('/rates', [RatesCardController::class, 'get_rates']);
    Route::delete('/delete-transaction', [SubscriptionPaymentController::class, 'delete_payment']);
    Route::get('/my-transactions/{school_id}', [SubscriptionPaymentController::class, 'my_transactions']);
    Route::get('/payment-transactions/{school_id}', [SubscriptionPaymentController::class, 'get_all_transactions']);
});


Route::middleware([IdentifyTenant::class])->prefix('api/v1/school-expenses')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-expenses', [ExpensesController::class, 'add_new_expense']);
    Route::middleware(['auth:sanctum'])->delete('/delete-expenses/{expense_id}', [ExpensesController::class, 'delete_expense']);
    Route::middleware(['auth:sanctum'])->get('/my-expenses', [ExpensesController::class, 'get_all_expenses']);
    Route::middleware(['auth:sanctum'])->get('/expenses-details/{expense_id}', [ExpensesController::class, 'expenses_details']);
    Route::middleware(['auth:sanctum'])->put('/update-expenses/{expense_id}', [ExpensesController::class, 'update_expense']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/school-expenses-category')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-category', [ExpensesCategorycontroller::class, 'create_category_expenses']);
    Route::middleware(['auth:sanctum'])->delete('/delete-category/{category_expense_id}', [ExpensesCategorycontroller::class, 'delete_category_expense']);
    Route::middleware(['auth:sanctum'])->get('/get-category-expenses', [ExpensesCategorycontroller::class, 'get_all_category_expenses']);
    Route::middleware(['auth:sanctum'])->put('/update-category/{category_expense_id}', [ExpensesCategorycontroller::class, 'update_category_expenses']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/student-batches')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-batch', [StudentBatchcontroller::class, 'create_student_batch']);
    Route::middleware(['auth:sanctum'])->get('/student-batches', [StudentBatchcontroller::class, 'get_all_student_batches']);
    Route::middleware(['auth:sanctum'])->delete('/delete-batch/{batch_id}', [StudentBatchcontroller::class, 'delete_student_batch']);
    Route::middleware(['auth:sanctum'])->put('/update-batch/{batch_id}', [StudentBatchcontroller::class, 'update_student_batch']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/fee-payment')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/pay-fees', [FeePaymentController::class, 'pay_school_fees']);
    Route::middleware(['auth:sanctum'])->get('/paid-fees', [FeePaymentController::class, 'get_all_fees_paid']);
    Route::middleware(['auth:sanctum'])->put('/update-payment/{fee_id}', [FeePaymentController::class, 'update_student_fee_payment']);
    Route::middleware(['auth:sanctum'])->delete('/delete-payment-record/{fee_id}', [FeePaymentController::class, 'delete_fee_payment_record']);
    Route::middleware(['auth:sanctum'])->get('/indebted-students', [FeePaymentController::class, 'get_all_student_deptors']);
    Route::middleware(['aut:sanctum'])->post('/payRegistrationFee', [FeePaymentController::class, 'payRegistrationFees']);
    Route::middleware(['auth:sanctum'])->get("/getTransactions", [FeePaymentController::class, 'getTuitionFeeTransactions']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/student-resit')->group(function () {
    Route::middleware(['auth:sanctum'])->get('/get-student-resits/{student_id}', [StudentResitController::class, 'get_my_resits']);
    Route::middleware(['auth:sanctum'])->put('/pay-for-resit/{resit_id}', [StudentResitController::class, 'pay_for_resit']);
    Route::middleware(['auth:sanctum'])->put('/update-resit-status/{resit_id}', [StudentResitController::class, 'update_exam_status']);
    Route::middleware(['auth:sanctum'])->put('/update-resit/{resit_id}', [StudentResitController::class, 'update_student_resit']);
    Route::middleware(['auth:sanctum'])->delete('/delete-resit/{resit_id}', [StudentResitController::class, 'delete_student_resit_record']);
    Route::middleware(['auth:sanctum'])->post('/resit-timetable', [StudentResitController::class, 'create_resit_timetable_entry']);
    Route::middleware(['auth:sanctum'])->get('/student_resits', [StudentResitController::class, 'get_student_resits']);
    Route::middleware(['auth:sanctum'])->get('/get-specialty-resit/{specialty_id}/{exam_id}', [ResitTimeTableController::class, 'get_resits_for_specialty']);
    Route::middleware(['auth:sanctum'])->get('/generate-resit-timetable/{exam_id}', [ResitTimeTableController::class, '']);
    Route::middleware(['auth:sanctum'])->get("/details/{resit_id}", [StudentResitController::class, 'student_resit_details']);
    Route::middleware(['auth:sanctum'])->get("/getTransactions", [StudentController::class, 'getResitPaymentTransactions']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/elections')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-election', [ElectionsController::class, 'createElection']);
    Route::middleware(['auth:sanctum'])->get('/get-elections', [ElectionsController::class, 'getElections']);
    Route::middleware(['auth:sanctum'])->delete('/delete-election/{election_id}', [ElectionsController::class, 'deleteElection']);
    Route::middleware(['auth:sanctum'])->get('/update-election/{election_id}', [ElectionsController::class, 'updateElection']);
    Route::middleware(['auth:sanctum'])->post('/cast-vote', [ElectionsController::class, 'vote']);
    Route::middleware(['auth:sanctum'])->get('/election-results/{election_id}', [ElectionResultsController::class, 'fetchElectionResults']);
    Route::middleware(['auth:sanctum'])->get('/election-candidates/{electionId}', [ElectionsController::class, 'getElectionCandidates']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/election-application')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/apply', [ElectionApplicationController::class, 'createElectionApplication']);
    Route::middleware(['auth:sanctum'])->get('/applications/{election_id}', [ElectionApplicationController::class, 'getApplications']);
    Route::middleware(['auth:sanctum'])->put('/update-application/{application_id}', [ElectionApplicationController::class, 'updateApplication']);
    Route::middleware(['auth:sanctum'])->delete('/delete/{application_id}', [ElectionApplicationController::class, 'deleteApplication']);
    Route::middleware(['auth:sanctum'])->put('/approve-application/{application_id}', [ElectionApplicationController::class, 'approveApplication']);
});

Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/election-roles')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-role', [ElectionRolesController::class, 'createElectionRole']);
    Route::middleware(['auth:sanctum'])->put('/update-election/{election_role_id}', [ElectionRolesController::class, 'updateElectionRole']);
    Route::middleware(['auth:sanctum'])->delete('/delete-role/{election_role_id}', [ElectionRolesController::class, 'deleteElectionRole']);
    Route::middleware(['auth:sanctum'])->get('/election-roles/{election_id}', [ElectionRolesController::class, 'getElectionRoles']);
});

Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/additional-fees')->group(function () {
    Route::post("/createFee", [StudentAdditionalFeesController::class, 'createStudentAdditionalFees']);
    Route::put('/updateFee/{feeId}', [StudentAdditionalFeesController::class, 'updateStudentAdditionalFees']);
    Route::delete('/deleteFee/{feeId}', [StudentAdditionalFeesController::class, 'deleteStudentAdditionalFees']);
    Route::get('/getbyStudent/{studentId}', [StudentAdditionalFeesController::class, 'getStudentAdditionalFees']);
    Route::get('/getAll', [StudentAdditionalFeesController::class, 'getAdditionalFees']);
    Route::post('/payFee', [StudentAdditionalFeesController::class, 'payAdditionalFees']);
    Route::post('/getTransactions', [StudentAdditionalFeesController::class, 'getAdditionalFeesTransactions']);
});

Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/feepayment-schedule')->group(function () {
    Route::post('/createSchedule', [FeePaymentScheduleController::class, 'createFeePaymentSchedule']);
    Route::put('/updateSchedule/{scheduleId}', [FeePaymentScheduleController::class, 'updateFeePaymentSchedule']);
    Route::get('/getAllSchedule', [FeePaymentScheduleController::class, 'getAllFeePaymentSchedule']);
    Route::get('/getBySpecialty/{specialtyId}', [FeePaymentScheduleController::class, 'getFeePaymentScheduleBySpecialty']);
    Route::delete('/deleteSpecialty/{scheduleId}', [FeePaymentScheduleController::class, 'deleteFeePaymentSchedule']);
});

Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/fee-waiver')->group(function () {
    Route::post('/createFeeWaiver', [FeeWaiverController::class, 'createFeeWaiver']);
    Route::put('/updateFeeWaiver/{feeWaiverId}', [FeeWaiverController::class, 'updateFeeWaiver']);
    Route::get('/getByStudent/{studentId}', [FeeWaiverController::class, 'getByStudent']);
    Route::delete('/deleteFeeWaiver/{feeWaiverId}', [FeeWaiverController::class, 'deleteFeeWaiver']);
    Route::get('/getAllFeeWaivers', [FeeWaiverController::class, 'getAllFeeWaiver']);
});

Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/additional-fee-category')->group(function () {
    Route::post('/createCategory', [AdditionalFeeCategoryController::class, 'createAddtionalFeeCategory']);
    Route::get('/getCategory', [AdditionalFeeCategoryController::class, 'getAdditionalFeeCategory']);
    Route::delete('/deletCategory/{feeCategoryId}', [AdditionalFeeCategoryController::class, 'deleteAdditionalFeeCategory']);
    Route::put('/updateCategory/{feeCategoryId}', [AdditionalFeeCategoryController::class, 'updateAdditionalFeeCategory']);
});


Route::middleware([IdentifyTenant::class])->prefix('api/v1/stats')->group(function () {
    Route::middleware(['auth:sanctum'])->get("/get/financial-stats", [FinancialreportController::class, 'getFinanacialStats']);
});
