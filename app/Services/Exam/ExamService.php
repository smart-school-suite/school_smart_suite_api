<?php

namespace App\Services\Exam;

use App\Jobs\DataCreationJob\CreateExamCandidateJob;
use App\Jobs\NotificationJobs\SendAdminExamCreatedNotificationJob;
use App\Models\Exams;
use Illuminate\Support\Str;
use App\Models\LetterGrade;
use App\Models\SchoolGradesConfig;
use App\Models\AccessedStudent;
use App\Models\Examtype;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Specialty;
use Carbon\Carbon;
use Exception;
use App\Exceptions\AppException;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ExamService
{
    public function createExam(array $data, $currentSchool)
    {
        try {
            $specialty = Specialty::with(['level'])->find($data['specialty_id']);
            if (!$specialty) {
                throw new AppException(
                    "Specialty Not Found",
                    404,
                    "Specialty Not Found",
                    "Specialty Not Found Check to ensure that specialty exist and is not accidentally deleted",
                    null
                );
            }
            $examType = Examtype::find($data['exam_type_id']);
            if (!$examType) {
                throw new AppException(
                    "Exam Type Not Found",
                    404,
                    "Exam Type Not Found",
                    "Exam Type Not Found Please ensure that this exam type exist and its not accidentally deleted",
                    null
                );
            }

            $existingExam = Exams::where("school_branch_id", $currentSchool->id)
                ->where("exam_type_id", $data['exam_type_id'])
                ->where("specialty_id", $specialty->id)
                ->where("level_id", $specialty->level_id)
                ->where("student_batch_id", $data['student_batch_id'])
                ->first();
            if ($existingExam) {
                throw new AppException(
                    "Duplicate Exam Details",
                    409,
                    "Duplicate Exam Credentials",
                    "Your Trying to create and exam that already exist, please check exam details and try again"
                );
            }
            $examId = Str::uuid();
            $exam = new Exams();
            $exam->id = $examId;
            $exam->school_branch_id = $currentSchool->id;
            $exam->start_date = $data["start_date"];
            $exam->end_date = $data["end_date"];
            $exam->level_id = $specialty->level_id;
            $exam->exam_type_id = $examType->id;
            $exam->weighted_mark = $data["weighted_mark"];
            $exam->semester_id = $examType->semester_id;
            $exam->school_year = $data["school_year"];
            $exam->specialty_id = $specialty->id;
            $exam->student_batch_id = $data["student_batch_id"];
            $exam->save();
            $examData =  [
                'specialty' => $specialty->specialty_name,
                'level' => $specialty->level->name,
                'startDate' => Carbon::parse($data['start_date'])->format('l, F j, Y'),
                'endDate' => Carbon::parse($data['end_date'])->format('l, F j, Y'),
                'school_year' => $data['school_year'],
                'semester' => Semester::find($examType->semester_id)->name,
                'examName' => $examType->exam_name
            ];
            CreateExamCandidateJob::dispatch(
                $data['specialty_id'],
                $specialty->level_id,
                $data['student_batch_id'],
                $examId
            );
            SendAdminExamCreatedNotificationJob::dispatch(
                $currentSchool->id,
                $examData
            );
            return $exam;
        } catch (Exception $e) {
            throw new AppException(
                $e->getMessage(),
                500,
                "Server Error",
                "An error occurred while creating the exam. Please try again later.",
                null
            );
        }
    }
    public function deleteExam(string $examId, Object $currentSchool)
    {
        try {

            $exam = Exams::where("school_branch_id", $currentSchool->id)
                ->findorFail($examId);
            $this->deleteExamCandidate($examId, $currentSchool);

            $exam->delete();

            return $exam;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "The exam you are trying to delete was not found. Please verify the exam ID.",
                404,
                "Exam Not Found",
                "We could not find the exam associated with the provided ID. It may have already been deleted.",
                null
            );
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while deleting the exam. Please try again later.",
                500,
                "Deletion Error",
                "We encountered a server-side issue while attempting to delete the exam.",
                $e->getMessage()
            );
        }
    }
    private function deleteExamCandidate($examId, $currentSchool)
    {
        $examCandidates = AccessedStudent::where("school_branch_id", $currentSchool)
            ->where("exam_id", $examId)
            ->get();
        foreach ($examCandidates as $examCandidate) {
            $examCandidate->delete();
        }
    }
    public function bulkDeleteExam(array $examIds, $currentSchool): array
    {
        $deletedExams = [];

        try {
            DB::beginTransaction();

            foreach ($examIds as $examIdItem) {
                $examId = $examIdItem['exam_id'] ?? null;
                $exam = Exams::where("school_branch_id", $currentSchool->id)
                    ->findOrFail($examId);
                $this->deleteExamCandidate($examId, $currentSchool);
                $exam->delete();
                $deletedExams[] = $exam;
            }

            DB::commit();

            return $deletedExams;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new AppException(
                "One or more exams you tried to delete were not found. Please verify the IDs and try again.",
                404,
                "Exams Not Found",
                "We could not find one or more exams associated with the provided IDs. They may have already been deleted.",
                null
            );
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "An unexpected error occurred while deleting the exams. Please try again.",
                500,
                "Deletion Error",
                "We encountered an issue while trying to delete the exams due to a server-side problem.",
                null
            );
        }
    }
    public function updateExam(string $examId, $currentSchool, array $data)
    {
        try {
            $exam = Exams::where("school_branch_id", $currentSchool->id)
                ->find($examId);

            if (!$exam) {
                throw new AppException(
                    "The exam you are trying to update was not found.",
                    404,
                    "Exam Not Found",
                    "We could not find the exam with the provided ID for this school. Please verify the ID and try again.",
                    null
                );
            }

            $filteredData = array_filter($data);

            $exam->update($filteredData);

            return $exam;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while updating the exam. Please try again later.",
                500,
                "Update Error",
                "We encountered a server-side issue while attempting to update the exam. " . $e->getMessage(),
                null
            );
        }
    }
    public function bulkUpdateExam($examUpdateList)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($examUpdateList as $examUpdate) {
                $exam = Exams::findOrFail($examUpdate['exam_id']);
                $filterData = array_filter($examUpdate);
                $exam->update($filterData);
                $result[] = [
                    $exam
                ];
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getExams($currentSchool)
    {
        $exams = Exams::where('school_branch_id', $currentSchool->id)
            ->with(['examtype', 'semester', 'specialty', 'level', 'studentBatch'])
            ->get();

        if ($exams->isEmpty()) {
            throw new AppException(
                "There are no exams available for this school branch yet.",
                404,
                "No Exams Found",
                "We could not find any exams associated with your school branch. Please try creating one first.",
                null
            );
        }

        return $exams;
    }
    public function examDetails($currentSchool, string $examId)
    {
        $exam = Exams::where("school_branch_id", $currentSchool->id)
            ->with(['examtype', 'semester', 'specialty', 'level', 'studentBatch'])
            ->find($examId);

        if (!$exam) {
            throw new AppException(
                "The exam you are looking for was not found.",
                404,
                "Exam Not Found",
                "We could not find an exam with the provided ID for this school. Please verify the ID and try again.",
                null
            );
        }

        return $exam;
    }
    public function getAssociateWeightedMarkLetterGrades(string $examId, $currentSchool)
    {
        $results = [];

        $exam = Exams::where("school_branch_id", $currentSchool->id)
            ->with(["examtype"])
            ->find($examId);

        if (!$exam) {
            throw new AppException(
                "The exam you are looking for was not found.",
                404,
                "Exam Not Found",
                "We could not find an exam with the provided ID for this school. Please verify the ID and try again.",
                null
            );
        }

        $letterGrades = LetterGrade::all();

        if ($letterGrades->isEmpty()) {
            throw new AppException(
                "No letter grades have been configured for the system.",
                500,
                "Grades Configuration Missing",
                "The system requires letter grades to be configured before you can associate them with exam marks. Please contact support.",
                null
            );
        }

        foreach ($letterGrades as $letterGrade) {
            $results[] = [
                "letter_grade" => $letterGrade,
                "exam" => $exam,
            ];
        }

        return $results;
    }
    public function addExamGrading(string $examId, $currentSchool, $gradesConfigId)
    {

        $gradesConfig = SchoolGradesConfig::where("school_branch_id", $currentSchool->id)
            ->find($gradesConfigId);

        if (!$gradesConfig) {
            throw new AppException(
                "The selected grading configuration was not found.",
                404,
                "Grading Configuration Not Found",
                "We could not find the specified grading configuration for this school. Please verify the ID and try again.",
                null
            );
        }

        if ($gradesConfig->isgrades_configured == false) {
            throw new AppException(
                "The selected grading configuration has not been set up completely.",
                400,
                "Incomplete Configuration",
                "You cannot apply an incomplete grading configuration to an exam. Please complete the setup and try again.",
                null
            );
        }

        $exam = Exams::where("school_branch_id", $currentSchool->id)
            ->find($examId);

        if (!$exam) {
            throw new AppException(
                "The exam you are trying to grade was not found.",
                404,
                "Exam Not Found",
                "We could not find the exam with the provided ID for this school. Please verify the ID and try again.",
                null
            );
        }

        $exam->grades_category_id = $gradesConfig->grades_category_id;
        $exam->grading_added = true;
        $exam->save();

        return $exam;
    }
    public function bulkAddExamGrading($examGradingList, $currentSchool)
    {
        $result = [];
        try {
            DB::beginTransaction();

            foreach ($examGradingList as $examGrading) {
                $gradesConfigId = $examGrading['grades_config_Id'] ?? null;
                $examId = $examGrading['exam_id'] ?? null;

                $gradesConfig = SchoolGradesConfig::where("school_branch_id", $currentSchool->id)
                    ->find($gradesConfigId);

                if (!$gradesConfig) {
                    throw new AppException(
                        "A grading configuration was not found for one of the exams.",
                        404,
                        "Grading Configuration Not Found",
                        "We could not find the specified grading configuration for exam ID: {$examId}.",
                        null
                    );
                }

                if ($gradesConfig->isgrades_configured === false) {
                    throw new AppException(
                        "The selected grading configuration is incomplete.",
                        400,
                        "Incomplete Configuration",
                        "The grading configuration with ID: {$gradesConfigId} has not been fully set up.",
                        null
                    );
                }

                $exam = Exams::where("school_branch_id", $currentSchool->id)->find($examId);

                if (!$exam) {
                    throw new AppException(
                        "An exam was not found.",
                        404,
                        "Exam Not Found",
                        "We could not find the exam with ID: {$examId}.",
                        null
                    );
                }

                $exam->grades_category_id = $gradesConfig->grades_category_id;
                $exam->grading_added = true;
                $exam->save();

                $result[] = [
                    'grades_config' => $gradesConfig,
                    'exam' => $exam,
                ];
            }

            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "An unexpected error occurred during the bulk grading assignment. Please try again.",
                500,
                "Bulk Grading Error",
                "A server-side issue prevented the grading from being applied to all exams. Error: " . $e->getMessage(),
                null
            );
        }
    }
}
