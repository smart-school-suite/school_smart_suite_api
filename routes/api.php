<?php

use App\Http\Controllers\Auth\Edumanage\CreateAppAdminController;
use App\Http\Controllers\Auth\Edumanage\LoginAppAdminController;
use App\Http\Controllers\Auth\Edumanage\LogoutAppAdminController;
use App\Http\Controllers\Auth\Parent\ChangePasswordController;
use App\Http\Controllers\Auth\Parent\CreateParentController;
use App\Http\Controllers\Auth\Parent\GetAuthParentController;
use App\Http\Controllers\Auth\Parent\LoginController;
use App\Http\Controllers\Auth\Parent\LogoutController;
use App\Http\Controllers\Auth\SchoolAdmin\CreatesSchoolAdminController;
use App\Http\Controllers\Auth\SchoolAdmin\GetAuthSchoolAdminController;
use App\Http\Controllers\Auth\SchoolAdmin\LoginSchoolAdminController;
use App\Http\Controllers\Auth\SchoolAdmin\LogoutSchoolAdminController;
use App\Http\Controllers\Auth\Student\ChangePasswordController as StudentChangePasswordController;
use App\Http\Controllers\Auth\Student\CreateStudentController;
use App\Http\Controllers\Auth\Student\GetAuthStudentController;
use App\Http\Controllers\Auth\Student\LoginStudentController;
use App\Http\Controllers\Auth\Student\LogoutStudentController;
use App\Http\Controllers\Auth\Teacher\ChangePasswordController as TeacherChangePasswordController;
use App\Http\Controllers\Auth\Teacher\CreateTeacherController;
use App\Http\Controllers\Auth\Teacher\GetAuthTeacherController;
use App\Http\Controllers\Auth\Teacher\LoginTeacherController;
use App\Http\Controllers\Auth\Teacher\LogoutTeacherController;
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
use App\Http\Controllers\GradesCategoryController;
use App\Http\Controllers\SchoolGradeConfigController;
use App\Http\Controllers\Auth\Edumanage\PasswordResetController as AppAdminPasswordResetController;
use App\Http\Controllers\Auth\Edumanage\ValidateOtpController as VaidateAppAdminOtpController;
use App\Http\Controllers\Auth\SchoolAdmin\PasswordResetController as ResetSchoolAdminPasswordController;
use App\Http\Controllers\Auth\Student\ResetPasswordController as ResetStudentPasswordController;
use App\Http\Controllers\Auth\Teacher\ResetPasswordController as ResetTeacherPasswordController;
use App\Http\Controllers\Auth\Parent\PasswordResetController as ResetParentPasswordController;
use App\Http\Controllers\Auth\Parent\ValidateOtpController as ParentValidateOtpController;
use App\Http\Controllers\Auth\Teacher\ValidateOtpController as TeacherValidateOtpController;
use App\Http\Controllers\Auth\Student\ValidateOtpController as StudentValidateOtpController;
use App\Http\Controllers\Auth\SchoolAdmin\ChangePasswordController as ChangeSchoolAdminPasswordController;
use App\Http\Controllers\AccessedStudentController;
use App\Http\Controllers\StudentResultController;
use App\Http\Controllers\AccessedResitStudentController;
use App\Http\Controllers\Stats\FinancialStatsController;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;



Route::prefix('api/v1/app-admin')->group(function () {
    Route::post('/create-admin', [CreateAppAdminController::class, 'createAppAdmin']);
    Route::post('/loginAppAdmin', [LoginAppAdminController::class, 'loginAppAdmin']);
    Route::middleware('auth:sanctum')->post('/logout-admin', [LogoutAppAdminController::class, 'logoutAppAdmin']);
    Route::get('/get-all-admins', [EdumanageAdminController::class, 'getAppAdmins']);
    Route::middleware('auth:sanctum')->post('/change-password', [ChangeAppAdminPassword::class, 'cchangeAppAdminPassword']);
    Route::delete('/delete-admin/{edumanage_admin_id}', [EdumanageAdminController::class, 'deleteAppAdmin']);
    Route::middleware('auth:sanctum')->get('/auth-edumanage-admin', [GetAuthAppAdminController::class, 'getAuthAppAdmin']);
    Route::put('/update-admin/{edumanage_admin_id}', [EdumanageAdminController::class, 'updateAppAdmin']);
    Route::post('/resetPassword', [AppAdminPasswordResetController::class, 'resetAppAdminPassword']);
    Route::post('/validatePasswordResetOtp', [AppAdminPasswordResetController::class, 'verifyAppAdminOtp']);
    Route::post('/updatePassword', [AppAdminPasswordResetController::class, 'changeAppAdminPasswordUnAuthenticated']);
    Route::post('/validateLoginOtp', [VaidateAppAdminOtpController::class, 'verifyAppAdminOtp']);
    Route::post('/requestNewOtp', [VaidateAppAdminOtpController::class, 'requestNewotpCode']);
});

