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
use App\Http\Controllers\gradesController;
use App\Http\Controllers\instructoravailabilityController;
use App\Http\Controllers\marksController;
use App\Http\Controllers\parentsController;
use App\Http\Controllers\Passwordresetcontroller;
use App\Http\Controllers\schooladminController;
use App\Http\Controllers\schoolbranchesController;
use App\Http\Controllers\schoolsController;
use App\Http\Controllers\semesterController;
use App\Http\Controllers\specialtyController;
use App\Http\Controllers\studentController;
use App\Http\Controllers\teacherController;
use App\Http\Controllers\timetableController;
use App\Http\Middleware\IdentifyTenant;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;

Route::prefix('edumanage-admin')->group( function (){
    Route::post('/create-admin', [createeduadmincontroller::class, 'create_edumanage_admin']);
    Route::middleware('auth:sanctum')->post('/login-admin', [logineduadmincontroller::class, 'login_edumanage_admin']);
    Route::middleware('auth:sanctum')->post('/logout-admin', [logouteduadmincontroller::class, 'logout_eduadmin']);
    Route::get('/get-all-admins', [edumanageadminController::class, 'get_all_eduamage_admins']);
    Route::delete('/delete-admin/{edumanage_admin_id}', [edumanageadminController::class, 'delete_edumanage_admin']);
    Route::put('/update-admin/{edumanage_admin_id}', [edumanageadminController::class, 'update_edumanage_admin']);
});

Route::prefix('parent')->group(function () {
    Route::post('/login', [logincontroller::class, 'login_parent']);
    Route::post('/reset-password', [ParentResetpasswordController::class, 'reset_password']);
    Route::middleware('auth:sanctum')->post('/change-password', [changepasswordController::class, 'change_parent_password']);
    Route::middleware('auth:sanctum')->post('/logout', [logoutcontroller::class, 'logout_parent']);
    Route::middleware('auth:sanctum')->post('/auth-parent', [getauthenticatedparentcontroller::class, 'get_authenticated_parent']);
    Route::middleware([IdentifyTenant::class])->post('/create-parent/{school_id}', [createparentController::class, 'create_parent']);
    Route::middleware([IdentifyTenant::class])->delete('/delete-parent/{school_id}/{parent_id}', [parentsController::class, 'delete_parent_with_scope']);
    Route::middleware([IdentifyTenant::class])->put('/update-parent/{school_id}/{parent_id}', [parentsController::class, 'update_parent_with_scope']);
    Route::middleware([IdentifyTenant::class])->get('/get-parents-no-relations/{school_id}', [parentsController::class, 'get_all_parents_within_a_School_without_relations']);
    Route::middleware([IdentifyTenant::class])->get('/get-parents-with-relations/{school_id}', [parentsController::class, 'get_all_parents_with_relations_without_scope']);
});

Route::prefix('student')->group(function () {
    Route::post('/login', [loginstudentcontroller::class, 'login_student']);
    Route::middleware('auth:sanctum')->post('/logout', [logoutstudentcontroller::class, 'logout_parent']);
    Route::middleware('auth:sanctum')->post('/change-password', [StudentChangepasswordController::class, 'change_student_password']);
    Route::post('/reset-password', [resetpasswordController::class, 'reset_password']);
    Route::middleware('auth:sanctum')->post('/auth-student', [getauthenticatedstudentcontroller::class, 'get_authenticated_student']);
    Route::middleware([IdentifyTenant::class])->post('/create-student/{school_id}', [createstudentController::class, 'create_student']);
    Route::middleware([IdentifyTenant::class])->get('/generate-report-card/{student_id}/{level_id}/{exam_id}/{school_id}', [studentController::class, 'generate_student_report_card']);
    Route::middleware([IdentifyTenant::class])->get('/get-students/{school_id}', [studentController::class, 'get_all_students_in_school']);
    Route::middleware([IdentifyTenant::class])->put('/update-student/{student_id}/{school_id}', [studentController::class, 'update_student_scoped']);
    Route::middleware([IdentifyTenant::class])->delete('/delete-student/{school_id}/{student_id}', [studentController::class, 'delete_Student_Scoped']);
});

Route::prefix('school-admin')->group(function () {
    Route::post('/login', [loginschooladmincontroller::class, 'login_school_admin']);
    Route::middleware('auth:sanctum')->post('/logout', [logoutschooladmincontroller::class, 'logout_school_admin']);
    Route::middleware('auth:sanctum')->post('/auth-school-admin', [getauthenticatedschoolcontroller::class, 'get_authenticated_school_admin']);
    Route::middleware([IdentifyTenant::class])->post('/create-school-admin/{school_id}', [createschooladmincontroller::class, 'create_school_admin']);
    Route::middleware([IdentifyTenant::class])->put('/update-school-admin/{school_admin_id}', [schooladminController::class, 'update_school_admin']);
    Route::middleware([IdentifyTenant::class])->delete('/delete-school-admin/{school_id}/{school_admin_id}', [schooladminController::class, 'delete_school_admin']);
    Route::middleware([IdentifyTenant::class])->get('/get-all-school-admins/{school_id}', [schooladminController::class, 'get_all_school_admins_scoped']);
});

