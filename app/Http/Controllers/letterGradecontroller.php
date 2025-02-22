<?php

namespace App\Http\Controllers;


use App\Services\LetterGradeService;
use App\Http\Requests\LetterGradeRequest;
use App\Http\Requests\UpdateLetterGradeRequest;
use App\Services\ApiResponseService;

class LetterGradecontroller extends Controller
{
    //
     protected LetterGradeService $letterGradeService;
     public function __construct(LetterGradeService $letterGradeService){
        $this->letterGradeService = $letterGradeService;
     }
    public function create_letter_grade(LetterGradeRequest $request){
        $createLetterGrade = $this->letterGradeService->createLetterGrade($request->validated());
        return ApiResponseService::success("Letter grade created Sucessfully", $createLetterGrade, null, 201);
    }

    public function get_all_letter_grades(){
        $letterGrade = $this->letterGradeService->getAllLetterGrades();
        return ApiResponseService::success("Letter grade data fetched sucessfully", $letterGrade, null, 200);
    }

    public function delete_letter_grade($letter_grade_id){
        $deleteLetterGrade = $this->letterGradeService->deleteLetterGrade($letter_grade_id);
        return ApiResponseService::success("Letter Grade Deleted Succefully", $deleteLetterGrade, null, 200);
    }

    public function update_letter_grade(UpdateLetterGradeRequest $request, $letter_grade_id){
        $updateLetterGrade = $this->letterGradeService->updateLetterGrade($letter_grade_id, $request->validated());
        return ApiResponseService::success("Letter Grade Updated Sucessfully", $updateLetterGrade, null, 200);
    }

}
