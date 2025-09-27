<?php

namespace App\Services;

use App\Models\AccessedStudent;
use App\Exceptions\AppException;
use App\Models\Exams;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AccessedStudentService
{
    public function getAccessedStudents($currentSchool)
    {
        try {
            $accessedStudents = AccessedStudent::where("school_branch_id", $currentSchool->id)
                ->with(['student' => function ($query) {
                    $query->with(['level', 'specialty']);
                }, 'exam.examtype'])
                ->get();

            if ($accessedStudents->isEmpty()) {
                throw new AppException(
                    "No exam candidates found for this school branch.",
                    404,
                    "No Candidates Found",
                    "There are no exam candidates available. Candidates are automatically created when you create an exam.",
                    "/exams"
                );
            }

            return $accessedStudents;
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new AppException(
                "An unexpected error occurred while fetching exam candidates. Please try again later.",
                500,
                "Server Error",
                "We encountered an unexpected issue while retrieving the list of exam candidates.",
                null
            );
        }
    }

    public function deleteAccessedStudent($accessedStudentId)
    {
        try {
            DB::beginTransaction();
            $deleteAccessedStudent = AccessedStudent::findOrFail($accessedStudentId);
            $exam = Exams::findOrFail($deleteAccessedStudent->exam_id);

            $deleteAccessedStudent->delete();
            $exam->decrement('expected_candidate_number');

            DB::commit();

            return true;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new AppException(
                "The student record you are trying to delete was not found.",
                404,
                "Record Not Found",
                "The student or associated exam record does not exist. It may have already been deleted.",
                "/accessed-students"
            );
        } catch (\Exception $e) {
            DB::rollBack();
            throw new AppException(
                "An unexpected error occurred. Please try again later.",
                500,
                "Server Error",
                "We were unable to complete the deletion due to a server error.",
                "/accessed-students"
            );
        }
    }
}