Route::prefix('teacher')->group(function () {
    Route::post('/login', [loginteachercontroller::class, 'login_teacher']);
    Route::post('/reset-password', [TeacherResetpasswordController::class, 'reset_password']);
    Route::middleware('auth:sanctum')->post('/change-password', [TeacherChangepasswordController::class, 'change_teacher_password']);
    Route::middleware('auth:sanctum')->post('/logout', [logoutteachercontroller::class, 'logout_teacher']);
    Route::middleware('auth:sanctum')->post('/auth-teacher', [getauthenticatedteachercontroller::class, 'get_authenticated_teacher']);
    Route::middleware([IdentifyTenant::class])->post('/create-teacher/{school_id}', [createteacherController::class, 'create_teacher']);
    Route::middleware([IdentifyTenant::class])->delete('/delete-teacher/{teacher_id}/{school_id}', [teacherController::class, 'delete_teacher_scoped']);
    Route::middleware([IdentifyTenant::class])->put('/update-teacher/{teacher_id}/{school_id}', [teacherController::class, 'update_teacher_data_scoped']);
    Route::middleware([IdentifyTenant::class])->get('/get-all-teachers/{school_id}', [teacherController::class, 'get_all_teachers_Without_relations']);
    Route::middleware([IdentifyTenant::class])->get('/get-teachers-with-relations/{school_id}', [teacherController::class, 'get_all_teachers_with_relations_scoped']);
    Route::middleware([IdentifyTenant::class])->get('/get-teacher-timetable/{school_id}/{teacher_id}', [teacherController::class, 'get_my_timetable']);
});

Route::prefix('reset-password')->group( function () {
     Route::post('/request-otp', [Passwordresetcontroller::class, 'request_password_reset_otp']);
     Route::post('/validate-otp', [Passwordresetcontroller::class, 'verify_otp']);    
});

Route::prefix('school')->group(function () {
    Route::post('/register', [schoolsController::class, 'register_school_to_edumanage']);
    Route::put('/update_school/{school_id}', [schoolsController::class, 'update_school']);
    Route::get('/registered-schools', [schoolsController::class, 'get_all_schools']);
    Route::get('/registerd-schools-branches', [schoolsController::class, 'get_schools_with_branches']);
    Route::delete('/delete-school/{school_id}', [schoolsController::class, 'delete_school']);
});

Route::prefix('school-branch')->group(function () {
    Route::post('/register', [schoolbranchesController::class, 'create_school_branch']);
    Route::delete('/delete-branch/{branch_id}', [schoolbranchesController::class, 'delete_school_branch']);
    Route::put('/update-branch/{branch_id}', [schoolbranchesController::class, 'update_school_branch']);
    Route::middleware([IdentifyTenant::class])->get('/my-school-branches/{school_id}', [schoolbranchesController::class, 'get_all_school_branches_scoped']);
    Route::get('/school-branches', [schoolbranchesController::class, 'get_all_schoool_branches']);
});

Route::prefix('country')->group(function (){
    Route::post('/create-country', [countryController::class, 'create_country']);
    Route::get('/countries', [countryController::class, 'get_all_countries']);
    Route::delete('/delete-country/{country_id}', [countryController::class, 'delete_country']);
    Route::put('/update-country/{country_id}', [countryController::class, 'update_country']);
});

Route::middleware([IdentifyTenant::class])->prefix('department')->group(function (){
    Route::post('/create-department/{school_id}', [departmentController::class, 'create_school_department']);
    Route::get('/my-departments/{school_id}', [departmentController::class, 'get_all_department_without_school_branches']);
    Route::put('/update-department/{school_id}', [departmentController::class, 'update_school_department']);
    Route::delete('/delete-department/{school_id}/{department_id}', [departmentController::class, 'delete_school_department']);
});

Route::middleware([IdentifyTenant::class])->prefix('course')->group(function () {
    Route::post('/create-course/{school_id}', [coursesController::class, 'create_course']);
    Route::delete('/delete-course/{school_id}/{course_id}', [coursesController::class, 'delete_course']);
    Route::put('/update-course/{school_id}/{course_id}', [coursesController::class, 'update_course']);
    Route::get('/my-courses/{school_id}', [coursesController::class, 'get_all_courses_with_no_relation']);
});

Route::middleware([IdentifyTenant::class])->prefix('specialty')->group(function (){
    Route::post('/create-specialty/{school_id}', [specialtyController::class, 'create_school_speciality']);
    Route::delete('/delete-specialty/{school_id}/{specialty_id}', [specialtyController::class, 'delete_school_specialty']);
    Route::put('/update-specialty/{school_id}/{specialty_id}', [specialtyController::class, 'update_school_specialty']);
    Route::get('/my-specialties/{school_id}', [specialtyController::class, 'get_all_tenant_School_specailty_scoped']);
});

