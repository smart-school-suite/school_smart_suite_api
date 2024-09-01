<?php

use App\Http\Controllers\Auth\Parent\createparentController;
use App\Http\Controllers\Auth\Parent\getauthenticatedparentcontroller;
use App\Http\Controllers\Auth\Parent\logincontroller;
use App\Http\Controllers\Auth\Parent\logoutcontroller;
use App\Http\Controllers\Auth\SchoolAdmin\createschooladmincontroller;
use App\Http\Controllers\Auth\SchoolAdmin\getauthenticatedschoolcontroller;
use App\Http\Controllers\Auth\SchoolAdmin\loginschooladmincontroller;
use App\Http\Controllers\Auth\SchoolAdmin\logoutschooladmincontroller;
use App\Http\Controllers\Auth\Student\createstudentController;
use App\Http\Controllers\Auth\Student\getauthenticatedstudentcontroller;
use App\Http\Controllers\Auth\Student\loginstudentcontroller;
use App\Http\Controllers\Auth\Student\logoutstudentcontroller;
use App\Http\Controllers\Auth\Teacher\createteacherController;
use App\Http\Controllers\Auth\Teacher\getauthenticatedteachercontroller;
use App\Http\Controllers\Auth\Teacher\loginteachercontroller;
use App\Http\Controllers\Auth\Teacher\logoutteachercontroller;
use App\Http\Controllers\schoolbranchesController;
use App\Http\Controllers\schoolsController;
use App\Http\Middleware\IdentifyTenant;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;

Route::prefix('parent')->group(function () {
    Route::post('/login', [logincontroller::class, 'login_parent']);
    Route::middleware('auth:sanctum')->post('/logout', [logoutcontroller::class, 'logout_parent']);
    Route::middleware('auth:sanctum')->post('/auth-parent', [getauthenticatedparentcontroller::class, 'get_authenticated_parent']);
    Route::post('/create-parent/{school-id}', [createparentController::class, 'create_parent']);
});

Route::prefix('student')->group(function () {
    Route::post('/login', [loginstudentcontroller::class, 'login_student']);
    Route::middleware('auth:sanctum')->post('/logout', [logoutstudentcontroller::class, 'logout_parent']);
    Route::middleware('auth:sanctum')->post('/auth-student', [getauthenticatedstudentcontroller::class, 'get_authenticated_student']);
    Route::post('/create-student/{school-id}', [createstudentController::class, 'create_student']);
});

Route::prefix('school-admin')->group(function () {
    Route::post('/login', [loginschooladmincontroller::class, 'login_school_admin']);
    Route::middleware('auth:sanctum')->post('/logout', [logoutschooladmincontroller::class, 'logout_school_admin']);
    Route::middleware('auth:sanctum')->post('/auth-school-admin', [getauthenticatedschoolcontroller::class, 'get_authenticated_school_admin']);
    Route::post('/create-school-admin/{school-branch-id}', [createschooladmincontroller::class, 'create_school_admin']);
});

Route::prefix('teacher')->group(function () {
    Route::post('/login', [loginteachercontroller::class, 'login_teacher']);
    Route::middleware('auth:sanctum')->post('/logout', [logoutteachercontroller::class, 'logout_teacher']);
    Route::middleware('auth:sanctum')->post('/auth-teacher', [getauthenticatedteachercontroller::class, 'get_authenticated_teacher']);
    Route::post('/create-teacher/{school-branch-id}', [createteacherController::class, 'create_teacher']);
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
    Route::middleware([IdentifyTenant::class])->get('/my-school-branches/{school-branch-id}', [schoolbranchesController::class, 'get_all_school_branches_scoped']);
    Route::get('/school-branches', [schoolbranchesController::class, 'get_all_schoool_branches']);
});

