<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstructorAvailability;
use App\Models\Specialty;
use Carbon\Carbon;
use App\Models\Timetable;

class timetableController extends Controller
{
    //
    public function create_time_slots_scoped(Request $request)
{
    $currentSchool = $request->attributes->get('currentSchool');
    $request->validate([
        'specailty_timetable' => 'required|array',
        'specailty_timetable.*.teacher_id' => 'required|string',
        'specailty_timetable.*.course_id' => 'required|exists:courses,id',
        'specailty_timetable.*.day_of_week' => 'required|string',
        'specailty_timetable.*.start_time' => 'required|date_format:H:i',
        'specailty_timetable.*.specialty_id' => 'required|string',
        'specailty_timetable.*.level_id' => 'required|string',
        'specailty_timetable.*.semester_id' => 'required|string',
        'specailty_timetable.*.end_time' => 'required|date_format:H:i|after:start_time',
    ]);


    foreach ($request->specailty_timetable as $timetable) {
        $this->createTimeSlot($currentSchool, $timetable);
    }

    return response()->json([
        'status' => 'ok',
        'message' => 'Time slots created successfully',
    ], 200);
}

private function createTimeSlot($currentSchool, $timetable)
{

    $isTeacherAvailable = $this->isTeacherAvailable($currentSchool, $timetable);
    $isTimeSlotAlreadyAssigned = $this->isTimeSlotAlreadyAssigned($currentSchool, $timetable);

    if (!$isTeacherAvailable) {

        throw new \Exception("Teacher is not available at this time", 409);
    }

    if ($isTimeSlotAlreadyAssigned) {

        throw new \Exception("Teacher is already assigned to this time slot", 409);
    }

    $timeTableData = new Timetable();
    $timeTableData->school_branch_id = $currentSchool->id;
    $timeTableData->course_id = $timetable['course_id'];
    $timeTableData->teacher_id = $timetable['teacher_id'];
    $timeTableData->day_of_week = $timetable['day_of_week'];
    $timeTableData->specialty_id = $timetable['specialty_id'];
    $timeTableData->level_id = $timetable['level_id'];
    $timeTableData->semester_id = $timetable['semester_id'];
    $timeTableData->start_time = $timetable['start_time'];
    $timeTableData->end_time = $timetable['end_time'];

    $timeTableData->save();

    return $timeTableData;
}

private function isTeacherAvailable($currentSchool, $timetable)
{
    return InstructorAvailability::where('school_branch_id', $currentSchool->id)
        ->where('teacher_id', $timetable['teacher_id'])
        ->where('level_id', $timetable['level_id'])
        ->where('semester_id', $timetable['semester_id'])
        ->where('specialty_id', $timetable['specialty_id'])
        ->where('day_of_week', $timetable['day_of_week'])
        ->where(function ($query) use ($timetable) {
            $query->whereBetween('start_time', [$timetable['start_time'], $timetable['end_time']])
                ->orWhereBetween('end_time', [$timetable['start_time'], $timetable['end_time']])
                ->orWhere(function ($query) use ($timetable) {
                    $query->where('start_time', '<=', $timetable['start_time'])
                        ->where('end_time', '>=', $timetable['end_time']);
                });
        })
        ->exists();
}

private function isTimeSlotAlreadyAssigned($currentSchool, $timetable)
{
    return Timetable::where('school_branch_id', $currentSchool->id)
        ->where('teacher_id', $timetable['teacher_id'])
        ->where('level_id', $timetable['level_id'])
        ->where('semester_id', $timetable['semester_id'])
        ->where('specialty_id', $timetable['specialty_id'])
        ->where('day_of_week', $timetable['day_of_week'])
        ->where(function ($query) use ($timetable) {
            $query->whereBetween('start_time', [$timetable['start_time'], $timetable['end_time']])
                ->orWhereBetween('end_time', [$timetable['start_time'], $timetable['end_time']])
                ->orWhere(function ($query) use ($timetable) {
                    $query->where('start_time', '<=', $timetable['start_time'])
                        ->where('end_time', '>=', $timetable['end_time']);
                });
        })
        ->exists();
}


    public function delete_timetable_scoped(Request $request, $timetable_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $time_table = Timetable::Where('school_id', $currentSchool->id)->find($timetable_id);
        if (!$time_table) {
            return response()->json([
                'status' => 'error',
                'message' => 'Entry not created'
            ], 409);
        }

        $time_table->delete();

        return response()->json([
             'status' => 'ok',
             'message' => 'Entry deleted sucessfully',
             'deleted_timetable' => $time_table
        ], 200);
    }