Route::middleware([IdentifyTenant::class])->prefix('marks')->group( function (){
    Route::post('/add-student-mark/{school_id}', [marksController::class, 'add_student_mark']);
    Route::put('/update-student-mark/{school_id}/{mark_id}', [marksController::class, 'update_student_mark_scoped']);
    Route::delete('/delete-student-mark/{school_id}/{mark_id}', [marksController::class, 'delete_mark_of_student_scoped']);
});

Route::middleware([IdentifyTenant::class])->prefix('teacher-avialability')->group( function (){
    Route::post('/create-availability/{school_id}', [instructoravailabilityController::class, 'create_availability']);
    Route::delete('/delete-availability/{school_id}/{availabilty_id}', [instructoravailabilityController::class, 'delete_scoped_teacher_availability']);
    Route::put('/update-availability/{school_id}/{availability_id}', [instructoravailabilityController::class, 'update_teacher_avialability']);
    Route::get('/teacher-avialability/{school_id/{availability_id}', [instructoravailabilityController::class, 'get_all_avialability_not_scoped']);
});

Route::prefix('levels')->group( function (){
     Route::post('/create-level', [educationlevelsController::class, 'create_education_levels']);
     Route::put('/update-level/{level_id}', [educationlevelsController::class, 'update_education_levels']);
     Route::delete('/delete-level/{level_id}', [educationlevelsController::class, 'delete_education_levels']);
     Route::get('/education-levels', [educationlevelsController::class, 'get_all_education_leves']);  
});


Route::middleware([IdentifyTenant::class])->prefix('grades')->group( function () {
     Route::post('/create-grade/{school_id}', [gradesController::class, 'make_grade_for_exam_scoped']);
     Route::get('/grades-for-exams/{school_id}', [gradesController::class, 'get_all_grades_scoped']);
     Route::put('/update-grade/{school_id}/{grade_id}', [gradesController::class, 'update_grades_scoped']);
     Route::delete('/delete-grade/{school_id}/{grade_id}', [gradesController::class, 'delete_grades_scoped']);
});


Route::middleware([IdentifyTenant::class])->prefix('exams')->group( function () {
    Route::post('/create-exam/{school_id}', [examsController::class, 'create_exam_scoped']);
    Route::put('/update-exam/{exam_id}/{school_id}', [examsController::class, 'update_exam_scoped']);
    Route::get('/getexams/{school_id}', [examsController::class, 'get_all_exams']);
    Route::delete('/delete-exams/{school_id}/{exam_id}', [examsController::class, 'delete_school_exam']);
});


Route::middleware([IdentifyTenant::class])->prefix('exam-timetable')->group( function (){
    Route::post('/create-timetable/{school_id}', [examtimetableController::class, 'create_exam_timetable']);
    Route::put('/update-exam-time-table/{school_id}/{examtimetable_id}', [examtimetableController::class, 'update_exam_time_table_scoped']);
    Route::get('/generate-timetable/{school_id}/{level_id}/{specialty_id}', [examtimetableController::class, 'generate_time_table_for_specialty']);
    Route::delete('/delete/exam-time-table/{school_id}/{examtimetable_id}', [examtimetableController::class, 'delete_exam_time_table_scoped']);
});

Route::middleware([IdentifyTenant::class])->prefix('time-table')->group( function (){
    Route::post('/create-timetable/{school_id}', [timetableController::class, 'create_time_slots_scoped']);
    Route::put('/update-timetable/{school_id}/{timetable_id}', [timetableController::class, 'update_time_table_record_scoped']);
    Route::delete('/delete-timetable/{school_id}/{timetable_id}', [timetableController::class, 'delete_timetable_scoped']);
    Route::get('/generate-timetable/{school_id}/{level_id}/{specailty_id}', [timetableController::class, 'generate_time_table_scoped']);
});

Route::prefix('semester')->group( function() {
     Route::post('/create-semester', [semesterController::class, 'create_semester']);
     Route::delete('/delete-semester/{semester_id}', [semesterController::class, 'delete_semester']);
     Route::get('/semesters', [semesterController::class, 'get_all_semesters']);
     Route::put('/update-semester/{semester_id}', [semesterController::class, 'update_semester']);
});

Route::middleware([IdentifyTenant::class])->prefix('event')->group( function () {
     Route::post('/create-event/{school_id}', [eventsController::class, 'create_semester']);
     Route::put('/update-event/{event_id}/{school_id}', [eventsController::class, 'update_school_event']);
     Route::delete('/delete-event/{event_id}/{school_id}', [eventsController::class, 'delete_school_event']);
     Route::get('/school-events/{school_id}', [eventsController::class, 'et_all_events']);
});

