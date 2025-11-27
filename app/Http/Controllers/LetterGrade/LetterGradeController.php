<?php

namespace App\Http\Controllers\LetterGrade;

use App\Http\Controllers\Controller;
use App\Http\Requests\LetterGrade\CreateLetterGradeRequest;
use App\Http\Requests\LetterGrade\UpdateLetterGradeRequest;
use App\Services\ApiResponseService;
use App\Services\Grade\LetterGradeService;
class LetterGradeController extends Controller
{
         protected LetterGradeService $letterGradeService;
     public function __construct(LetterGradeService $letterGradeService){
        $this->letterGradeService = $letterGradeService;
     }
    public function createLettGrade(CreateLetterGradeRequest $request){
        $createLetterGrade = $this->letterGradeService->createLetterGrade($request->validated());
        return ApiResponseService::success("Letter grade created Sucessfully", $createLetterGrade, null, 201);
    }

    public function getLetterGrades(){
        $letterGrade = $this->letterGradeService->getAllLetterGrades();
        return ApiResponseService::success("Letter grade data fetched sucessfully", $letterGrade, null, 200);
    }

    public function deleteLetterGrade($letterGradeId){
        $deleteLetterGrade = $this->letterGradeService->deleteLetterGrade($letterGradeId);
        return ApiResponseService::success("Letter Grade Deleted Succefully", $deleteLetterGrade, null, 200);
    }

    public function updateLetterGrade(UpdateLetterGradeRequest $request, $letterGradeId){
        $updateLetterGrade = $this->letterGradeService->updateLetterGrade($letterGradeId, $request->validated());
        return ApiResponseService::success("Letter Grade Updated Sucessfully", $updateLetterGrade, null, 200);
    }
}
