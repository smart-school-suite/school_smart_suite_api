<?php

use App\Http\Controllers\Auth\Edumanage\createeduadmincontroller;
use App\Http\Controllers\Auth\Edumanage\logineduadmincontroller;
use App\Http\Controllers\Auth\Edumanage\logouteduadmincontroller;
use App\Http\Controllers\Auth\Parent\changepasswordController;
use App\Http\Controllers\Auth\Parent\createparentController;
use App\Http\Controllers\Auth\Parent\getauthenticatedparentcontroller;
use App\Http\Controllers\Auth\Parent\logincontroller;
use App\Http\Controllers\Auth\Parent\logoutcontroller;
use App\Http\Controllers\Auth\Parent\resetpasswordController as ParentResetpasswordController;
use App\Http\Controllers\Auth\SchoolAdmin\createschooladmincontroller;
use App\Http\Controllers\Auth\SchoolAdmin\getauthenticatedschoolcontroller;
use App\Http\Controllers\Auth\SchoolAdmin\loginschooladmincontroller;
use App\Http\Controllers\Auth\SchoolAdmin\logoutschooladmincontroller;
use App\Http\Controllers\Auth\Student\changepasswordController as StudentChangepasswordController;
use App\Http\Controllers\Auth\Student\createstudentController;
use App\Http\Controllers\Auth\Student\getauthenticatedstudentcontroller;
use App\Http\Controllers\Auth\Student\loginstudentcontroller;
use App\Http\Controllers\Auth\Student\logoutstudentcontroller;
use App\Http\Controllers\Auth\Student\resetpasswordController;
use App\Http\Controllers\Auth\Teacher\changepasswordController as TeacherChangepasswordController;
use App\Http\Controllers\Auth\Teacher\createteacherController;
use App\Http\Controllers\Auth\Teacher\getauthenticatedteachercontroller;
use App\Http\Controllers\Auth\Teacher\loginteachercontroller;
use App\Http\Controllers\Auth\Teacher\logoutteachercontroller;
use App\Http\Controllers\Auth\Teacher\resetpasswordController as TeacherResetpasswordController;
use App\Http\Controllers\countryController;
use App\Http\Controllers\coursesController;
use App\Http\Controllers\departmentController;
use App\Http\Controllers\educationlevelsController;
use App\Http\Controllers\edumanageadminController;
use App\Http\Controllers\eventsController;
use App\Http\Controllers\examsController;
use App\Http\Controllers\examtimetableController;
use App\Http\Controllers\Examtypecontroller;
use App\Http\Controllers\gradesController;
use App\Http\Controllers\instructoravailabilityController;
use App\Http\Controllers\letterGradecontroller;
use App\Http\Controllers\marksController;
use App\Http\Controllers\parentsController;
use App\Http\Controllers\Passwordresetcontroller;
use App\Http\Controllers\Reportcardgenerationcontroller;
use App\Http\Controllers\schooladminController;
use App\Http\Controllers\schoolbranchesController;
use App\Http\Controllers\Schoolexpensescategorycontroller;
use App\Http\Controllers\SchoolexpensesController;
use App\Http\Controllers\schoolsController;
use App\Http\Controllers\semesterController;
use App\Http\Controllers\SchoolSemesterController;
use App\Http\Controllers\specialtyController;
use App\Http\Controllers\Studentbatchcontroller;
use App\Http\Controllers\studentController;
use App\Http\Controllers\StudentPerformanceReportController;
use App\Http\Controllers\teacherController;
use App\Http\Controllers\timetableController;
use App\Http\Controllers\feepaymentController;
use App\Http\Controllers\Auth\Edumanage\changeedumanagepasswordcontroller;
use App\Http\Controllers\Auth\Edumanage\getauthenticatededumanageadmincontroller;
use App\Http\Controllers\Auth\SchoolAdmin\validateOtpController;
use App\Http\Controllers\studentpromotionController;
use App\Http\Controllers\studentResitController;
use App\Http\Controllers\ResitcontrollerTimetable;
use App\Http\Controllers\TeacherSpecailtyPreferenceController;
use App\Http\Controllers\SchoolSubscriptionController;
use App\Http\Controllers\RatesCardController;
use App\Http\Controllers\SubscriptionPaymentController;
use App\Http\Controllers\electionsController;
use App\Http\Controllers\electionApplicationController;
use App\Http\Controllers\electionRolesController;
use App\Http\Controllers\electionResultsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReportAnalytics\FinancialreportController;
use App\Http\Controllers\HodController;
use App\Http\Controllers\HosController;
use App\Http\Middleware\IdentifyTenant;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;



