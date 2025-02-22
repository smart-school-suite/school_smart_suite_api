<?php

namespace App\Http\Controllers;


use App\Models\Examtimetable;
use App\Http\Requests\ExamTimeTableRequest;
use App\Services\ExamTimeTableService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Exception;

class ExamTimeTableController extends Controller
{
    protected ExamTimeTableService $examTimeTableService;
    public function __construct(ExamTimeTableService $examTimeTableService)
    {
        $this->examTimeTableService = $examTimeTableService;
    }
    public function create_exam_timetable(ExamTimeTableRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $createExamTimeTable = $this->examTimeTableService->createExamTimeTable($request->exam_courses, $currentSchool);
            return ApiResponseService::success("Time Table Created Sucessfully", $createExamTimeTable, null, 201);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    public function delete_exam_time_table_scoped(Request $request, $examtimetable_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteExamTimeTable = $this->examTimeTableService->deleteTimeTable($examtimetable_id, $currentSchool);
        return ApiResponseService::success("Exam Time Table Entry Deleted Sucessfully", $deleteExamTimeTable, null, 200);
    }

    //work on the update functionality
    public function update_exam_time_table_scoped(Request $request, $examtimetable_id)
    {

        $currentSchool = $request->attributes->get('currentSchool');

        $exam_data_entry = ExamTimetable::where('school_branch_id', $currentSchool->id)->find($examtimetable_id);

        if (!$exam_data_entry) {
            return response()->json(['message' => 'Exam not found'], 409);
        }

        $request->validate([
            'course_id' => 'sometimes|exists:courses,id',
            'exam_id' => 'sometimes|exists:exams,id',
            'specialty_id' => 'sometimes|exists:specialties,id',
            'level_id' => 'sometimes|exists:educationlevels,id',
            'day' => 'sometimes|string',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
        ]);

        $startTime = null;
        $endTime = null;
        if ($request->has('start_time') && $request->has('end_time')) {
            $startTime = \Carbon\Carbon::parse($request->start_time);
            $endTime = \Carbon\Carbon::parse($request->end_time);
        }

        $overlappingTimetables = ExamTimetable::where('school_branch_id', $currentSchool->id)
            ->where('course_id', $request->course_id)
            ->where('specialty_id', $request->specialty_id)
            ->where('level_id', $request->level_id)
            ->where('day', $request->day)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($query) use ($startTime, $endTime) {
                        $query->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })
            ->exists();

        if ($overlappingTimetables) {
            return response()->json([
                'status' => 'ok',
                'message' => 'The timetable overlaps with an existing course. Please choose a different time.'
            ], 409);
        }

        $exam_timetable_data = array_filter($request->all());

        if (isset($startTime) && isset($endTime)) {
            $exam_timetable_data['duration'] = $startTime->diffInMinutes($endTime);
        }

        $exam_data_entry->fill($exam_timetable_data);
        $exam_data_entry->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Exam timetable updated successfully'
        ], 200);
    }

    public function generate_time_table_for_specialty(Request $request, $specialty_id, $level_id)
    {

        $currentSchool = $request->attributes->get('currentSchool');
        $generateExamTimeTable = $this->examTimeTableService->generateExamTimeTable($level_id, $specialty_id, $currentSchool);
        return ApiResponseService::success("Exam Time Table Generated Sucessfully", $generateExamTimeTable, null, 200);
    }

    public function prepareExamTimeTableData(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $exam_id = $request->route("exam_id");
        $prepareExamTimeTableData = $this->examTimeTableService->prepareExamTimeTableData($exam_id, $currentSchool);
        return ApiResponseService::success("Exam Time Table Data Fetched Successfully", $prepareExamTimeTableData, null, 200);
    }
}
