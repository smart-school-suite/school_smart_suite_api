<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolBranchesController;
use App\Http\Middleware\IdentifyTenant;

Route::post('/register', [SchoolBranchesController::class, 'createSchoolBranch']);
Route::middleware(['auth:sanctum'])->delete('/delete-branch/{branch_id}', [SchoolBranchesController::class, 'deleteSchoolBranch']);
Route::middleware(['auth:sanctum'])->put('/update-branch/{branch_id}', [SchoolBranchesController::class, 'updateSchoolBranch']);
Route::middleware([IdentifyTenant::class,])->get('/my-school-branches', [SchoolBranchesController::class, 'getAllSchoolBranches']);