Route::prefix('api/v1/parent')->group(function () {
    Route::post('/login', [LoginController::class, 'loginParent']);
    Route::middleware('auth:sanctum')->post('/change-password', [ChangePasswordController::class, 'changeParentPassword']);
    Route::middleware('auth:sanctum')->post('/logout', [LogoutController::class, 'logoutParent']);
    Route::middleware('auth:sanctum')->post('/auth-parent', [GetAuthParentController::class, 'getAuthParent']);
    Route::middleware([IdentifyTenant::class, LimitParents::class,  'auth:sanctum'])->post('/create-parent', [CreateParentController::class, 'createParent']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->delete('/delete-parent/{parent_id}', [ParentsController::class, 'deleteParent']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->put('/update-parent/{parent_id}', [ParentsController::class, 'updateParent']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/get-parents', [ParentsController::class, 'getAllParents']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/parent-details/{parent_id}', [ParentsController::class, 'getParentDetails']);
    Route::post('/resetPassword', [ResetParentPasswordController::class, 'reset_password']);
    Route::post('/validatePasswordResetOtp', [ResetParentPasswordController::class, 'verifyParentOtp']);
    Route::post('/updatePassword', [ResetParentPasswordController::class, 'changeParentPasswordUnAuthenticated(']);
    Route::post('/validateLoginOtp', [ParentValidateOtpController::class, 'verifyParentOtp']);
    Route::post('/requestNewOtp', [ParentValidateOtpController::class, 'requestNewOtp']);
    Route::delete('/bulkDeleteParent/{parentIds}', [ParentsController::class, 'bulkDeleteParents']);
    Route::put('/bulkUpdateParent', [ParentsController::class, 'BulkUpdateParents']);
});

Route::prefix('api/v1/student')->group(function () {
    Route::post('/login', [LoginStudentController::class, 'loginStudent']);
    Route::middleware('auth:sanctum')->post('/logout', [LogoutStudentController::class, 'logoutStudent']);
    Route::middleware('auth:sanctum')->post('/change-password', [StudentChangePasswordController::class, 'changeStudentPassword']);
    Route::middleware('auth:sanctum')->post('/auth-student', [GetAuthStudentController::class, 'getAuthStudent']);
    Route::middleware([IdentifyTenant::class, Limitstudents::class, 'auth:sanctum'])->post('/create-student', [CreateStudentController::class, 'createStudent']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/generate-report-card/{student_id}/{level_id}/{exam_id}', [ReportCardGenerationcontroller::class, 'getStudentReportCard']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/get-students', [StudentController::class, 'getStudents']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/student-details/{student_id}', [StudentController::class, 'getStudentDetails']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->put('/update-student/{student_id}', [StudentController::class, 'updateStudent']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->delete('/delete-student/{student_id}', [StudentController::class, 'deleteStudent']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post('/promote-student', [studentpromotionController::class, 'promoteStudent']);
    Route::post('/resetPassword', [ResetStudentPasswordController::class, 'resetStudentPassword']);
    Route::post('/validatePasswordResetOtp', [ResetStudentPasswordController::class, 'verifyStudentOtp']);
    Route::post('/updatePassword', [ResetStudentPasswordController::class, 'changeStudentPasswordUnAuthenticated']);
    Route::post('/validateLoginOtp', [StudentValidateOtpController::class, 'verifyInstructorLoginOtp']);
    Route::post('/requestNewOtp', [StudentValidateOtpController::class, 'requestNewOtp']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post('/deactivateAccount/{studentId}', [StudentController::class, 'deactivateAccount']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post('/activateAccount/{studentId}', [StudentController::class, 'activateAccount']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->post('/markStudentAsDropout/{studentId}', [StudentController::class, 'markStudentAsDropout']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->get('/getAllStudentDropout', [StudentController::class, 'getStudentDropoutList']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->get('/getStudentDropoutDetails/{studentDropoutId}', [StudentController::class, 'getStudentDropoutDetails']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->delete('/deleteStudentDropout/{studentDropoutId}', [StudentController::class, 'deleteStudentDropout']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->post('/reinstateDropoutStudent/{studentDropoutId}', [StudentController::class, 'reinstateDropedOutStudent']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->delete("/bulkDeleteStudent/{studentIds}", [StudentController::class, 'bulkDeleteStudent']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->delete("/bulkDeleteStudentDropout/{dropOutIds}", [StudentController::class, 'bulkDeleteStudentDropout']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->post("/bulkActivateStudent/{studentIds}", [StudentController::class, 'bulkActivateStudent']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->post("/bulkDeActivateStudent/{studentIds}", [StudentController::class, 'bulkDeactivateStudent']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->post("/bulkMarkStudentAsDropout", [StudentController::class, 'bulkMarkStudentAsDropout']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->put("/bulkUpdateStudent", [StudentController::class, 'bulkUpdateStudent']);

});

Route::prefix('api/v1/school-admin')->group(function () {
    Route::post('/login', [LoginSchoolAdminController::class, 'loginShoolAdmin']);
    Route::post('/verify-otp', [validateOtpController::class, 'verifySchoolAdminOtp']);
    Route::post("/request-otp", [validateOtpController::class, "requestNewCode"]);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->post("/change-password", [ChangeSchoolAdminPasswordController::class, 'changeSchoolAdminPassword']);
    Route::post('/register/super-admin', [SchoolAdminController::class, 'createAdminOnSignup']);
    Route::middleware('auth:sanctum')->post('/logout', [LogoutSchoolAdminController::class, 'logoutSchoolAdmin']);
    Route::middleware('auth:sanctum')->get('/auth-school-admin', [GetAuthSchoolAdminController::class, 'getAuthSchoolAdmin']);
    Route::middleware([IdentifyTenant::class, LimitSchoolAdmin::class, 'auth:sanctum',])->post('/create-school-admin', [CreatesSchoolAdminController::class, 'createSchoolAdmin']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->put('/update-school-admin/{school_admin_id}', [SchoolAdminController::class, 'updateSchoolAdmin']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->delete('/delete-school-admin/{school_admin_id}', [SchoolAdminController::class, 'deleteSchoolAdmin']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum', 'permission:view-admin'])->get('/get-all-school-admins', [SchoolAdminController::class, 'getSchoolAdmin']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/school-admin/details/{school_admin_id}', [SchoolAdminController::class, 'getSchoolAdminDetails']);
    Route::post('/resetPassword', [ResetSchoolAdminPasswordController::class, 'resetSchoolAdminPassword']);
    Route::post('/validatePasswordResetOtp', [ResetSchoolAdminPasswordController::class, 'verifySchoolAdminOtp']);
    Route::post('/updatePassword', [ResetSchoolAdminPasswordController::class, 'changeShoolAdminPasswordUnAuthenticated']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post("/uploadProfilePic", [SchoolAdminController::class, 'uploadProfilePicture']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->delete("/deleteProfilePic", [SchoolAdminController::class, 'deleteProfilePicture']);
    Route::middleware('auth:sanctum')->post('/deactivateAccount/{schoolAdminId}', [SchoolAdminController::class, 'deactivateAccount']);
    Route::middleware('auth:sanctum')->post("/activateAccount/{schoolAdminId}", [SchoolAdminController::class, 'activateAccount']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->delete('/bulkDeleteSchoolAdmin/{schoolAdminIds}',  [SchoolAdminController::class, 'bulkDeleteSchoolAdmin']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->put('/bulkUpdateSchoolAdmin', [SchoolAdminController::class, 'bulkUpdateSchoolAdmin']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->post('/bulkDeactivateSchoolAdmin/{schoolAdminIds}', [SchoolAdminController::class, 'bulkDeactivateSchoolAdmin']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->post('/bulkActivateSchoolAdmin/{schoolAdminIds}', [SchoolAdminController::class, 'bulkActivateSchoolAdmin']);

});

Route::prefix('api/v1/teacher')->group(function () {
    Route::post('/login', [LoginTeacherController::class, 'loginInstructor']);
    Route::middleware('auth:sanctum')->post('/change-password', [TeacherChangePasswordController::class, 'changeInstructorPassword']);
    Route::middleware('auth:sanctum')->post('/logout', [LogoutTeacherController::class, 'logoutInstructor']);
    Route::middleware('auth:sanctum')->get('/auth-teacher', [GetAuthTeacherController::class, 'getAuthTeacher']);
    Route::middleware([IdentifyTenant::class, LimitTeachers::class,  'auth:sanctum',])->post('/create-teacher', [createTeacherController::class, 'createInstructor']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->delete('/delete-teacher/{teacher_id}', [TeacherController::class, 'deleteInstructor']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->put('/update-teacher/{teacher_id}', [TeacherController::class, 'updateInstructor']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->get('/teacher-details/{teacher_id}', [TeacherController::class, 'getInstructorDetails']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->get('/getallInstructors', [TeacherController::class, 'getInstructors']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->get('/get-teacher-timetable/{teacher_id}', [TeacherController::class, 'getTimettableByTeacher']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post('/add-specailty-preference/{teacherId}', [TeacherController::class, 'assignTeacherSpecailtyPreference']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get("/teacher-specailty-preference/{teacherId}", [TeacherSpecailtyPreferenceController::class, 'getTeacherSpecailtyPreference']);
    Route::post('/resetPassword', [ResetTeacherPasswordController::class, 'resetInstructorPassword']);
    Route::post('/validatePasswordResetOtp', [ResetTeacherPasswordController::class, 'resetInstructorPassword']);
    Route::post('/updatePassword', [ResetTeacherPasswordController::class, 'ChangeInstructorPasswordUnAuthenticated']);
    Route::post('/validateLoginOtp', [TeacherValidateOtpController::class, 'verifyInstructorLoginOtp']);
    Route::post('/requestNewOtp', [TeacherValidateOtpController::class, 'requestNewOtp']);
    Route::post("/deactivateAccount/{teacherId}", [TeacherController::class, 'deactivateTeacher']);
    Route::post("/activateAccount/{teacherId}", [TeacherController::class, 'activateTeacher']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->put('/bulkUpdateTeacher', [TeacherController::class, 'bulkUpdateTeacher']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post('/bulkActivateTeacher/{teacherIds}', [TeacherController::class, 'bulkActivateTeacher']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post('/bulkDeactivateTeacher/{teacherIds}', [TeacherController::class, 'bulkDeactivateTeacher']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->delete('/bulkDeleteTeacher/{teacherIds}', [TeacherController::class, 'bulkDeleteTeacher']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post('/bulkAddTeacherSpecialtyPreference/{teacherIds}', [TeacherController::class, 'bulkAddSpecialtyPreference']);
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
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->post('/assign-role/{schoolAdminId}', [RoleController::class, 'assignRoleSchoolAdmin']);
    Route::middleware(['auth:sanctum'])->post('/remove-role/{schoolAdminId}', [RoleController::class, 'removeRoleSchoolAdmin']);
});




Route::prefix('api/v1/school')->group(function () {
    Route::post('/register', [SchoolsController::class, 'createSchool']);
    Route::middleware(['auth:sanctum'])->put('/update_school', [SchoolsController::class, 'updateSchool']);
    Route::middleware(['auth:sanctum'])->delete('/delete-school/{school_id}', [SchoolsController::class, 'deleteSchool']);
    Route::middleware(['auth:sanctum'])->get('/schoolDetails', [schoolsController::class, 'getSchoolDetails']);
});

Route::prefix('api/v1/school-branch')->group(function () {
    Route::post('/register', [SchoolBranchesController::class, 'createSchoolBranch']);
    Route::middleware(['auth:sanctum'])->delete('/delete-branch/{branch_id}', [SchoolBranchesController::class, 'deleteSchoolBranch']);
    Route::middleware(['auth:sanctum'])->put('/update-branch/{branch_id}', [SchoolBranchesController::class, 'updateSchoolBranch']);
    Route::middleware([IdentifyTenant::class,])->get('/my-school-branches', [SchoolBranchesController::class, 'getAllSchoolBranches']);
});

Route::prefix('api/v1/country')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-country', [CountryController::class, 'createCountry']);
    Route::get('/countries', [CountryController::class, 'getCountries']);
    Route::middleware(['auth:sanctum'])->delete('/delete-country/{country_id}', [CountryController::class, 'deleteCountry']);
    Route::middleware(['auth:sanctum'])->put('/update-country/{country_id}', [CountryController::class, 'updateCountry']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/department')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-department', [DepartmentController::class, 'createDepartment']);
    Route::middleware(['auth:sanctum'])->get('/my-departments', [DepartmentController::class, 'getDepartments']);
    Route::middleware(['auth:sanctum'])->get('/department-details/{department_id}', [DepartmentController::class, 'getDepartmentDetails']);
    Route::middleware(['auth:sanctum'])->put('/update-department/{department_id}', [DepartmentController::class, 'updateDepartment']);
    Route::middleware(['auth:sanctum'])->delete('/delete-department/{department_id}', [DepartmentController::class, 'deleteDepartment']);
    Route::middleware(['auth:sanctum'])->get('/get-hods', [HodController::class, 'getHods']);
    Route::middleware(['auth:sanctum'])->delete("/delete-hod/{hodId}", [HodController::class, 'removeHod']);
    Route::middleware(['auth:sanctum'])->post('/assign-hod', [HodController::class, 'assignHeadOfDepartment']);
    Route::middleware(['auth:sanctum'])->get("/getAllHods", [HodController::class, "getAllHods"]);
    Route::middleware(['auth:sanctum'])->post("/deactivateDepartment/{departmentId}", [DepartmentController::class, "deactivateDepartment"]);
    Route::middleware(['auth:sanctum'])->post("/activateDepartment/{departmentId}", [DepartmentController::class, "activateDepartment"]);
    Route::middleware(['auth:sanctum'])->get("/getHodDetails/{hodId}", [HodController::class, "getHodDetails"]);
    Route::middleware(['auth:sanctum'])->delete('/bulkRemoveHods/{hodIds}', [HodController::class, 'bulkRemoveHod']);
    Route::middleware(['auth:sanctum'])->put('/bulkUpdateDepartment', [DepartmentController::class, 'bulkUpdateDepartment']);
    Route::middleware(['auth:sanctum'])->delete('/bulkDeleteDepartment/{departmentIds}', [DepartmentController::class, 'bulkDeleteDepartment']);
    Route::middleware(['auth:sanctum'])->post('/bulkDeactivateDepartment/{departmentIds}', [DepartmentController::class, 'bulkDeactivateDepartment']);
    Route::middleware(['auth:sanctum'])->post('/bulkActivateDepartment/{departmentIds}', [DepartmentController::class, 'bulkActivateDepartment']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/course')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-course', [CoursesController::class, 'createCourse']);
    Route::middleware(['auth:sanctum'])->delete('/delete-course/{course_id}', [CoursesController::class, 'deleteCourse']);
    Route::middleware(['auth:sanctum'])->put('/update-course/{course_id}', [CoursesController::class, 'updateCourse']);
    Route::middleware(['auth:sanctum'])->get('/my-courses', [CoursesController::class, 'getCourses']);
    Route::middleware(['auth:sanctum'])->get('/course-details/{course_id}', [CoursesController::class, 'getCourseDetails']);
    Route::middleware(['auth:sanctum'])->get('/my-courses/{specialty_id}/{semester_id}', [CoursesController::class, 'getBySpecialtyLevelSemester']);
    Route::middleware(['auth:sanctum'])->post('/deactivateCourse/{courseId}', [CoursesController::class, 'deactivateCourse']);
    Route::middleware(['auth:sanctum'])->post("/activateCourse/{courseId}", [CoursesController::class, 'activateCourse']);
    Route::middleware(['auth:sanctum'])->get('/getCoursesBySchoolSemester/{semesterId}/{specialtyId}', [CoursesController::class, 'getCoursesBySchoolSemester']);
    Route::middleware(['auth:sanctum'])->delete('/bulkDeleteCourses/{courseIds}', [CoursesController::class, 'bulkDeleteCourse']);
    Route::middleware(['auth:sanctum'])->get('/getActiveCourses', [CoursesController::class, 'getActiveCourses']);
    Route::middleware(['auth:sanctum'])->put('/bulkUpdateCourse', [CoursesController::class, 'bulkUpdateCourse']);
    Route::middleware(['auth:sanctum'])->post('/bulkActivateCourse/{courseIds}', [CoursesController::class, 'bulkActivateCourse']);
    Route::middleware(['auth:sanctum'])->post('/bulkDeactivateCourse/{courseIds}', [CoursesController::class, 'bulkDeactivateCourse']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/specialty')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-specialty', [SpecialtyController::class, 'createSpecialty']);
    Route::middleware(['auth:sanctum'])->delete('/delete-specialty/{specialty_id}', [SpecialtyController::class, 'deleteSpecialty']);
    Route::middleware(['auth:sanctum'])->put('/update-specialty/{specialty_id}', [SpecialtyController::class, 'updateSpecialty']);
    Route::middleware(['auth:sanctum'])->get('/my-specialties', [SpecialtyController::class, 'getSpecialtiesBySchoolBranch']);
    Route::middleware(['auth:sanctum'])->get('/specialty-details/{specialty_id}', [SpecialtyController::class, 'getSpecialtyDetails']);
    Route::middleware(['auth:sanctum'])->post("/assign-hos", [HosController::class, 'assignHeadOfSpecialty']);
    Route::middleware(['auth:sanctum'])->get('/get-assigned-hos', [HosController::class, 'getHeadOfSpecialty']);
    Route::middleware(['auth:sanctum'])->delete("/remove-hos/{hosId}", [HosController::class, 'removeHeadOfSpecialty']);
    Route::middleware(['auth:sanctum'])->delete("/bulkRemoveHos/{hosId}", [HosController::class, 'bulkRemoveHos']);
    Route::middleware(['auth:sanctum'])->get("/getAllHos", [HosController::class, "getAllHos"]);
    Route::middleware(['auth:sanctum'])->post("/deactivateSpecialty/{specialtyId}", [SpecialtyController::class, "deactivateSpecialty"]);
    Route::middleware(['auth:sanctum'])->post("/activateSpecialty/{specialtyId}", [SpecialtyController::class, "activateSpecialty"]);
    Route::middleware(['auth:sanctum'])->get("/getHosDetails/{hosId}", [HosController::class, "getHosDetails"]);
    Route::middleware(['auth:sanctum'])->delete('/bulkDeleteSpecialty/{specialtyIds}', [SpecialtyController::class, 'bulkDeleteSpecialty']);
    Route::middleware(['auth:sanctum'])->put('/bulkUpdateSpecialty/{specialtyIds}', [SpecialtyController::class, 'bulkUdateSpecialty']);
    Route::middleware(['auth:sanctum'])->post('/bulkDeactivateSpecialty/{specialtyIds}', [SpecialtyController::class, 'bulkDeactivateSpecialty']);
    Route::middleware(['auth:sanctum'])->post('/bulkActivateSpecialty/{specialtyIds}', [SpecialtyController::class, 'bulkActivateSpecialty']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/marks')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/add-student-mark', [MarksController::class, 'createMark']);
    Route::middleware(['auth:sanctum'])->put('/update-student-mark/{mark_id}', [MarksController::class, 'updateMark']);
    Route::middleware(['auth:sanctum'])->delete('/delete-student-mark/{mark_id}', [MarksController::class, 'deleteMark']);
    Route::middleware(['auth:sanctum'])->get('/scores-exam/{student_id}/{exam_id}', [MarksController::class, 'getMarksByExamStudent']);
    Route::middleware(['auth:sanctum'])->get("/scores-exam/student", [MarksController::class, 'getMarkDetails']);
    Route::middleware(['auth:sanctum'])->get("/score-details/{mark_id}", [MarksController::class, 'getMarkDetails']);
    Route::middleware(['auth:sanctum'])->get("/accessed-courses/{examId}", [MarksController::class, "getAccessedCoursesWithLettergrades"]);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/teacher-avialability')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-availability', [InstructorAvailabilityController::class, 'createInstructorAvailability']);
    Route::middleware(['auth:sanctum'])->delete('/delete-availability/{availabilty_id}', [InstructorAvailabilityController::class, 'deleteInstructorAvailabilty']);
    Route::middleware(['auth:sanctum'])->put('/update-availability/{availability_id}', [InstructorAvailabilityController::class, 'updateInstructorAvailability']);
    Route::middleware(['auth:sanctum'])->get('/teacher-avialability/{teacher_id}', [InstructorAvailabilityController::class, 'getInstructorAvailability']);
});

Route::prefix('api/v1/levels')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-level', [EducationLevelsController::class, ' createEducationLevel']);
    Route::middleware(['auth:sanctum'])->put('/update-level/{level_id}', [EducationLevelsController::class, 'updateEducationLevel']);
    Route::middleware(['auth:sanctum'])->delete('/delete-level/{level_id}', [EducationLevelsController::class, 'deleteEducationLevel']);
    Route::middleware(['auth:sanctum'])->get('/education-levels', [EducationLevelsController::class, 'getEducationLevel']);
});


Route::middleware([IdentifyTenant::class])->prefix('api/v1/grades')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-grade', [GradesController::class, 'createExamGrades']);
    Route::middleware(['auth:sanctum'])->get('/grades-for-exams', [GradesController::class, 'getAllGrades']);
    Route::middleware(['auth:sanctum'])->put('/update-grade/{grade_id}', [GradesController::class, 'update_grades_scoped']);
    Route::middleware(['auth:sanctum'])->delete('/delete-grade/{examId}', [GradesController::class, 'deleteGrades']);
    Route::middleware(['auth:sanctum'])->get('/getRelatedExams/{examId}', [GradesController::class, 'getRelatedExams']);
    Route::middleware(['auth:sanctum'])->get('/getGradesByExam/{examId}', [GradesController::class, 'getGradesConfigByExam']);
    Route::middleware(['auth:sanctum'])->get('/getExamConfigData/{examId}', [GradesController::class, 'getExamConfigData']);
    Route::middleware(['auth:sanctum'])->get('/getSchoolGradesConfig', [SchoolGradeConfigController::class, 'getSchoolGradesConfig']);
    Route::middleware(['auth:sanctum'])->post('/createGradeByOtherConfig/{configId}/{targetConfigId}', [GradesController::class, 'createGradesByOtherGrades']);
});

Route::middleware(['auth:sanctum'])->prefix('api/v1/grades-category')->group( function () {
    Route::post('/createGradeCategory', [GradesCategoryController::class, 'createCategory']);
    Route::delete('/deleteGradeCategory/{categoryId}', [GradesCategoryController::class, 'deleteCategory']);
    Route::put('/updateGradeCategory/{categoryId}', [GradesCategoryController::class, 'updateCategory']);
    Route::get('/getGradeCategories', [GradesCategoryController::class, 'getGradesCategory']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/exams')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-exam', [ExamsController::class, 'createExam']);
    Route::middleware(['auth:sanctum'])->put('/update-exam/{exam_id}', [ExamsController::class, 'updateExam']);
    Route::middleware(['auth:sanctum'])->get('/getexams', [ExamsController::class, 'getExams']);
    Route::middleware(['auth:sanctum'])->get('/exam-details/{exam_id}', [ExamsController::class, 'getExamDetails']);
    Route::middleware(['auth:sanctum'])->delete('/delete-exams/{exam_id}', [ExamsController::class, 'deleteExam']);
    Route::middleware(['auth:sanctum'])->get('/letter-grades/{exam_id}', [ExamsController::class, 'associateWeightedMarkWithLetterGrades']);
    Route::middleware(['auth:sanctum'])->get("/accessed_exams/{student_id}", [ExamsController::class, "getAccessedExams"]);
    Route::middleware(['auth:sanctum'])->post('/addExamGrading/{examId}/{gradesConfigId}', [ExamsController::class, 'addExamGrading']);
    Route::middleware(['auth:sanctum'])->get('/getAllResitExams', [ExamsController::class, 'getResitExams']);
    Route::middleware(['auth:sanctum'])->delete('/bulkDeleteExam/{examIds}', [ExamsController::class, 'bulkDeleteExam']);
    Route::middleware(['auth:sanctum'])->put('/bulkUpdateExam', [ExamsController::class, 'bulkUpdateExam']);
    Route::middleware(['auth:sanctum'])->post('/bulkAddExamGrading', [ExamsController::class, 'bulkAddExamGrading']);
});


Route::middleware([IdentifyTenant::class])->prefix('api/v1/exam-timetable')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-timetable/{examId}', [ExamTimeTableController::class, 'createTimtable']);
    Route::middleware(['auth:sanctum'])->put('/update-exam-time-table/{examtimetable_id}', [ExamTimeTableController::class, 'updateTimetable']);
    Route::middleware(['auth:sanctum'])->get('/generate-timetable/{level_id}/{specialty_id}', [ExamTimeTableController::class, 'getTimetableBySpecialty']);
    Route::middleware(['auth:sanctum'])->get('/course-data/{exam_id}', [ExamTimeTableController::class, 'prepareExamTimeTableData']);
    Route::middleware(['auth:sanctum'])->delete('/deleteTimetableEntry/{timetableEntryId}', [ExamTimeTableController::class, 'deleteTimetableEntry']);
    Route::middleware(['auth:sanctum'])->delete('/deleteTimeTable/{examId}', [ExamTimetableController::class, 'deleteTimetable']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/time-table')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/createTimetableByAvailability/{semesterId}', [TimeTableController::class, 'createTimetableByAvailability']);
    Route::middleware(['auth:sanctum'])->post('/createTimetable/{semesterId}', [TimeTableController::class, 'createTimetable']);
    Route::middleware(['auth:sanctum'])->put('/update-timetable/{timetable_id}', [TimeTableController::class, 'updateTimetable']);
    Route::middleware(['auth:sanctum'])->delete('/delete-timetable/{timetable_id}', [TimeTableController::class, 'deleteTimetable']);
    Route::middleware(['auth:sanctum'])->get('/generate-timetable', [TimeTableController::class, 'generateTimetable']);
    Route::middleware(['auth:sanctum'])->get('/timetable-details/{entry_id}', [TimeTableController::class, 'getTimetableDetails']);
    Route::middleware(['auth:sanctum'])->get('/instructor-availability/{semester_id}/{specialty_id}', [TimetableController::class, 'getInstructorAvailabilityBySemesterSpecialty']);

});

Route::prefix('api/v1/semester')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-semester', [SemesterController::class, 'createSemester']);
    Route::middleware(['auth:sanctum'])->delete('/delete-semester/{semester_id}', [SemesterController::class, 'deleteSemester']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->get('/semesters', [SemesterController::class, 'getSemesters']);
    Route::middleware(['auth:sanctum'])->put('/update-semester/{semester_id}', [SemesterController::class, 'updateSemester']);
});

Route::prefix('api/v1/school-semesters')->group(function () {
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->post('/create-school-semester', [SchoolSemesterController::class, 'createSchoolSemester']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->put("/update-school-semester/{schoolSemesterId}", [SchoolSemesterController::class, 'updateSchoolSemester']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->get("/school-semeters", [SchoolSemesterController::class, 'getSchoolSemester']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->get("/delete-school-semeter/{schoolSemesterId}", [SchoolSemesterController::class, 'deleteSchoolSemester']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->get("/schoolSemesterDetails/{schoolSemesterId}", [SchoolSemesterController::class, 'getSchoolSemesterDetails']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/event')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-event', [EventsController::class, 'createEvent']);
    Route::middleware(['auth:sanctum'])->put('/update-event/{event_id}', [EventsController::class, 'updateEvent']);
    Route::middleware(['auth:sanctum'])->delete('/delete-event/{event_id}', [EventsController::class, 'deleteEvent']);
    Route::middleware(['auth:sanctum'])->get('/school-events', [EventsController::class, 'getEvents']);
    Route::middleware(['auth:sanctum'])->get("/school-event/details/{event_id}", [EventsController::class, "getEventDetails"]);
});




Route::prefix('api/v1/exam-type')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-exam-type', [ExamTypecontroller::class, 'createExamType']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->get('/exam_types', [ExamTypecontroller::class, 'getExamType']);
    Route::middleware(['auth:sanctum'])->delete('/exam-type/{exam_id}', [ExamTypecontroller::class, 'deleteExamType']);
    Route::middleware(['auth:sanctum'])->put('/update-exam-type/{exam_id}', [ExamTypecontroller::class, 'updateExamType']);
});

Route::prefix('api/v1/letter-grade')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-letter-grade', [LetterGradecontroller::class, 'createLettGrade']);
    Route::middleware(['auth:sanctum'])->get('/get-letter-grades', [LetterGradecontroller::class, 'getLetterGrades']);
    Route::middleware(['auth:sanctum'])->delete('/delete-letter-grade/{letter_grade_id}', [LetterGradecontroller::class, 'deleteLetterGrade']);
    Route::middleware(['auth:sanctum'])->put('/update-letter-grate/{letter_grade_id}', [LetterGradecontroller::class, 'updateLetterGrade']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/grades-analytics')->group(function () {
    Route::middleware(['auth:sanctum'])->get('/get-risky-subjects', [StudentPerformanceReportController::class, 'high_risk_course_tracking']);
    Route::middleware(['auth:sanctum'])->post('/calculate-desired-gpa', [StudentPerformanceReportController::class, 'calculate_desired_gpa']);
});

Route::prefix('api/v1/subcription')->group(function () {
    Route::post('/subscribe', [SchoolSubscriptionController::class, 'subscribe']);
    Route::get('/subscribed-schools', [SchoolSubscriptionController::class, 'getSubscribedSchools']);
    Route::get('/subscription-details/{subscription_id}', [SchoolSubscriptionController::class, 'getSchoolSubscriptonDetails']);
    Route::post('/create-rate', [RatesCardController::class, 'createRates']);
    Route::put('/update-rate', [RatesCardController::class, 'updateRates']);
    Route::delete('/delete-rate/{rate_id}', [RatesCardController::class, 'deleteRates']);
    Route::get('/rates', [RatesCardController::class, 'getAllRates']);
    Route::delete('/delete-transaction', [SubscriptionPaymentController::class, 'deletePayment']);
    Route::get('/my-transactions/{school_id}', [SubscriptionPaymentController::class, 'getTransactionsBySchool']);
    Route::get('/payment-transactions/{school_id}', [SubscriptionPaymentController::class, 'getAllTransactions']);
});


Route::middleware([IdentifyTenant::class])->prefix('api/v1/school-expenses')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-expenses', [ExpensesController::class, 'createExpense']);
    Route::middleware(['auth:sanctum'])->delete('/delete-expenses/{expense_id}', [ExpensesController::class, 'deleteExpense']);
    Route::middleware(['auth:sanctum'])->get('/my-expenses', [ExpensesController::class, 'getExpenses']);
    Route::middleware(['auth:sanctum'])->get('/expenses-details/{expense_id}', [ExpensesController::class, 'getExpensesDetails']);
    Route::middleware(['auth:sanctum'])->put('/update-expenses/{expense_id}', [ExpensesController::class, 'updateExpense']);
    Route::middleware(['auth:sanctum'])->delete('/bulkDeleteSchoolExpenses/{schoolExpensesIds}', [ExpensesController::class, 'bulkDeleteSchoolExpenses']);
    Route::middleware(['auth:sanctum'])->put('/bulkUpdateSchoolExpenses', [ExpensesController::class, 'bulkUpdateSchoolExpenses']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/school-expenses-category')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-category', [ExpensesCategorycontroller::class, 'createCategory']);
    Route::middleware(['auth:sanctum'])->delete('/delete-category/{category_expense_id}', [ExpensesCategorycontroller::class, 'deleteCategory']);
    Route::middleware(['auth:sanctum'])->get('/get-category-expenses', [ExpensesCategorycontroller::class, 'getCategory']);
    Route::middleware(['auth:sanctum'])->put('/update-category/{category_expense_id}', [ExpensesCategorycontroller::class, 'updateCategory']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/student-batches')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-batch', [StudentBatchcontroller::class, 'createStudentBatch']);
    Route::middleware(['auth:sanctum'])->get('/student-batches', [StudentBatchcontroller::class, 'getStudentBatch']);
    Route::middleware(['auth:sanctum'])->delete('/delete-batch/{batch_id}', [StudentBatchcontroller::class, 'deleteStudentBatch']);
    Route::middleware(['auth:sanctum'])->put('/update-batch/{batch_id}', [StudentBatchcontroller::class, 'updateStudentBatch']);
    Route::middleware(['auth:sanctum'])->post('/activateStudentBatch/{batchId}', [StudentBatchController::class, 'activateStudentBatch']);
    Route::middleware(['auth:sanctum'])->post('/deactivateStudentBatch/{batchId}', [StudentBatchController::class, 'deactivateStudentBatch']);
    Route::middleware(['auth:sanctum'])->post('/assignGraduationDatesByBatch', [StudentBatchController::class, 'assignGradDatesBySpecialty']);
    Route::middleware(['auth:sanctum'])->get('/getStudentGraduationDatesByBatch/{batchId}', [StudentBatchController::class, 'getGraduationDatesByBatch']);
    Route::middleware(['auth:sanctum'])->delete("/bulkDeleteStudentBatch/{batchIds}", [StudentBatchController::class, 'bulkDeleteStudentBatch']);
    Route::middleware(['auth:sanctum'])->post("/bulkActivateStudentBatch/{batchIds}", [StudentBatchController::class, 'bulkActivateStudentBatch']);
    Route::middleware(['auth:sanctum'])->post("/bulkDeactivateStudentBatch/{batchIds}", [StudentBatchController::class, 'bulkDeactivateStudentBatch']);
    Route::middleware(['auth:sanctum'])->put("/bulkUpdateStudentBatch", [StudentBatchController::class, 'bulkUpdateStudentBatch']);
    Route::middleware(['auth:sanctum'])->post("/bulkAssignGradDatesBySpecialty", [StudentBatchController::class, 'bulkAssignGradDateBySpecialty']);

});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/fee-payment')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/pay-fees', [FeePaymentController::class, 'payTuitionFees']);
    Route::middleware(['auth:sanctum'])->get('/paid-fees', [FeePaymentController::class, 'getFeesPaid']);
    Route::middleware(['auth:sanctum'])->put('/update-payment/{fee_id}', [FeePaymentController::class, 'updateFeesPaid']);
    Route::middleware(['auth:sanctum'])->delete('/delete-payment-record/{fee_id}', [FeePaymentController::class, 'deleteFeePaid']);
    Route::middleware(['auth:sanctum'])->get('/indebted-students', [FeePaymentController::class, 'getFeeDebtors']);
    Route::middleware(['auth:sanctum'])->post('/payRegistrationFee', [FeePaymentController::class, 'payRegistrationFees']);
    Route::middleware(['auth:sanctum'])->get('/getTuitionFees', [FeePaymentController::class, 'getTuitionFees']);
    Route::middleware(['auth:sanctum'])->get("/getRegistrationFees", [FeePaymentController::class, 'getRegistrationFees']);
    Route::middleware(['auth:sanctum'])->get("/getTransactions", [FeePaymentController::class, 'getTuitionFeeTransactions']);
    Route::middleware(['auth:sanctum'])->delete('/reverseTuitionFeeTransaction/{transactionId}', [FeePaymentController::class, 'reverseTuitionFeeTransaction']);
    Route::middleware(['auth:sanctum'])->delete('/deleteTransaction/{transactionId}', [FeePaymentController::class, 'deleteTuitionFeeTransaction']);
    Route::middleware(['auth:sanctum'])->get("/getTuitionFeeTransactionDetails/{transactionId}", [FeePaymentController::class, 'getTuitionTransactionFeeDetails']);
    Route::middleware(['auth:sanctum'])->get('/getRegistrationFeeTransactions', [FeePaymentController::class, 'getRegistrationFeeTransactions']);
    Route::middleware(['auth:sanctum'])->delete('/reverseRegistrationFeeTransaction/{transactionId}', [FeePaymentController::class, 'reverseRegistrationFeeTransaction']);
    Route::middleware(['auth:sanctum'])->delete('/bulkDeleteTuitionFeeTransactions/{transactionIds}', [FeePaymentController::class, 'bulkDeleteTuitionFeeTransactions']);
    Route::middleware(['auth:sanctum'])->delete('/bulkDeleteRegistrationFeeTransactions/{transactionIds}', [FeePaymentController::class, 'bulkDeleteRegistrationFeeTransactions']);
    Route::middleware(['auth:sanctum'])->post('/bulkReverseFeeTuitionFeeTransactions/{transactionIds}', [FeePaymentController::class, 'bulkReverseTuitionFeeTransaction']);
    Route::middleware(['auth:sanctum'])->post('/bulkReverseRegistrationFeeTransactions/{transactionIds}', [FeePaymentController::class, 'bulkReverseRegistrationFeeTransaction']);
    Route::middleware(['auth:sanctum'])->post('/bulkPayRegistrationFee', [FeePaymentController::class, 'bulkPayRegistrationFee']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/student-resit')->group(function () {
    Route::middleware(['auth:sanctum'])->get('/get-student-resits/{student_id}', [StudentResitController::class, 'getResitByStudent']);
    Route::middleware(['auth:sanctum'])->post('/pay-for-resit', [StudentResitController::class, 'payResit']);
    Route::middleware(['auth:sanctum'])->put('/update-resit-status/{resit_id}', [StudentResitController::class, 'update_exam_status']);
    Route::middleware(['auth:sanctum'])->put('/update-resit/{resit_id}', [StudentResitController::class, 'updateResit']);
    Route::middleware(['auth:sanctum'])->delete('/delete-resit/{resit_id}', [StudentResitController::class, 'deleteResit']);
    Route::middleware(['auth:sanctum'])->post('/resit-timetable', [StudentResitController::class, 'create_resit_timetable_entry']);
    Route::middleware(['auth:sanctum'])->get('/student_resits', [StudentResitController::class, 'getAllResits']);
    Route::middleware(['auth:sanctum'])->get('/get-specialty-resit/{specialty_id}/{exam_id}', [ResitTimeTableController::class, 'getResitsBySpecialty']);
    Route::middleware(['auth:sanctum'])->post('/createResitTimetable/{examId}', [ResitTimeTableController::class, 'createResitTimetable']);
    Route::middleware(['auth:sanctum'])->get('/getResitCoursesByExam/{examId}', [ResitTimeTableController::class, 'getResitCoursesByExam']);
    Route::middleware(['auth:sanctum'])->delete('/deleteResitTimetable/{examId}', [ResitTimetableController::class, 'deleteResitTimetable']);
    Route::middleware(['auth:sanctum'])->get('/accessedResitStudents', [AccessedResitStudentController::class, 'getResitExamCandidates']);
    Route::middleware(['auth:sanctum'])->delete('/deleteAccessedStudent/{candidateId}', [AccessedResitStudentController::class, 'deleteAccessedResitStudent']);
    Route::middleware(['auth:sanctum'])->get("/details/{resit_id}", [StudentResitController::class, 'getResitDetails']);
    Route::middleware(['auth:sanctum'])->get("/getTransactions", [StudentResitController::class, 'getResitPaymentTransactions']);
    Route::middleware(['auth:sanctum'])->delete('/deleteTransaction/{transactionId}', [StudentResitController::class, 'deleteFeePaymentTransaction']);
    Route::middleware(['auth:sanctum'])->get('/transactionDetails/{transactionId}', [StudentResitController::class, 'getTransactionDetails']);
    Route::middleware(['auth:sanctum'])->delete('/reverseTransaction/{transactionId}', [StudentResitController::class, 'reverseTransaction']);
    Route::middleware(['auth:sanctum'])->post('/submitResitResults', [StudentResitController::class, 'submitResitScores']);
    Route::middleware(['auth:sanctum'])->get("/getStudentResitData/{examId}/{studentId}", [StudentResitController::class, 'prepareResitData']);
    Route::middleware(['auth:sanctum'])->post('/bulkPayStudentResit/{studentResitIds}', [StudentResitController::class, 'bulkPayStudentResit']);
    Route::middleware(['auth:sanctum'])->delete('/bulkDeleteStudentResit/{studentResitIds}', [StudentResitController::class, 'bulkDeleteStudentResit']);
    Route::middleware(['auth:sanctum'])->delete('/bulkDeleteResitTransactions/{studentResitIds}', [StudentResitController::class, 'bulkDeleteStudentResitTransactions']);
    Route::middleware(['auth:santum'])->post('/bulkReverseResitTransactions/{transactionIds}', [StudentResitController::class, 'bulkReverseTransaction']);
    Route::middleware(['auth:sanctum'])->put('/bulkUpdateStudentResit/{studentResitIds}', [StudentResitController::class, 'bulkUpdateStudentResit']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/elections')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-election', [ElectionsController::class, 'createElection']);
    Route::middleware(['auth:sanctum'])->get('/get-elections', [ElectionsController::class, 'getElections']);
    Route::middleware(['auth:sanctum'])->delete('/delete-election/{election_id}', [ElectionsController::class, 'deleteElection']);
    Route::middleware(['auth:sanctum'])->get('/update-election/{election_id}', [ElectionsController::class, 'updateElection']);
    Route::middleware(['auth:sanctum'])->post('/cast-vote', [ElectionsController::class, 'vote']);
    Route::middleware(['auth:sanctum'])->get('/election-results/{election_id}', [ElectionResultsController::class, 'getElectionResults']);
    Route::middleware(['auth:sanctum'])->get('/election-candidates/{electionId}', [ElectionsController::class, 'getElectionCandidates']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/election-application')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/apply', [ElectionApplicationController::class, 'createElectionApplication']);
    Route::middleware(['auth:sanctum'])->get('/applications/{election_id}', [ElectionApplicationController::class, 'getApplications']);
    Route::middleware(['auth:sanctum'])->put('/update-application/{application_id}', [ElectionApplicationController::class, 'updateApplication']);
    Route::middleware(['auth:sanctum'])->delete('/delete/{application_id}', [ElectionApplicationController::class, 'deleteApplication']);
    Route::middleware(['auth:sanctum'])->put('/approve-application/{application_id}', [ElectionApplicationController::class, 'approveApplication']);
    Route::middleware(['auth:sanctum'])->get('/getAllApplications', [ElectionApplicationController::class, 'getAllElectionApplication']);
});

Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/election-roles')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-role', [ElectionRolesController::class, 'createElectionRole']);
    Route::middleware(['auth:sanctum'])->put('/update-election/{election_role_id}', [ElectionRolesController::class, 'updateElectionRole']);
    Route::middleware(['auth:sanctum'])->delete('/delete-role/{election_role_id}', [ElectionRolesController::class, 'deleteElectionRole']);
    Route::middleware(['auth:sanctum'])->get('/election-roles/{election_id}', [ElectionRolesController::class, 'getElectionRoles']);
    Route::middleware(['auth:sanctum'])->get("/getAllRoles", [ElectionRolesController::class, 'getAllElectionRoles']);
});

Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/additional-fees')->group(function () {
    Route::post("/createFee", [StudentAdditionalFeesController::class, 'createStudentAdditionalFees']);
    Route::put('/updateFee/{feeId}', [StudentAdditionalFeesController::class, 'updateStudentAdditionalFees']);
    Route::delete('/deleteFee/{feeId}', [StudentAdditionalFeesController::class, 'deleteStudentAdditionalFees']);
    Route::get('/getbyStudent/{studentId}', [StudentAdditionalFeesController::class, 'getStudentAdditionalFees']);
    Route::get('/getAll', [StudentAdditionalFeesController::class, 'getAdditionalFees']);
    Route::post('/payFee', [StudentAdditionalFeesController::class, 'payAdditionalFees']);
    Route::get('/getTransactions', [StudentAdditionalFeesController::class, 'getAdditionalFeesTransactions']);
    Route::delete('/deleteTransaction/{transactionId}', [StudentAdditionalFeesController::class, 'deleteTransaction']);
    Route::delete('/reverseTransaction/{transactionId}', [StudentAdditionalFeesController::class, 'reverseAdditionalFeesTransaction']);
    Route::get("/getTransactionDetails/{transactionId}", [StudentAdditionalFeesController::class, 'getTransactionDetails']);
    Route::delete("/bulkDeleteAdditionalFees/{additionalFeeIds}", [StudentAdditionalFeesController::class, 'bulkDeleteStudentAdditionalFees']);
    Route::delete("/bulkDeleteTransaction/{transactionIds}", [StudentAdditionalFeesController::class, 'bulkDeleteTransaction']);
    Route::post("/bulkBillStudent", [StudentAdditionalFeesController::class, 'bulkBillStudents']);
    Route::post("/bulkReverseTransaction/{transactionIds}", [StudentAdditionalFeesController::class, 'bulkReverseTransaction']);
    Route::post('/bulkPayFee', [StudentAdditionalFeesController::class, 'bulkPayFees']);
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

Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/accessed-student')->group( function () {
   Route::get('/getAccessedStudent', [AccessedStudentController::class, 'getAccessedStudent']);
   Route::delete('/deleteAccessedStudent/{accessedStudentId}', [AccessedStudentController::class, 'deleteAccessedStudent']);
});

Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->prefix('api/v1/additional-fee-category')->group(function () {
    Route::post('/createCategory', [AdditionalFeeCategoryController::class, 'createAddtionalFeeCategory']);
    Route::get('/getCategory', [AdditionalFeeCategoryController::class, 'getAdditionalFeeCategory']);
    Route::delete('/deletCategory/{feeCategoryId}', [AdditionalFeeCategoryController::class, 'deleteAdditionalFeeCategory']);
    Route::put('/updateCategory/{feeCategoryId}', [AdditionalFeeCategoryController::class, 'updateAdditionalFeeCategory']);
});


Route::middleware([IdentifyTenant::class])->prefix('api/v1/stats')->group(function () {
    Route::middleware(['auth:sanctum'])->get("/get/financial-stats", [FinancialStatsController::class, 'getFinanacialStats']);
});

Route::middleware([IdentifyTenant::class])->prefix('api/v1/student-results')->group( function() {
    Route::middleware(['auth:sanctum'])->get('/getAllStudentResults', [StudentResultController::class, 'getAllStudentResults']);
});
