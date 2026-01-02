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
use App\Models\ResitExam;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Grades;
use App\Jobs\DataCreationJob\UpdateExamStatusJob;
use App\Events\Actions\AdminActionEvent;
use App\Events\Actions\StudentActionEvent;
use App\Events\Analytics\AcademicAnalyticsEvent;
use App\Constant\Analytics\Academic\AcademicAnalyticsEvent as AcademicEvent;
class ExamService
{
    public function createExam(array $data, $currentSchool, $authAdmin)
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
            $exam->result_released = false;
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
            UpdateExamStatusJob::dispatch($examId)->delay(Carbon::parse($data['end_date']));
            UpdateExamStatusJob::dispatch($examId)->delay(Carbon::parse($data['start_date']));
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.exam.create"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "examManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $exam,
                    "message" => "Exam Created",
                ]
            );
            StudentActionEvent::dispatch([
                'schoolBranch' => $currentSchool->id,
                'specialtyIds'   => [$specialty->id],
                'feature'      => 'examCreate',
                'message'      => 'Exam Created',
                'data'         => $exam,
            ]);
            event(new AcademicAnalyticsEvent(
                 eventType:AcademicEvent::EXAM_CREATED,
                 version:1,
                 payload:[
                    "school_branch_id" => $currentSchool->id,
                    "specialty_id" => $specialty->id,
                    "department_id" => $specialty->department_id,
                    "level_id" => $specialty->level_id,
                    "value" => 1
                 ]
            ));
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
    public function deleteExam(string $examId, Object $currentSchool, $authAdmin)
    {
        try {

            $exam = Exams::where("school_branch_id", $currentSchool->id)
                ->findorFail($examId);
            $this->deleteExamCandidate($examId, $currentSchool);

            $exam->delete();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.exam.delete"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "examManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $exam,
                    "message" => "Exam Deleted",
                ]
            );
            StudentActionEvent::dispatch([
                'schoolBranch' => $currentSchool->id,
                'specialtyIds'   => [$exam->specialty_id],
                'feature'      => 'examDelete',
                'message'      => 'Exam  Deleted',
                'data'         => $exam,
            ]);
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
    public function bulkDeleteExam(array $examIds, $currentSchool, $authAdmin): array
    {
        $deletedExams = [];

        try {
            DB::beginTransaction();
            $specialtyIds = [];
            foreach ($examIds as $examIdItem) {
                $examId = $examIdItem['exam_id'] ?? null;
                $exam = Exams::where("school_branch_id", $currentSchool->id)
                    ->findOrFail($examId);
                $specialtyIds[] = $exam->specialty_id;
                $this->deleteExamCandidate($examId, $currentSchool);
                $exam->delete();
                $deletedExams[] = $exam;
            }

            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.exam.delete"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "examManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $deletedExams,
                    "message" => "Exam Deleted",
                ]
            );
            StudentActionEvent::dispatch([
                'schoolBranch' => $currentSchool->id,
                'specialtyIds'   => $specialtyIds,
                'feature'      => 'examDelete',
                'message'      => 'Exam  Deleted',
                'data'         => $deletedExams,
            ]);
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
    public function updateExam(string $examId, $currentSchool, array $data, $authAdmin)
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
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.exam.update"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "examManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $exam,
                    "message" => "Exam Updated",
                ]
            );
            StudentActionEvent::dispatch([
                'schoolBranch' => $currentSchool->id,
                'specialtyIds'   => [$exam->specialty_id],
                'feature'      => 'examUpdate',
                'message'      => 'Exam Updated',
                'data'         => $exam,
            ]);
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
    public function bulkUpdateExam($examUpdateList, $currentSchool, $authAdmin)
    {
        $result = [];
        $specialtyIds = [];
        try {
            DB::beginTransaction();
            foreach ($examUpdateList as $examUpdate) {
                $exam = Exams::where("school_branch_id", $currentSchool->id)
                    ->findOrFail($examUpdate['exam_id']);
                $filterData = array_filter($examUpdate);
                $exam->update($filterData);
                $specialtyIds[] = $exam->specialty_id;
                $result[] = [
                    $exam
                ];
            }
            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.exam.update"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "examManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $result,
                    "message" => "Exam Updated",
                ]
            );
            StudentActionEvent::dispatch([
                'schoolBranch' => $currentSchool->id,
                'specialtyIds'   => $specialtyIds,
                'feature'      => 'examUpdate',
                'message'      => 'Exam Updated',
                'data'         => $exam,
            ]);
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
    public function addExamGrading(string $examId, $currentSchool, $gradesConfigId, $authAdmin)
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
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.exam.add.grade.config"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "examManagement",
                "authAdmin" => $authAdmin,
                "data" => $exam,
                "message" => "Exam Grade Scale Added",
            ]
        );
        StudentActionEvent::dispatch([
            'schoolBranch' => $currentSchool->id,
            'specialtyIds'   => [$exam->specialty_id],
            'feature'      => 'examGradeScale',
            'message'      => 'Exam Grade Scale Added',
            'data'         => $exam,
        ]);
        return $exam;
    }
    public function bulkAddExamGrading($examGradingList, $currentSchool, $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();
            $specialtyIds = [];
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
                $specialtyIds[] = $exam->specialty_id;
                $result[] = [
                    'grades_config' => $gradesConfig,
                    'exam' => $exam,
                ];
            }
            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.exam.add.grade.config"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "examManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $result,
                    "message" => "Exam Grade Scale Added",
                ]
            );
            StudentActionEvent::dispatch([
                'schoolBranch' => $currentSchool->id,
                'specialtyIds'   => $specialtyIds,
                'feature'      => 'examGradeScale',
                'message'      => 'Exam Grade Scale Added',
                'data'         => $exam,
            ]);
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
    public function getExamsByStudentIdSemesterId($currentSchool, $studentId, $semesterId)
    {
        $student = Student::where('school_branch_id', $currentSchool->id)
            ->findOrFail($studentId);
        $examQueryConstraints = [
            ['school_branch_id', $currentSchool->id],
            ['specialty_id', $student->specialty_id],
            ['level_id', $student->level_id],
            ['semester_id', $semesterId],
        ];

        $relationshipsToLoad = ['semester', 'examtype', 'specialty', 'level'];

        $exams = Exams::where($examQueryConstraints)
            ->with($relationshipsToLoad)
            ->get();

        $resitExams = ResitExam::where($examQueryConstraints)
            ->with($relationshipsToLoad)
            ->get();

        $allExams = $exams->merge($resitExams);

        $examItems = $allExams->map(function ($exam) {
            $item = [
                "exam_name"         => $exam->examtype->exam_name ?? null,
                "exam_id"           => $exam->id,
                "level_id"          => $exam->level_id,
                "level_name"        => $exam->level?->name ?? null,
                "specialty_id"      => $exam->specialty_id,
                "specialty_name"    => $exam->specialty?->specialty_name ?? null,
                "description"       => $exam->examtype->description ?? null,
                "exam_type_id"      => $exam->exam_type_id,
                "student_batch_id"  => $exam->student_batch_id,
                "timetable_created" => (bool) $exam->timetable_published,
                "result_released"   => (bool) $exam->result_released,
                "start_date"        => $exam->start_date,
                "end_date"          => $exam->end_date,
                "result_message"    => null,
            ];

            if ($exam->result_released == true) {
                $item["result_message"] = [
                    "id"    => (string) Str::uuid(),
                    "title" => ($exam->examtype->exam_name ?? 'Exam') . " Results Are Out!",
                    "body"  => "Well done on completing the exams! Your results are ready.",
                ];
            }

            return $item;
        });

        $semester = Semester::find($semesterId);

        $result = [
            "semester"   => $semester?->name ?? "Semester",
            "semesterId" => $semesterId,
            "exams"      => $examItems->values()->all()
        ];

        return $result;
    }
    public function getExamGradeScale(string $examId, $currentSchool)
    {
        $exam = Exams::where('school_branch_id', $currentSchool->id)
            ->with([
                'examtype',
                'semester',
                'level',
                'specialty'
            ])
            ->findOrFail($examId);

        $grades = Grades::where('school_branch_id', $currentSchool->id)
            ->where('grades_category_id', $exam->grades_category_id)
            ->with('lettergrade')
            ->orderBy('minimum_score', 'desc')
            ->get();

        $gradeScale = $grades->map(function ($grade) {
            return [
                "id"             => $grade->id,
                "minimum_score"  => (float) $grade->minimum_score,
                "maximum_score"  => (float) $grade->maximum_score,
                "grade_status"   => $grade->grade_status ?? "N/A",
                "grade"          => $grade->lettergrade?->letter_grade ?? "N/A",
                "determinant"    => $grade->determinant ?? "N/A",
                "grade_points"  => (float) $grade->grade_points,
            ];
        })->values();

        $result = [
            "exam" => [
                "exam_id"         => $exam->id,
                "exam_name"       => $exam->examtype?->exam_name ?? "Unnamed Exam",
                "exam_type"       => $exam->examtype?->type ?? "Unnamed Type",
                "semester"        => $exam->semester?->name ?? "Unknown Semester",
                "semester_id"     => $exam->semester_id,
                "level_name"      => $exam->level?->name ?? "N/A",
                "specialty_name"  => $exam->specialty?->specialty_name ?? "N/A",
            ],
            "grade_scale" => $gradeScale
        ];

        return $result;
    }
    public function getUpcomingExams($currentSchool, $student)
    {
        $student = Student::where('school_branch_id', $currentSchool->id)
            ->find($student->id);

        if (!$student) {
            throw new AppException(
                "Student Not Found",
                404,
                "Student Not Found",
                "Student Not Found. Please check to ensure that the student has not been accidentally deleted or dropped."
            );
        }

        $now = Carbon::now();


        $regularExams = Exams::where('school_branch_id', $currentSchool->id)
            ->where('specialty_id', $student->specialty_id)
            ->where('level_id', $student->level_id)
            ->where('end_date', '>=', $now)
            ->with(['examtype', 'semester'])
            ->select('id', 'exam_type_id', 'start_date', 'end_date', 'timetable_published', 'semester_id')
            ->get();

        $resitExams = ResitExam::where('school_branch_id', $currentSchool->id)
            ->where('specialty_id', $student->specialty_id)
            ->where('level_id', $student->level_id)
            ->where('end_date', '>=', $now)
            ->with(['examtype', 'semester'])
            ->select('id', 'exam_type_id', 'start_date', 'end_date', 'timetable_published', 'semester_id')
            ->get();

        $allUpcoming = collect();

        $regularExams->each(function ($exam) use ($allUpcoming) {
            $allUpcoming->push([
                "exam_id"            => $exam->id,
                "exam_name"          => $exam->examtype?->exam_name ?? "Upcoming Exam",
                'description'        => $exam->examtype?->description ?? null,
                "semester"           => $exam->semester?->name ?? "Unknown Semester",
                "start_date"         => $exam->start_date,
                "end_date"           => $exam->end_date,
                "timetable_published" => (bool) $exam->timetable_published,
            ]);
        });

        $resitExams->each(function ($exam) use ($allUpcoming) {
            $allUpcoming->push([
                "exam_id"            => $exam->id,
                "exam_name"          => $exam->examtype?->exam_name ?? "Resit Exam",
                'description'        => $exam->examtype?->description ?? null,
                "semester"           => $exam->semester?->name ?? "Unknown Semester",
                "start_date"         => $exam->start_date,
                "end_date"           => $exam->end_date,
                "timetable_published" => (bool) $exam->timetable_published,
            ]);
        });

        $sorted = $allUpcoming->sortBy('start_date')->values();
        return $sorted;
    }
    public function getAllExamsByStudentId($currentSchool, $studentId)
    {
        $student = Student::where('school_branch_id', $currentSchool->id)
            ->findOrFail($studentId);

        $examQueryConstraints = [
            ['school_branch_id', $currentSchool->id],
            ['specialty_id', $student->specialty_id],
            ['level_id', $student->level_id],
        ];

        $relationshipsToLoad = ['semester', 'examtype', 'specialty', 'level'];

        $exams = Exams::where($examQueryConstraints)
            ->with($relationshipsToLoad)
            ->get();

        $resitExams = ResitExam::where($examQueryConstraints)
            ->with($relationshipsToLoad)
            ->get();

        $allExams = $exams->merge($resitExams);

        $grouped = $allExams->groupBy('semester_id');

        $result = $grouped->map(function ($examsInSemester) {
            $firstExam = $examsInSemester->first();
            $semester = $firstExam->semester;

            $semesterExams = $examsInSemester->map(function ($exam) {
                $item = [
                    "exam_name"         => $exam->examtype->exam_name ?? null,
                    "exam_id"           => $exam->id,
                    "level_id"          => $exam->level_id,
                    "level_name"        => $exam->level?->name ?? null,
                    "specialty_id"      => $exam->specialty_id,
                    "specialty_name"    => $exam->specialty?->specialty_name ?? null,
                    "description"       => $exam->examtype->description ?? null,
                    "exam_type_id"      => $exam->exam_type_id,
                    "student_batch_id"  => $exam->student_batch_id,
                    "timetable_created" => (bool) $exam->timetable_published,
                    "result_released"   => (bool) $exam->result_released,
                    "start_date"        => $exam->start_date,
                    "end_date"          => $exam->end_date,
                    "result_message"    => null,
                ];

                if ($exam->result_released == true) {
                    $item["result_message"] = [
                        "id"    => (string) Str::uuid(),
                        "title" => ($exam->examtype->exam_name ?? 'Exam') . " Results Are Out",
                        "body"  => "Well done on completing the exams! Your results are ready.",
                    ];
                }

                return $item;
            });


            return [
                "semester"   => $semester?->name ?? 'Unknown Semester',
                "semesterId" => $semester?->id ?? $firstExam->semester_id,
                "exams"      => $semesterExams->values()->all()
            ];
        })->values()->all();

        return $result;
    }
}
