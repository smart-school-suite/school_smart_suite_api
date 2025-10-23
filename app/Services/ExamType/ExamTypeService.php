<?php

namespace App\Services\ExamType;

use App\Models\Examtype;
use App\Exceptions\AppException;
use Throwable;

class ExamTypeService
{
    public function createExamType(array $data)
    {
        $semesterId = $data["semester_id"];
        $examName = $data["exam_name"];
        $programName = $data["program_name"];

        $existingExamType = ExamType::where('semester_id', $semesterId)
            ->where('exam_name', $examName)
            ->where('program_name', $programName)
            ->first();

        if ($existingExamType) {
            throw new AppException(
                "An exam type with the name '{$examName}' already exists for the program '{$programName}' in semester ID '{$semesterId}'.",
                409,
                "Duplicate Exam Type 📛",
                "This exact exam type configuration (Name, Program, Semester) is already defined. Please ensure uniqueness.",
                null
            );
        }

        try {
            $new_exam_type_instance = new ExamType();
            $new_exam_type_instance->semester_id = $semesterId;
            $new_exam_type_instance->exam_name = $examName;
            $new_exam_type_instance->program_name = $programName;
            $new_exam_type_instance->save();

            return $new_exam_type_instance;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to create exam type '{$examName}'. Error: " . $e->getMessage(),
                500,
                "Creation Failed 🛑",
                "A system error occurred while trying to save the new exam type. Please try again or contact support.",
                null
            );
        }
    }
    public function updateExamType(array $data, $exam_type_id)
    {
        try {
            $findExam = ExamType::find($exam_type_id);

            if (!$findExam) {
                throw new AppException(
                    "Exam type ID '{$exam_type_id}' not found.",
                    404,
                    "Exam Type Not Found 🔎",
                    "The exam type you are trying to update could not be found.",
                    null
                );
            }

            $filterData = array_filter($data);

            if (isset($filterData['exam_name']) || isset($filterData['program_name']) || isset($filterData['semester_id'])) {
                $semesterId = $filterData['semester_id'] ?? $findExam->semester_id;
                $examName = $filterData['exam_name'] ?? $findExam->exam_name;
                $programName = $filterData['program_name'] ?? $findExam->program_name;

                $existingExamType = ExamType::where('semester_id', $semesterId)
                    ->where('exam_name', $examName)
                    ->where('program_name', $programName)
                    ->where('id', '!=', $exam_type_id)
                    ->first();

                if ($existingExamType) {
                    throw new AppException(
                        "The updated exam type configuration (Name, Program, Semester) already exists.",
                        409,
                        "Duplicate Exam Type 📛",
                        "The resulting exam type configuration is not unique. Please change the Name, Program, or Semester ID.",
                        null
                    );
                }
            }

            $findExam->update($filterData);
            return $findExam;
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {

            throw new AppException(
                "Failed to update exam type ID '{$exam_type_id}'. Error: " . $e->getMessage(),
                500,
                "Update Failed 🛑",
                "A system error occurred while trying to save the changes. Please try again or contact support.",
                null
            );
        }
    }
    public function deleteExamType($exam_type_id)
    {
        $examName = 'Unknown Exam Type';
        try {
            $findExam = ExamType::find($exam_type_id);

            if (!$findExam) {
                throw new AppException(
                    "Exam Type ID '{$exam_type_id}' not found for deletion.",
                    404, // Not Found
                    "Exam Type Not Found 🗑️",
                    "The exam type you are trying to delete could not be found. It may have already been deleted.",
                    null
                );
            }

            $examName = $findExam->exam_name;

            $findExam->delete();
            return $findExam;
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            $message = "Failed to delete exam type '{$examName}' (ID: {$exam_type_id}). Error: " . $e->getMessage();

            if (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                throw new AppException(
                    $message,
                    409,
                    "Deletion Blocked 🔒",
                    "Cannot delete the exam type '{$examName}' because it is linked to active exam results or grading configurations. Please remove all associations first.",
                    null
                );
            }

            throw new AppException(
                $message,
                500,
                "Deletion Failed 🛑",
                "A system error occurred during deletion. Please try again or contact support.",
                null
            );
        }
    }
    public function getExamType($currentSchool)
    {
        try {
            $num_semesters = $currentSchool->semester_count;

            $exam_type_data = ExamType::with('semesters')
                ->whereHas('semesters', function ($query) use ($num_semesters) {
                    $query->whereBetween('count', [1, $num_semesters + 1]);
                })->get();

            if ($exam_type_data->isEmpty()) {
                throw new AppException(
                    "No exam types found within the specified semester count range ({$num_semesters}).",
                    404,
                    "No Exam Types Found 📝",
                    "There are no exam types defined for the semesters configured for your school. Please create a new exam type.",
                    null
                );
            }

            return $exam_type_data;
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to retrieve exam types. Error: " . $e->getMessage(),
                500,
                "Data Retrieval Failed 🛑",
                "A system error occurred while trying to load the exam types. Please try again or contact support.",
                null
            );
        }
    }
}