    public function update_time_table_record_scoped(Request $request, $timetable_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $time_table = Timetable::where('school_id', $currentSchool->id)->find($timetable_id);
        if (!$time_table) {
            return response()->json([
                'status' => 'error',
                'message' => 'Entry not found'
            ], 409);
        }

        $clashExists = InstructorAvailability::where('school_id', $currentSchool->id) // Scope to current school
            ->where('teacher_id', $request->teacher_id)
            ->where('level_id', $request->level_id)
            ->where('semester_id', $request->semester_id)
            ->where('specialty_id', $request->specialty_id)
            ->where('day_of_week', $request->day_of_week)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                    ->orWhere(function ($query) use ($request) {
                        $query->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                    });
            })
            ->exists();

        if (!$clashExists) {
            $entry_data = $request->all();
            $entry_data = array_filter($entry_data);
            $time_table->fill($entry_data);
            $time_table->save();
            return response()->json([
                'status' => 'ok',
                'message' => 'Entry created succesfully',
                'updated_timetable' => $clashExists
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Teacher not available within this time slot',
                'clashes' => $clashExists,
            ], 409);
        }
    }


    public function generate_time_table_scoped(Request $request, $specailty_id, $level_id)
    {
        $specailty_id = $request->route('specailty_id');
        $level_id = $request->route('level_id');
        $currentSchool = $request->attributes->get('currentSchool');


        $timetables = Timetable::where('school_branch_id', $currentSchool->id)
            ->where('specialty_id', $specailty_id)
            ->where('level_id', $level_id)
            ->with(['course', 'teacher'])
            ->get();

        if($timetables->isEmpty()){
            return response()->json([
                'status' => 'error',
                'message' => 'Timetable records seem to be empty'
            ], 409);
        }
        $time_table = [
            "monday" => [],
            "tuesday" => [],
            "wednesday" => [],
            "thursday" => [],
            "friday" => []
        ];
        foreach ($timetables as $entry) {
            $day = strtolower($entry->day_of_week);

            if (array_key_exists($day, $time_table)) {
                $time_table[$day][] = [
                    "course" => $entry->course->course_title,
                    "start_time" => Carbon::parse($entry->start_time)->format('g:i A'),
                    "end_time" => Carbon::parse($entry->end_time)->format('g:i A'),
                    "teacher" => $entry->teacher->name
                ];
            }
        }

        return response()->json([
             'status' => 'ok',
             'message' => 'Time table fetched succefully',
             'timetable' => $time_table
        ], 200);
    }

    public function get_timetable_details(Request $request){
         $currentSchool = $request->attributes->get("currentSchool");
         $entry_id =  $request->route("entry_id");
         $find_timetable_entry = Timetable::find($entry_id);
         if(!$find_timetable_entry){
            return response()->json([
                 "status" => "error",
                 "message" => "Timetable entry not found"
            ], 400);
         }

         $timetable_details = Timetable::where("school_branch_id", $currentSchool->id)
                                         ->where("id", $entry_id)
                                         ->with([''])
                                         ->get();
        return response()->json([
             "status" => "ok",
             "message" => "Time table details fetched sucessfully",
             "timetable_details" => $timetable_details
        ], 200);

    }

    public function get_instructor_availability(Request $request)
    {

        $currentSchool = $request->attributes->get("currentSchool");
        $specialtyId = $request->route("specialty_id");
        $semesterId = $request->route("semester_id");


        if (!$currentSchool || !$specialtyId || !$semesterId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid input parameters',
            ], 400);
        }

        $specialty = Specialty::find($specialtyId);
        if (!$specialty) {
            return response()->json([
                'status' => 'error',
                'message' => 'Specialty not found',
            ], 404);
        }

        $levelId = $specialty->level->id;


        $instructorAvailabilityData = InstructorAvailability::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $specialtyId)
            ->where("semester_id", $semesterId)
            ->with(['teacher'])
            ->get();


        if (!$instructorAvailabilityData->count()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No instructor availability data found',
            ], 404);
        }


        $results = $instructorAvailabilityData->map(function ($item) use ($semesterId, $levelId) {
            return [
                'teacher_id' => $item->teacher_id,
                'semester_id' => $semesterId,
                'day' => $item->day_of_week,
                'start_time' => $item->start_time,
                'teacher_name' => $item->teacher->name,
                'end_time' => $item->end_time,
                'level_id' => $levelId,
            ];
        })->toArray();


        return response()->json([
            'status' => 'ok',
            'message' => 'Instructor availability data fetched successfully',
            'instructor_availability' => $results,
        ], 200);
    }
}