Route::prefix('edumanage-admin')->group( function (){
    Route::post('/create-admin', [createeduadmincontroller::class, 'create_edumanage_admin']);
    Route::middleware('web')->post('/login-admin', [logineduadmincontroller::class, 'login_edumanage_admin']);
    Route::middleware('auth:sanctum')->post('/logout-admin', [logouteduadmincontroller::class, 'logout_eduadmin']);
    Route::get('/get-all-admins', [edumanageadminController::class, 'get_all_eduamage_admins']);
    Route::middleware('auth:sanctum')->post('/change-password', [changeedumanagepasswordcontroller::class, 'change_edumanageadmin_password']);
    Route::delete('/delete-admin/{edumanage_admin_id}', [edumanageadminController::class, 'delete_edumanage_admin']);
    Route::middleware('auth:sanctum')->get('/auth-edumanage-admin', [getauthenticatededumanageadmincontroller::class, 'get_authenticated_eduamanageadmin']);
    Route::middleware(['auth:sanctum', 'web'])->post('/verify-otp', [logineduadmincontroller::class, 'verify_otp']);
    Route::put('/update-admin/{edumanage_admin_id}', [edumanageadminController::class, 'update_edumanage_admin']);
});

Route::prefix('permissions')->group(function(){
    Route::middleware(['auth:sanctum'])->post('/create-permission', [PermissionController::class, 'createPermission']);
    Route::middleware(['auth:sanctum'])->get("/get-permissions",  [PermissionController::class, "getPermission"]);
    Route::middleware(['auth:sanctum'])->delete("/delete-permission/{permissionId}", [PermissionController::class, 'deletePermission']);
    Route::middleware(['auth:sanctum'])->put('/update-permission/{permissionId}', [PermissionController::class, "updatePermission"]);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->get('/get-schooladmin/permissions/{schoolAdminId}', [PermissionController::class, "getSchoolAdminPermissions"]);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->post('/grant-schoolAdmin-permissions/{schoolAdminId}', [PermissionController::class, 'givePermissionToSchoolAdmin']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->post("/revoke-schoolAdmin-permissions/{schoolAdminId}", [PermissionController::class, 'revokePermission']);
});

Route::prefix('roles')->group( function() {
    Route::middleware(['auth:sanctum'])->post('/create-role', [RoleController::class, 'createRole']);
    Route::middleware(['auth:sanctum'])->get('/get-roles', [RoleController::class, 'getRoles']);
    Route::middleware(['auth:sanctum'])->delete('/delete-roles/{roleId}', [RoleController::class, 'updateRole']);
    Route::middleware(['auth:sanctum'])->put('/update-role/{roleId}', [RoleController::class, 'updateRole']);
    Route::middleware(['auth:sanctum'])->post('/assign-role/{schoolAdminId}', [RoleController::class, 'assignRoleSchoolAdmin']);
    Route::middleware(['auth:sanctum'])->post('/remove-role/{schoolAdminId}', [RoleController::class, 'removeRoleSchoolAdmin']);
});
Route::prefix('parent')->group(function () {
    Route::post('/login', [logincontroller::class, 'login_parent']);
    Route::post('/reset-password', [ParentResetpasswordController::class, 'reset_password']);
    Route::middleware('auth:sanctum')->post('/change-password', [changepasswordController::class, 'change_parent_password']);
    Route::middleware('auth:sanctum')->post('/logout', [logoutcontroller::class, 'logout_parent']);
    Route::middleware('auth:sanctum')->post('/auth-parent', [getauthenticatedparentcontroller::class, 'get_authenticated_parent']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post('/create-parent', [createparentController::class, 'create_parent']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->delete('/delete-parent/{parent_id}', [parentsController::class, 'delete_parent_with_scope']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->put('/update-parent/{parent_id}', [parentsController::class, 'update_parent_with_scope']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/get-parents-no-relations', [parentsController::class, 'get_all_parents_within_a_School_without_relations']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/get-parents-with-relations', [parentsController::class, 'get_all_parents_with_relations_without_scope']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/parent-details/{parent_id}', [parentsController::class, 'get_parent_details']);
});

Route::prefix('student')->group(function () {
    Route::post('/login', [loginstudentcontroller::class, 'login_student']);
    Route::middleware('auth:sanctum')->post('/logout', [logoutstudentcontroller::class, 'logout_parent']);
    Route::middleware('auth:sanctum')->post('/change-password', [StudentChangepasswordController::class, 'change_student_password']);
    Route::post('/reset-password', [resetpasswordController::class, 'reset_password']);
    Route::middleware('auth:sanctum')->post('/auth-student', [getauthenticatedstudentcontroller::class, 'get_authenticated_student']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post('/create-student', [createstudentController::class, 'create_student']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/generate-report-card/{student_id}/{level_id}/{exam_id}', [Reportcardgenerationcontroller::class, 'generate_student_report_card']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum', 'role:schoolSuperAdmin', 'permission:view student'])->get('/get-students', [studentController::class, 'get_all_students_in_school']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/student-details/{student_id}', [studentController::class, 'student_details']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->put('/update-student/{student_id}', [studentController::class, 'update_student_scoped']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->delete('/delete-student/{student_id}', [studentController::class, 'delete_Student_Scoped']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post('/promote-student', [studentpromotionController::class, 'promote_student_to_another_class']);
});

Route::prefix('school-admin')->group(function () {
    Route::post('/login', [loginschooladmincontroller::class, 'login_school_admin']);
    Route::post('/verify-otp', [validateOtpController::class, 'verify_otp']);
    Route::post("/request-otp", [validateOtpController::class,"request_another_code"]);
    Route::post('/register/super-admin', [schooladmincontroller::class,'create_School_admin_on_sign_up']);
    Route::middleware('auth:sanctum')->post('/logout', [logoutschooladmincontroller::class, 'logout_school_admin']);
    Route::middleware('auth:sanctum')->get('/auth-school-admin', [getauthenticatedschoolcontroller::class, 'get_authenticated_school_admin']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->post('/create-school-admin', [createschooladmincontroller::class, 'create_school_admin']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->put('/update-school-admin/{school_admin_id}', [schooladminController::class, 'update_school_admin']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->delete('/delete-school-admin/{school_admin_id}', [schooladminController::class, 'delete_school_admin']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->get('/get-all-school-admins', [schooladminController::class, 'get_all_school_admins_scoped']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get('/school-admin/details/{school_admin_id}', [schooladminController::class, 'school_admin_details']);
});

Route::prefix('teacher')->group(function () {
    Route::post('/login', [loginteachercontroller::class, 'login_teacher']);
    Route::middleware('web')->post('/reset-password', [TeacherResetpasswordController::class, 'reset_password']);
    Route::middleware('auth:sanctum')->post('/change-password', [TeacherChangepasswordController::class, 'change_teacher_password']);
    Route::middleware('auth:sanctum')->post('/logout', [logoutteachercontroller::class, 'logout_teacher']);
    Route::middleware('auth:sanctum')->get('/auth-teacher', [getauthenticatedteachercontroller::class, 'get_authenticated_teacher']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->post('/create-teacher', [createteacherController::class, 'create_teacher']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->delete('/delete-teacher/{teacher_id}', [teacherController::class, 'delete_teacher_scoped']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum', ])->put('/update-teacher/{teacher_id}', [teacherController::class, 'update_teacher_data_scoped']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum', ])->get('/get-all-teachers', [teacherController::class, 'get_all_teachers_not_scoped']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum', ])->get('/teacher-details/{teacher_id}', [teacherController::class, 'get_teacher_details']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->get('/get-teachers-with-relations', [teacherController::class, 'get_all_teachers_with_relations_scoped']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum',])->get('/get-teacher-timetable/{teacher_id}', [teacherController::class, 'get_my_timetable']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->post('/add-specailty-preference/{teacherId}', [teacherController::class, 'assignTeacherSpecailtyPreference']);
    Route::middleware([IdentifyTenant::class, 'auth:sanctum'])->get("/teacher-specailty-preference/{teacherId}", [TeacherSpecailtyPreferenceController::class, 'getTeacherSpecailtyPreference']);
});

Route::prefix('reset-password')->group( function () {
    Route::middleware('web')->post('/request-otp', [Passwordresetcontroller::class, 'request_password_reset_otp']);
    Route::middleware('web')->post('/validate-otp', [Passwordresetcontroller::class, 'verify_otp']);
});

Route::prefix('school')->group(function () {
    Route::post('/register', [schoolsController::class, 'register_school_to_edumanage']);
    Route::middleware(['auth:sanctum'])->put('/update_school', [schoolsController::class, 'update_school']);
    Route::middleware(['auth:sanctum'])->delete('/delete-school/{school_id}', [schoolsController::class, 'delete_school']);
});

Route::prefix('school-branch')->group(function () {
    Route::post('/register', [schoolbranchesController::class, 'create_school_branch']);
    Route::middleware(['auth:sanctum'])->delete('/delete-branch/{branch_id}', [schoolbranchesController::class, 'delete_school_branch']);
    Route::middleware(['auth:sanctum'])->put('/update-branch/{branch_id}', [schoolbranchesController::class, 'update_school_branch']);
    Route::middleware([IdentifyTenant::class,])->get('/my-school-branches', [schoolbranchesController::class, 'get_all_school_branches_scoped']);
    Route::get('/school-branches', [schoolbranchesController::class, 'get_all_schoool_branches']);
});

Route::prefix('country')->group(function (){
    Route::middleware(['auth:sanctum'])->post('/create-country', [countryController::class, 'create_country']);
    Route::get('/countries', [countryController::class, 'get_all_countries']);
    Route::middleware(['auth:sanctum'])->delete('/delete-country/{country_id}', [countryController::class, 'delete_country']);
    Route::middleware(['auth:sanctum'])->put('/update-country/{country_id}', [countryController::class, 'update_country']);
});

Route::middleware([IdentifyTenant::class])->prefix('department')->group(function (){
    Route::middleware(['auth:sanctum'])->post('/create-department', [departmentController::class, 'create_school_department']);
    Route::middleware(['auth:sanctum'])->get('/my-departments', [departmentController::class, 'get_all_school_department_with_school_branches']);
    Route::middleware(['auth:sanctum'])->get('/department-details/{department_id}', [departmentController::class, 'department_details']);
    Route::middleware(['auth:sanctum'])->put('/update-department', [departmentController::class, 'update_school_department']);
    Route::middleware(['auth:sanctum'])->delete('/delete-department/{department_id}', [departmentController::class, 'delete_school_department']);
    Route::middleware(['auth:sanctum'])->get('/get-hods', [HodController::class, 'getHods']);
    Route::middleware(['auth:sanctum'])->delete("/delete-hod/{hodId}", [HodController::class, 'removeHod']);
    Route::middleware(['auth:sanctum'])->post('/assign-hod', [HodController::class, 'assignHeadOfDepartment']);

});

Route::middleware([IdentifyTenant::class])->prefix('course')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-course', [coursesController::class, 'create_course']);
    Route::middleware(['auth:sanctum'])->delete('/delete-course/{course_id}', [coursesController::class, 'delete_course']);
    Route::middleware(['auth:sanctum'])->put('/update-course/{course_id}', [coursesController::class, 'update_course']);
    Route::middleware(['auth:sanctum'])->get('/my-courses', [coursesController::class, 'get_all_courses_with_no_relation']);
    Route::middleware(['auth:sanctum'])->get('/course-details/{course_id}', [coursesController::class, 'courses_details']);
    Route::middleware(['auth:sanctum'])->get('/my-courses/{specialty_id}/{semester_id}', [coursesController::class, 'get_specialty_level_semester_courses']);
});

Route::middleware([IdentifyTenant::class])->prefix('specialty')->group(function (){
    Route::middleware(['auth:sanctum'])->post('/create-specialty', [specialtyController::class, 'create_school_speciality']);
    Route::middleware(['auth:sanctum'])->delete('/delete-specialty/{specialty_id}', [specialtyController::class, 'delete_school_specialty']);
    Route::middleware(['auth:sanctum'])->put('/update-specialty/{specialty_id}', [specialtyController::class, 'update_school_specialty']);
    Route::middleware(['auth:sanctum'])->get('/my-specialties', [specialtyController::class, 'get_all_tenant_School_specailty_scoped']);
    Route::middleware(['auth:sanctum'])->get('/specialty-details/{specialty_id}', [specialtyController::class, 'specialty_details']);
    Route::middleware(['auth:sanctum'])->post("/assign-hos", [HosController::class, 'assignHeadOfSpecialty']);
    Route::middleware(['auth:sanctum'])->get('/get-assigned-hos', [HosController::class, 'getHeadOfSpecialty']);
    Route::middleware(['auth:sanctum'])->delete("/remove-hos", [HosController::class, 'removeHeadOfSpecialty']);
});

Route::middleware([IdentifyTenant::class])->prefix('marks')->group( function (){
    Route::middleware(['auth:sanctum'])->post('/add-student-mark', [marksController::class, 'add_student_mark']);
    Route::middleware(['auth:sanctum'])->put('/update-student-mark/{mark_id}', [marksController::class, 'update_student_mark_scoped']);
    Route::middleware(['auth:sanctum'])->delete('/delete-student-mark/{mark_id}', [marksController::class, 'delete_mark_of_student_scoped']);
    Route::middleware(['auth:sanctum'])->get('/scores-exam/{student_id}/{exam_id}', [marksController::class, 'get_all_student_marks']);
    Route::middleware(['auth:sanctum'])->get("/scores-exam/student", [marksController::class, 'get_all_student_scores']);
    Route::middleware(['auth:sanctum'])->get("/score-details/{mark_id}", [marksController::class, 'get_exam_score_details']);
    Route::middleware(['auth:sanctum'])->get("/accessed-courses/{exam_id}/{student_id}", [marksController::class,"get_exam_score_associated_data"]);
});

Route::middleware([IdentifyTenant::class])->prefix('teacher-avialability')->group( function (){
    Route::middleware(['auth:sanctum'])->post('/create-availability', [instructoravailabilityController::class, 'create_availability']);
    Route::middleware(['auth:sanctum'])->delete('/delete-availability/{availabilty_id}', [instructoravailabilityController::class, 'delete_scoped_teacher_availability']);
    Route::middleware(['auth:sanctum'])->put('/update-availability/{availability_id}', [instructoravailabilityController::class, 'update_teacher_avialability']);
    Route::middleware(['auth:sanctum'])->get('/teacher-avialability/{availability_id}', [instructoravailabilityController::class, 'getteacherAvialability']);
});

Route::prefix('levels')->group( function (){
     Route::middleware(['auth:sanctum'])->post('/create-level', [educationlevelsController::class, 'create_education_levels']);
     Route::middleware(['auth:sanctum'])->put('/update-level/{level_id}', [educationlevelsController::class, 'update_education_levels']);
     Route::middleware(['auth:sanctum'])->delete('/delete-level/{level_id}', [educationlevelsController::class, 'delete_education_levels']);
     Route::middleware(['auth:sanctum'])->get('/education-levels', [educationlevelsController::class, 'get_all_education_leves']);
});


Route::middleware([IdentifyTenant::class])->prefix('grades')->group( function () {
     Route::middleware(['auth:sanctum'])->post('/create-grade', [gradesController::class, 'makeGradeForExamScoped']);
     Route::middleware(['auth:sanctum'])->get('/grades-for-exams', [gradesController::class, 'get_all_grades_scoped']);
     Route::middleware(['auth:sanctum'])->put('/update-grade/{grade_id}', [gradesController::class, 'update_grades_scoped']);
     Route::middleware(['auth:sanctum'])->delete('/delete-grade/{grade_id}', [gradesController::class, 'delete_grades_scoped']);
});


Route::middleware([IdentifyTenant::class])->prefix('exams')->group( function () {
    Route::middleware(['auth:sanctum'])->post('/create-exam', [examsController::class, 'create_exam_scoped']);
    Route::middleware(['auth:sanctum'])->put('/update-exam/{exam_id}', [examsController::class, 'update_exam_scoped']);
    Route::middleware(['auth:sanctum'])->get('/getexams', [examsController::class, 'get_all_exams']);
    Route::middleware(['auth:sanctum'])->get('/exam-details/{exam_id}', [examsController::class, 'get_exam_details']);
    Route::middleware(['auth:sanctum'])->delete('/delete-exams/{exam_id}', [examsController::class, 'delete_school_exam']);
    Route::middleware(['auth:sanctum'])->get('/letter-grades/{exam_id}', [examsController::class,'associateWeightedMarkWithLetterGrades']);
    Route::middleware(['auth:sanctum'])->get("/accessed_exams/{student_id}", [examsController::class,"get_accessed_exams"]);
});


Route::middleware([IdentifyTenant::class])->prefix('exam-timetable')->group( function (){
    Route::middleware(['auth:sanctum'])->post('/create-timetable', [examtimetableController::class, 'create_exam_timetable']);
    Route::middleware(['auth:sanctum'])->put('/update-exam-time-table/{examtimetable_id}', [examtimetableController::class, 'update_exam_time_table_scoped']);
    Route::middleware(['auth:sanctum'])->get('/generate-timetable/{level_id}/{specialty_id}', [examtimetableController::class, 'generate_time_table_for_specialty']);
    Route::middleware(['auth:sanctum'])->delete('/delete/exam-time-table/{examtimetable_id}', [examtimetableController::class, 'delete_exam_time_table_scoped']);
    Route::middleware(['auth:sanctum'])->get('/course-data/{exam_id}', [examtimetableController::class,'prepareExamTimeTableData']);
});

Route::middleware([IdentifyTenant::class])->prefix('time-table')->group( function (){
    Route::middleware(['auth:sanctum'])->post('/create-timetable', [timetableController::class, 'create_time_slots_scoped']);
    Route::middleware(['auth:sanctum'])->put('/update-timetable/{timetable_id}', [timetableController::class, 'update_time_table_record_scoped']);
    Route::middleware(['auth:sanctum'])->delete('/delete-timetable/{timetable_id}', [timetableController::class, 'delete_timetable_scoped']);
    Route::middleware(['auth:sanctum'])->get('/generate-timetable', [timetableController::class, 'generate_time_table_scoped']);
    Route::middleware(['auth:sanctum'])->get('/timetable-details/{entry_id}', [timetableController::class, 'get_timetable_details']);
    Route::middleware(['auth:sanctum'])->get('/instructor-availability/{semester_id}/{specialty_id}', [timetableController::class, 'get_instructor_availability']);
});

Route::prefix('semester')->group( function() {
     Route::middleware(['auth:sanctum'])->post('/create-semester', [semesterController::class, 'create_semester']);
     Route::middleware(['auth:sanctum'])->delete('/delete-semester/{semester_id}', [semesterController::class, 'delete_semester']);
     Route::middleware(['auth:sanctum', IdentifyTenant::class])->get('/semesters', [semesterController::class, 'get_all_semesters']);
     Route::middleware(['auth:sanctum'])->put('/update-semester/{semester_id}', [semesterController::class, 'update_semester']);
});

Route::prefix('school-semesters')->group( function() {
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->post('/create-school-semester', [SchoolSemesterController::class, 'createSchoolSemester']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->put("/update-school-semester/{schoolSemesterId}", [SchoolSemesterController::class, 'updateSchoolSemester']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->get("/school-semeters", [SchoolSemesterController::class, 'getSchoolSemester']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->get("/delete-school-semeter/{schoolSemesterId}", [SchoolSemesterController::class, 'deleteSchoolSemester']);
});

Route::middleware([IdentifyTenant::class])->prefix('event')->group( function () {
     Route::middleware(['auth:sanctum'])->post('/create-event', [eventsController::class, 'create_school_evenT']);
     Route::middleware(['auth:sanctum'])->put('/update-event/{event_id}', [eventsController::class, 'update_school_event']);
     Route::middleware(['auth:sanctum'])->delete('/delete-event/{event_id}', [eventsController::class, 'delete_school_event']);
     Route::middleware(['auth:sanctum'])->get('/school-events', [eventsController::class, 'get_all_events']);
     Route::middleware(['auth:sanctum'])->get("/school-event/details/{event_id}", [eventsController::class,"event_details"]);
});




Route::prefix('exam-type')->group( function (){
    Route::middleware(['auth:sanctum'])->post('/create-exam-type', [Examtypecontroller::class, 'create_exam_type']);
    Route::middleware(['auth:sanctum', IdentifyTenant::class])->get('/exam_types', [Examtypecontroller::class, 'get_all_exam_type']);
    Route::middleware(['auth:sanctum'])->delete('/exam-type/{exam_id}', [Examtypecontroller::class,'delete_exam_type']);
    Route::middleware(['auth:sanctum'])->put('/update-exam-type/{exam_id}', [Examtypecontroller::class, 'update_exam_type']);
});

Route::prefix('letter-grade')->group(  function () {
   Route::middleware(['auth:sanctum'])->post('/create-letter-grade', [letterGradecontroller::class, 'create_letter_grade']);
   Route::middleware(['auth:sanctum'])->get('/get-letter-grades', [letterGradecontroller::class, 'get_all_letter_grades']);
   Route::middleware(['auth:sanctum'])->delete('/delete-letter-grade/{letter_grade_id}', [letterGradecontroller::class, 'delete_letter_grade']);
   Route::middleware(['auth:sanctum'])->put('/update-letter-grate/{letter_grade_id}', [letterGradecontroller::class, 'update_letter_grade']);
});

Route::middleware([IdentifyTenant::class])->prefix('grades-analytics')->group( function () {
   Route::middleware(['auth:sanctum'])->get('/get-risky-subjects', [StudentPerformanceReportController::class, 'high_risk_course_tracking']);
   Route::middleware(['auth:sanctum'])->post('/calculate-desired-gpa', [StudentPerformanceReportController::class, 'calculate_desired_gpa']);

});

Route::prefix('subcription')->group( function () {
   Route::post('/subscribe', [SchoolSubscriptionController::class,'subscribe']);
   Route::get('/subscribed-schools', [SchoolSubscriptionController::class,'get_all_subscribed_schools']);
   Route::get('/subscription-details/{subscription_id}', [SchoolSubscriptionController::class,'subcription_details']);
   Route::post('/create-rate', [RatesCardController::class,'create_rates']);
   Route::put('/update-rate', [RatesCardController::class,'update_rates_card']);
   Route::delete('/delete-rate/{rate_id}', [RatesCardController::class,'delete_rate']);
   Route::get('/rates', [RatesCardController::class,'get_rates']);
   Route::delete('/delete-transaction', [SubscriptionPaymentController::class,'delete_payment']);
   Route::get('/my-transactions/{school_id}', [SubscriptionPaymentController::class,'my_transactions']);
   Route::get('/payment-transactions/{school_id}', [SubscriptionPaymentController::class,'get_all_transactions']);
});


Route::middleware([IdentifyTenant::class])->prefix('school-expenses')->group( function () {
    Route::middleware(['auth:sanctum'])->post('/create-expenses', [SchoolexpensesController::class, 'add_new_expense']);
    Route::middleware(['auth:sanctum'])->delete('/delete-expenses/{expense_id}', [SchoolexpensesController::class, 'delete_expense']);
    Route::middleware(['auth:sanctum'])->get('/my-expenses', [SchoolexpensesController::class, 'get_all_expenses']);
    Route::middleware(['auth:sanctum'])->get('/expenses-details/{expense_id}', [SchoolexpensesController::class, 'expenses_details']);
    Route::middleware(['auth:sanctum'])->put('/update-expenses/{expense_id}', [SchoolexpensesController::class, 'update_expense']);
});

Route::middleware([IdentifyTenant::class])->prefix('school-expenses-category')->group( function () {
    Route::middleware(['auth:sanctum'])->post('/create-category', [Schoolexpensescategorycontroller::class, 'create_category_expenses']);
    Route::middleware(['auth:sanctum'])->delete('/delete-category/{category_expense_id}', [Schoolexpensescategorycontroller::class, 'delete_category_expense']);
    Route::middleware(['auth:sanctum'])->get('/get-category-expenses', [Schoolexpensescategorycontroller::class, 'get_all_category_expenses']);
    Route::middleware(['auth:sanctum'])->put('/update-category/{category_expense_id}', [Schoolexpensescategorycontroller::class, 'update_category_expenses']);
});

Route::middleware([IdentifyTenant::class])->prefix('student-batches')->group( function (){
    Route::middleware(['auth:sanctum'])->post('/create-batch', [Studentbatchcontroller::class, 'create_student_batch']);
    Route::middleware(['auth:sanctum'])->get('/student-batches', [Studentbatchcontroller::class, 'get_all_student_batches']);
    Route::middleware(['auth:sanctum'])->delete('/delete-batch/{batch_id}', [Studentbatchcontroller::class, 'delete_student_batch']);
    Route::middleware(['auth:sanctum'])->put('/update-batch/{batch_id}', [Studentbatchcontroller::class, 'update_student_batch']);
});

Route::middleware([IdentifyTenant::class])->prefix('fee-payment')->group( function (){
    Route::middleware(['auth:sanctum'])->post('/pay-fees', [feepaymentController::class, 'pay_school_fees']);
    Route::middleware(['auth:sanctum'])->get('/paid-fees', [feepaymentController::class, 'get_all_fees_paid']);
    Route::middleware(['auth:sanctum'])->put('/update-payment/{fee_id}', [feepaymentController::class, 'update_student_fee_payment']);
    Route::middleware(['auth:sanctum'])->delete('/delete-payment-record/{fee_id}', [feepaymentController::class, 'delete_fee_payment_record']);
    Route::middleware(['auth:sanctum'])->get('/indebted-students', [feepaymentController::class,'get_all_student_deptors']);
});

Route::middleware([IdentifyTenant::class])->prefix('student-resit')->group( function () {
    Route::middleware(['auth:sanctum'])->get('/get-student-resits/{student_id}', [studentResitController::class, 'get_my_resits']);
    Route::middleware(['auth:sanctum'])->put('/pay-for-resit/{resit_id}', [studentResitController::class, 'pay_for_resit']);
    Route::middleware(['auth:sanctum'])->put('/update-resit-status/{resit_id}', [studentResitController::class, 'update_exam_status']);
    Route::middleware(['auth:sanctum'])->put('/update-resit/{resit_id}', [studentResitController::class, 'update_student_resit']);
    Route::middleware(['auth:sanctum'])->delete('/delete-resit/{resit_id}', [studentResitController::class, 'delete_student_resit_record']);
    Route::middleware(['auth:sanctum'])->post('/resit-timetable', [studentResitController::class, 'create_resit_timetable_entry']);
    Route::middleware(['auth:sanctum'])->get('/student_resits', [studentResitController::class, 'get_student_resits']);
    Route::middleware(['auth:sanctum'])->get('/get-specialty-resit/{specialty_id}/{exam_id}', [ResitcontrollerTimetable::class, 'get_resits_for_specialty']);
    Route::middleware(['auth:sanctum'])->get('/generate-resit-timetable/{exam_id}', [ResitcontrollerTimetable::class, '']);
    Route::middleware(['auth:sanctum'])->get("/details/{resit_id}", [studentResitController::class, 'student_resit_details']);
});

Route::middleware([IdentifyTenant::class])->prefix('elections')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/create-election', [electionsController::class, 'createElection']);
    Route::middleware(['auth:sanctum'])->get('/get-elections', [electionsController::class,'getElections']);
    Route::middleware(['auth:sanctum'])->delete('/delete-election/{election_id}', [electionsController::class,'deleteElection']);
    Route::middleware(['auth:sanctum'])->get('/update-election/{election_id}', [electionsController::class,'updateElection']);
    Route::middleware(['auth:sanctum'])->post('/cast-vote', [electionsController::class, 'vote']);
    Route::middleware(['auth:sanctum'])->get('/election-results/{election_id}', [electionResultsController::class, 'fetchElectionResults']);
    Route::middleware(['auth:sanctum'])->get('/election-candidates/{electionId}', [electionsController::class, 'getElectionCandidates']);
});

Route::middleware([IdentifyTenant::class])->prefix('election-application')->group(function () {
    Route::middleware(['auth:sanctum'])->post('/apply', [electionApplicationController::class, 'createElectionApplication']);
    Route::middleware(['auth:sanctum'])->get('/applications/{election_id}', [electionApplicationController::class,'getApplications']);
    Route::middleware(['auth:sanctum'])->put('/update-application/{application_id}', [electionApplicationController::class,'updateApplication']);
    Route::middleware(['auth:sanctum'])->delete('/delete/{application_id}', [electionApplicationController::class,  'deleteApplication']);
    Route::middleware(['auth:sanctum'])->put('/approve-application/{application_id}', [electionApplicationController::class, 'approveApplication']);
});

Route::middleware([IdentifyTenant::class])->prefix('election-roles')->group( function () {
    Route::middleware(['auth:sanctum'])->post('/create-role', [electionRolesController::class, 'createElectionRole']);
    Route::middleware(['auth:sanctum'])->put('/update-election/{election_role_id}', [electionRolesController::class,'updateElectionRole']);
    Route::middleware(['auth:sanctum'])->delete('/delete-role/{election_role_id}', [electionRolesController::class,'deleteElectionRole']);
    Route::middleware(['auth:sanctum'])->get('/election-roles/{election_id}', [electionRolesController::class, 'getElectionRoles']);
});

Route::middleware([IdentifyTenant::class])->prefix('stats')->group( function() {
   Route::middleware(['auth:sanctum'])->get("/get/financial-stats", [FinancialreportController::class, 'getFinanacialStats']);
});
