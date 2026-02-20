<?php

namespace App\Services\JointCourse;

use App\Exceptions\AppException;
use App\Models\Course\JointCourseSlot;
use App\Models\Course\SemesterJointCourse;
use App\Models\InstructorAvailabilitySlot;
use App\Models\SemesterTimetable\SemesterTimetableSlot;
use App\Models\TeacherCoursePreference;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class JointCourseSlotService
{
    private function validateJointCourseConflict(array $jointCourseSlots)
    {
        foreach ($jointCourseSlots as $slot) {
            $hallConflict = JointCourseSlot::where("school_branch_id", $slot['school_branch_id'])
                ->where("hall_id", $slot['hall_id'])
                ->where('day_of_week', $slot['day'])
                ->where('start_time', '<', $slot['end_time'])
                ->where('end_time', '>', $slot['start_time'])
                ->exists();

            if ($hallConflict) {
                throw new AppException(
                    "Hall Conflict",
                    409,
                    "Hall Unavailable",
                    "The selected hall is not available during the specified time slot. Please choose a different hall or adjust the time."
                );
            }
        }

        $teacherConflicts = JointCourseSlot::where("school_branch_id", $slot['school_branch_id'])
            ->where("teacher_id", $slot['teacher_id'])
            ->where('day_of_week', $slot['day'])
            ->where('start_time', '<', $slot['end_time'])
            ->where('end_time', '>', $slot['start_time'])
            ->exists();

        if ($teacherConflicts) {
            throw new AppException(
                "Teacher Conflict",
                409,
                "Teacher Unavailable",
                "The teacher is already scheduled during this time slot. Please choose a different time."
            );
        }
    }
    public function createPreferenceJointCourseSlot(array $jointCourseSlotData, object $currentSchool)
    {
        $semesterJointCourseId = $jointCourseSlotData['semester_joint_course_id'];
        $this->validateNoSelfConflicts($jointCourseSlotData['slots']);

        $existingSlots = JointCourseSlot::where("school_branch_id", $currentSchool->id)
            ->where("semester_joint_course_id", $semesterJointCourseId)
            ->exists();

        if ($existingSlots) {
            throw new AppException(
                "Existing Slots Found",
                400,
                "Conflict Detected",
                "The specified joint course already has slots assigned to it. Please update the existing slots instead."
            );
        }

        $semesterJointCourse = SemesterJointCourse::where("school_branch_id", $currentSchool->id)
            ->where("id", $semesterJointCourseId)
            ->with(['course.courseSpecialty', 'semester', 'schoolYear'])
            ->firstOrFail();

        $course    = $semesterJointCourse->course;
        $semester  = $semesterJointCourse->semester;
        $specialties = $course->courseSpecialty->pluck('specialty_id')->toArray();

        $teacherCourse = TeacherCoursePreference::where("course_id", $course->id)
            ->where("school_branch_id", $currentSchool->id)
            ->with('teacher')
            ->first();

        if (!$teacherCourse) {
            throw new AppException(
                "No Teacher Assigned",
                404,
                "Teacher Not Found",
                "The course {$course->course_title} does not have a teacher assigned to it. Please assign a teacher before creating a joint course slot."
            );
        }

        $teacherPreferredSlots = InstructorAvailabilitySlot::where("school_branch_id", $currentSchool->id)
            ->where("teacher_id", $teacherCourse->teacher_id)
            ->whereHas('schoolSemester', function ($query) use ($semester) {
                $query->where('end_date', '>', now())
                    ->where('start_date', '<', now())
                    ->where('semester_id', $semester->id);
            })
            ->get();

        if ($teacherPreferredSlots->isEmpty()) {
            throw new AppException(
                "No Preferred Slots",
                404,
                "Preferred Slots Not Found",
                "{$teacherCourse->teacher->teacher_name} does not have any preferred slots for the current semester. Please ask the teacher to set their availability before creating a joint course slot."
            );
        }

        $busySlots = SemesterTimetableSlot::where("school_branch_id", $currentSchool->id)
            ->whereHas('schoolSemester', function ($query) {
                $query->where('end_date', '>', now())
                    ->where('start_date', '<', now());
            })
            ->get();

        foreach ($jointCourseSlotData['slots'] as $slot) {
            $slotDay       = $slot['day'];
            $slotStart     = Carbon::parse($slot['start_time']);
            $slotEnd       = Carbon::parse($slot['end_time']);

            $withinPreference = $teacherPreferredSlots->first(function ($preferred) use ($slotDay, $slotStart, $slotEnd) {
                return strtolower($preferred->day_of_week) === strtolower($slotDay)
                    && Carbon::parse($preferred->start_time)->lte($slotStart)
                    && Carbon::parse($preferred->end_time)->gte($slotEnd);
            });

            if (!$withinPreference) {
                throw new AppException(
                    "Outside Preferred Availability",
                    409,
                    "Slot Not Within Teacher Availability",
                    "The requested slot (" . $slotDay . " " . $slotStart->format('H:i') . " - " . $slotEnd->format('H:i') . ") is outside {$teacherCourse->teacher->teacher_name}'s preferred teaching hours. Please choose a slot within their available periods."
                );
            }

            $hallConflict = SemesterTimetableSlot::where("school_branch_id", $currentSchool->id)
                ->where("hall_id", $slot['hall_id'])
                ->where('day_of_week', $slotDay)
                ->where('start_time', '<', $slotEnd)
                ->where('end_time', '>', $slotStart)
                ->whereHas('schoolSemester', function ($query) {
                    $query->where('end_date', '>', now())
                        ->where('start_date', '<', now());
                })
                ->exists();

            if ($hallConflict) {
                throw new AppException(
                    "Hall Conflict",
                    409,
                    "Hall Unavailable",
                    "The selected hall is not available during the specified time slot. Please choose a different hall or adjust the time."
                );
            }

            $teacherConflict = $busySlots->filter(function ($busySlot) use ($teacherCourse, $slotDay, $slotStart, $slotEnd) {
                return $busySlot->teacher_id === $teacherCourse->teacher_id
                    && strtolower($busySlot->day_of_week) === strtolower($slotDay)
                    && Carbon::parse($busySlot->start_time)->lt($slotEnd)
                    && Carbon::parse($busySlot->end_time)->gt($slotStart);
            })->isNotEmpty();

            if ($teacherConflict) {
                throw new AppException(
                    "Teacher Conflict",
                    409,
                    "Teacher Unavailable",
                    "The teacher {$teacherCourse->teacher->teacher_name} is already scheduled during this time slot. Please choose a different time."
                );
            }

            $specialtyConflict = SemesterTimetableSlot::where("school_branch_id", $currentSchool->id)
                ->whereIn("specialty_id", $specialties)
                ->where('day_of_week', $slotDay)
                ->where('start_time', '<', $slotEnd)
                ->where('end_time', '>', $slotStart)
                ->whereHas('schoolSemester', function ($query) {
                    $query->where('end_date', '>', now())
                        ->where('start_date', '<', now());
                })
                ->exists();

            if ($specialtyConflict) {
                throw new AppException(
                    "Specialty Conflict",
                    409,
                    "Specialty Unavailable",
                    "One of the specialties linked to this course already has a class scheduled during this time slot. Please choose a different time."
                );
            }
        }

        foreach ($jointCourseSlotData['slots'] as $slot) {
            JointCourseSlot::create([
                'school_branch_id'        => $currentSchool->id,
                'course_id'               => $course->id,
                'teacher_id'              => $teacherCourse->teacher_id,
                'day'                     => strtolower($slot['day']),
                'start_time'              => $slot['start_time'],
                'end_time'                => $slot['end_time'],
                'semester_joint_course_id' => $semesterJointCourseId,
                'hall_id'                 => $slot['hall_id'],
            ]);
        }
    }
    public function createFixedJointCourseSlot(array $jointCourseSlotData, object $currentSchool)
    {
        $semesterJointCourseId = $jointCourseSlotData['semester_joint_course_id'];
        $this->validateNoSelfConflicts($jointCourseSlotData['slots']);

        $existingSlots = JointCourseSlot::where("school_branch_id", $currentSchool->id)
            ->where("semester_joint_course_id", $semesterJointCourseId)
            ->exists();

        if ($existingSlots) {
            throw new AppException(
                "Existing Slots Found",
                400,
                "Conflict Detected",
                "The specified joint course already has slots assigned to it. Please update the existing slots instead."
            );
        }

        $semesterJointCourse = SemesterJointCourse::where("school_branch_id", $currentSchool->id)
            ->where("id", $semesterJointCourseId)
            ->with(['course.courseSpecialty', 'semester', 'schoolYear'])
            ->firstOrFail();

        $course      = $semesterJointCourse->course;
        $semester    = $semesterJointCourse->semester;
        $specialties = $course->courseSpecialty->pluck('specialty_id')->toArray();

        $teacherCourse = TeacherCoursePreference::where("course_id", $course->id)
            ->where("school_branch_id", $currentSchool->id)
            ->with('teacher')
            ->first();

        if (!$teacherCourse) {
            throw new AppException(
                "No Teacher Assigned",
                404,
                "Teacher Not Found",
                "The course {$course->course_title} does not have a teacher assigned to it. Please assign a teacher before creating a joint course slot."
            );
        }

        $busySlots = SemesterTimetableSlot::where("school_branch_id", $currentSchool->id)
            ->whereHas('schoolSemester', function ($query) {
                $query->where('end_date', '>', now())
                    ->where('start_date', '<', now());
            })
            ->get();

        foreach ($jointCourseSlotData['slots'] as $slot) {
            $slotDay   = $slot['day'];
            $slotStart = Carbon::parse($slot['start_time']);
            $slotEnd   = Carbon::parse($slot['end_time']);

            $hallConflict = SemesterTimetableSlot::where("school_branch_id", $currentSchool->id)
                ->where("hall_id", $slot['hall_id'])
                ->where('day_of_week', $slotDay)
                ->where('start_time', '<', $slotEnd)
                ->where('end_time', '>', $slotStart)
                ->whereHas('schoolSemester', function ($query) {
                    $query->where('end_date', '>', now())
                        ->where('start_date', '<', now());
                })
                ->exists();

            if ($hallConflict) {
                throw new AppException(
                    "Hall Conflict",
                    409,
                    "Hall Unavailable",
                    "The selected hall is not available during the specified time slot. Please choose a different hall or adjust the time."
                );
            }

            $teacherConflict = $busySlots->filter(function ($busySlot) use ($teacherCourse, $slotDay, $slotStart, $slotEnd) {
                return $busySlot->teacher_id === $teacherCourse->teacher_id
                    && strtolower($busySlot->day_of_week) === strtolower($slotDay)
                    && Carbon::parse($busySlot->start_time)->lt($slotEnd)
                    && Carbon::parse($busySlot->end_time)->gt($slotStart);
            })->isNotEmpty();

            if ($teacherConflict) {
                throw new AppException(
                    "Teacher Conflict",
                    409,
                    "Teacher Unavailable",
                    "The teacher {$teacherCourse->teacher->teacher_name} is already scheduled during this time slot. Please choose a different time."
                );
            }

            $specialtyConflict = SemesterTimetableSlot::where("school_branch_id", $currentSchool->id)
                ->whereIn("specialty_id", $specialties)
                ->where('day_of_week', $slotDay)
                ->where('start_time', '<', $slotEnd)
                ->where('end_time', '>', $slotStart)
                ->whereHas('schoolSemester', function ($query) {
                    $query->where('end_date', '>', now())
                        ->where('start_date', '<', now());
                })
                ->exists();

            if ($specialtyConflict) {
                throw new AppException(
                    "Specialty Conflict",
                    409,
                    "Specialty Unavailable",
                    "One of the specialties linked to this course already has a class scheduled during this time slot. Please choose a different time."
                );
            }
        }

        foreach ($jointCourseSlotData['slots'] as $slot) {
            JointCourseSlot::create([
                'school_branch_id'         => $currentSchool->id,
                'course_id'                => $course->id,
                'teacher_id'               => $teacherCourse->teacher_id,
                'day'                      => strtolower($slot['day']),
                'start_time'               => $slot['start_time'],
                'end_time'                 => $slot['end_time'],
                'semester_joint_course_id' => $semesterJointCourseId,
                'hall_id'                  => $slot['hall_id'],
            ]);
        }
    }
    public function updatePreferenceJointCourseSlot(array $updateData, object $currentSchool)
    {
        $updateSlots = $updateData['slots'] ?? [];
        $this->validateNoSelfConflicts($updateSlots);

        $semesterJointCourse = SemesterJointCourse::where("school_branch_id", $currentSchool->id)
            ->where("id", $updateData['semester_joint_course_id'])
            ->with(['course.courseSpecialty', 'semester', 'schoolYear'])
            ->first();

        if (!$semesterJointCourse) {
            throw new AppException(
                "Joint Course Not Found",
                404,
                "Not Found",
                "The specified joint course could not be found. Please verify the joint course and try again."
            );
        }

        $course      = $semesterJointCourse->course;
        $semester    = $semesterJointCourse->semester;
        $specialties = $course->courseSpecialty->pluck('specialty_id')->toArray();

        $existingSlots = JointCourseSlot::where("school_branch_id", $currentSchool->id)
            ->where("semester_joint_course_id", $semesterJointCourse->id)
            ->get();

        if ($existingSlots->isEmpty()) {
            throw new AppException(
                "No Slots Found",
                404,
                "Slots Not Found",
                "No existing slots were found for the specified joint course. Please create slots before attempting an update."
            );
        }

        if (count($updateSlots) !== $existingSlots->count()) {
            throw new AppException(
                "Slot Count Mismatch",
                400,
                "Invalid Slot Count",
                "The number of slots provided (" . count($updateSlots) . ") does not match the number of existing slots (" . $existingSlots->count() . "). Please provide an update for every existing slot."
            );
        }

        $existingSlotIds = $existingSlots->pluck('id')->toArray();

        foreach ($updateSlots as $slot) {
            if (!in_array($slot['slot_id'], $existingSlotIds)) {
                throw new AppException(
                    "Invalid Slot",
                    400,
                    "Slot Mismatch",
                    "The slot with ID {$slot['slot_id']} does not belong to the specified joint course. Please ensure all slot IDs belong to the correct joint course."
                );
            }
        }

        $teacherCourse = TeacherCoursePreference::where("course_id", $course->id)
            ->where("school_branch_id", $currentSchool->id)
            ->with('teacher')
            ->first();

        if (!$teacherCourse) {
            throw new AppException(
                "No Teacher Assigned",
                404,
                "Teacher Not Found",
                "The course {$course->course_title} does not have a teacher assigned to it. Please assign a teacher before updating the joint course slot."
            );
        }

        $teacherPreferredSlots = InstructorAvailabilitySlot::where("school_branch_id", $currentSchool->id)
            ->where("teacher_id", $teacherCourse->teacher_id)
            ->whereHas('schoolSemester', function ($query) use ($semester) {
                $query->where('end_date', '>', now())
                    ->where('start_date', '<', now())
                    ->where('semester_id', $semester->id);
            })
            ->get();

        if ($teacherPreferredSlots->isEmpty()) {
            throw new AppException(
                "No Preferred Slots",
                404,
                "Preferred Slots Not Found",
                "{$teacherCourse->teacher->teacher_name} does not have any preferred slots for the current semester. Please ask the teacher to set their availability before updating the joint course slot."
            );
        }

        $busySlots = SemesterTimetableSlot::where("school_branch_id", $currentSchool->id)
            ->whereNotIn("joint_course_slot_id", $existingSlotIds)
            ->whereHas('schoolSemester', function ($query) {
                $query->where('end_date', '>', now())
                    ->where('start_date', '<', now());
            })
            ->get();

        foreach ($updateSlots as $slot) {
            $slotDay   = $slot['day'];
            $slotStart = Carbon::parse($slot['start_time']);
            $slotEnd   = Carbon::parse($slot['end_time']);

            $withinPreference = $teacherPreferredSlots->first(function ($preferred) use ($slotDay, $slotStart, $slotEnd) {
                return strtolower($preferred->day_of_week) === strtolower($slotDay)
                    && Carbon::parse($preferred->start_time)->lte($slotStart)
                    && Carbon::parse($preferred->end_time)->gte($slotEnd);
            });

            if (!$withinPreference) {
                throw new AppException(
                    "Outside Preferred Availability",
                    409,
                    "Slot Not Within Teacher Availability",
                    "The requested slot (" . $slotDay . " " . $slotStart->format('H:i') . " - " . $slotEnd->format('H:i') . ") is outside {$teacherCourse->teacher->teacher_name}'s preferred teaching hours. Please choose a slot within their available periods."
                );
            }

            $hallConflict = SemesterTimetableSlot::where("school_branch_id", $currentSchool->id)
                ->where("hall_id", $slot['hall_id'])
                ->where('day_of_week', $slotDay)
                ->where('start_time', '<', $slotEnd)
                ->where('end_time', '>', $slotStart)
                ->whereNotIn("joint_course_slot_id", $existingSlotIds)
                ->whereHas('schoolSemester', function ($query) {
                    $query->where('end_date', '>', now())
                        ->where('start_date', '<', now());
                })
                ->exists();

            if ($hallConflict) {
                throw new AppException(
                    "Hall Conflict",
                    409,
                    "Hall Unavailable",
                    "The selected hall is not available during the specified time slot. Please choose a different hall or adjust the time."
                );
            }

            $teacherConflict = $busySlots->filter(function ($busySlot) use ($teacherCourse, $slotDay, $slotStart, $slotEnd) {
                return $busySlot->teacher_id === $teacherCourse->teacher_id
                    && strtolower($busySlot->day_of_week) === strtolower($slotDay)
                    && Carbon::parse($busySlot->start_time)->lt($slotEnd)
                    && Carbon::parse($busySlot->end_time)->gt($slotStart);
            })->isNotEmpty();

            if ($teacherConflict) {
                throw new AppException(
                    "Teacher Conflict",
                    409,
                    "Teacher Unavailable",
                    "The teacher {$teacherCourse->teacher->teacher_name} is already scheduled during this time slot. Please choose a different time."
                );
            }

            $specialtyConflict = SemesterTimetableSlot::where("school_branch_id", $currentSchool->id)
                ->whereIn("specialty_id", $specialties)
                ->where('day_of_week', $slotDay)
                ->where('start_time', '<', $slotEnd)
                ->where('end_time', '>', $slotStart)
                ->whereNotIn("joint_course_slot_id", $existingSlotIds)
                ->whereHas('schoolSemester', function ($query) {
                    $query->where('end_date', '>', now())
                        ->where('start_date', '<', now());
                })
                ->exists();

            if ($specialtyConflict) {
                throw new AppException(
                    "Specialty Conflict",
                    409,
                    "Specialty Unavailable",
                    "One of the specialties linked to this course already has a class scheduled during this time slot. Please choose a different time."
                );
            }
        }

        foreach ($updateSlots as $slot) {
            JointCourseSlot::where("id", $slot['slot_id'])
                ->where("school_branch_id", $currentSchool->id)
                ->update([
                    'day'        => strtolower($slot['day']),
                    'start_time' => $slot['start_time'],
                    'end_time'   => $slot['end_time'],
                    'hall_id'    => $slot['hall_id'],
                    'teacher_id' => $slot['teacher_id'],
                ]);
        }
    }
    public function updateFixedJointCourseSlot(array $updateData, object $currentSchool)
    {
        $updateSlots = $updateData['slots'] ?? [];
        $this->validateNoSelfConflicts($updateSlots);
        $semesterJointCourse = SemesterJointCourse::where("school_branch_id", $currentSchool->id)
            ->where("id", $updateData['semester_joint_course_id'])
            ->with(['course.courseSpecialty', 'semester', 'schoolYear'])
            ->first();

        if (!$semesterJointCourse) {
            throw new AppException(
                "Joint Course Not Found",
                404,
                "Not Found",
                "The specified joint course could not be found. Please verify the joint course and try again."
            );
        }

        $course      = $semesterJointCourse->course;
        $semester    = $semesterJointCourse->semester;
        $specialties = $course->courseSpecialty->pluck('specialty_id')->toArray();

        $existingSlots = JointCourseSlot::where("school_branch_id", $currentSchool->id)
            ->where("semester_joint_course_id", $semesterJointCourse->id)
            ->get();

        if ($existingSlots->isEmpty()) {
            throw new AppException(
                "No Slots Found",
                404,
                "Slots Not Found",
                "No existing slots were found for the specified joint course. Please create slots before attempting an update."
            );
        }

        if (count($updateSlots) !== $existingSlots->count()) {
            throw new AppException(
                "Slot Count Mismatch",
                400,
                "Invalid Slot Count",
                "The number of slots provided (" . count($updateSlots) . ") does not match the number of existing slots (" . $existingSlots->count() . "). Please provide an update for every existing slot."
            );
        }

        $existingSlotIds = $existingSlots->pluck('id')->toArray();

        foreach ($updateSlots as $slot) {
            if (!in_array($slot['slot_id'], $existingSlotIds)) {
                throw new AppException(
                    "Invalid Slot",
                    400,
                    "Slot Mismatch",
                    "The slot with ID {$slot['slot_id']} does not belong to the specified joint course. Please ensure all slot IDs belong to the correct joint course."
                );
            }
        }

        $teacherCourse = TeacherCoursePreference::where("course_id", $course->id)
            ->where("school_branch_id", $currentSchool->id)
            ->with('teacher')
            ->first();

        if (!$teacherCourse) {
            throw new AppException(
                "No Teacher Assigned",
                404,
                "Teacher Not Found",
                "The course {$course->course_title} does not have a teacher assigned to it. Please assign a teacher before updating the joint course slot."
            );
        }

        $busySlots = SemesterTimetableSlot::where("school_branch_id", $currentSchool->id)
            ->whereNotIn("joint_course_slot_id", $existingSlotIds)
            ->whereHas('schoolSemester', function ($query) {
                $query->where('end_date', '>', now())
                    ->where('start_date', '<', now());
            })
            ->get();

        foreach ($updateSlots as $slot) {
            $slotDay   = $slot['day'];
            $slotStart = Carbon::parse($slot['start_time']);
            $slotEnd   = Carbon::parse($slot['end_time']);

            $hallConflict = SemesterTimetableSlot::where("school_branch_id", $currentSchool->id)
                ->where("hall_id", $slot['hall_id'])
                ->where('day_of_week', $slotDay)
                ->where('start_time', '<', $slotEnd)
                ->where('end_time', '>', $slotStart)
                ->whereNotIn("joint_course_slot_id", $existingSlotIds)
                ->whereHas('schoolSemester', function ($query) {
                    $query->where('end_date', '>', now())
                        ->where('start_date', '<', now());
                })
                ->exists();

            if ($hallConflict) {
                throw new AppException(
                    "Hall Conflict",
                    409,
                    "Hall Unavailable",
                    "The selected hall is not available during the specified time slot. Please choose a different hall or adjust the time."
                );
            }

            $teacherConflict = $busySlots->filter(function ($busySlot) use ($teacherCourse, $slotDay, $slotStart, $slotEnd) {
                return $busySlot->teacher_id === $teacherCourse->teacher_id
                    && strtolower($busySlot->day_of_week) === strtolower($slotDay)
                    && Carbon::parse($busySlot->start_time)->lt($slotEnd)
                    && Carbon::parse($busySlot->end_time)->gt($slotStart);
            })->isNotEmpty();

            if ($teacherConflict) {
                throw new AppException(
                    "Teacher Conflict",
                    409,
                    "Teacher Unavailable",
                    "The teacher {$teacherCourse->teacher->teacher_name} is already scheduled during this time slot. Please choose a different time."
                );
            }

            $specialtyConflict = SemesterTimetableSlot::where("school_branch_id", $currentSchool->id)
                ->whereIn("specialty_id", $specialties)
                ->where('day_of_week', $slotDay)
                ->where('start_time', '<', $slotEnd)
                ->where('end_time', '>', $slotStart)
                ->whereNotIn("joint_course_slot_id", $existingSlotIds)
                ->whereHas('schoolSemester', function ($query) {
                    $query->where('end_date', '>', now())
                        ->where('start_date', '<', now());
                })
                ->exists();

            if ($specialtyConflict) {
                throw new AppException(
                    "Specialty Conflict",
                    409,
                    "Specialty Unavailable",
                    "One of the specialties linked to this course already has a class scheduled during this time slot. Please choose a different time."
                );
            }
        }

        foreach ($updateSlots as $slot) {
            JointCourseSlot::where("id", $slot['slot_id'])
                ->where("school_branch_id", $currentSchool->id)
                ->update([
                    'day'        => strtolower($slot['day']),
                    'start_time' => $slot['start_time'],
                    'end_time'   => $slot['end_time'],
                    'hall_id'    => $slot['hall_id'],
                    'teacher_id' => $slot['teacher_id'],
                ]);
        }
    }
    public function suggestSlotsJointCourse(object $currentSchool, array $suggestionData)
    {
        $semesterJointCourse = SemesterJointCourse::where("school_branch_id", $currentSchool->id)
            ->where("id", $suggestionData['semester_joint_course_id'])
            ->with(['course.teacherCoursePreference.teacher', 'semester', 'schoolYear'])
            ->first();

        $course = $semesterJointCourse->course;
        $semester = $semesterJointCourse->semester;
        $teacher = $course->teacherCoursePreference[0]->teacher;
        $hallId = $suggestionData['hall_id'] ?? null;
        $interval = $suggestionData['interval'] ?? 60;

        $teacherPreferredSlots = InstructorAvailabilitySlot::where("school_branch_id", $currentSchool->id)
            ->where("teacher_id", $teacher->id)
            ->whereHas('schoolSemester', function ($query) use ($semester) {
                $query->where('end_date', '>', now())
                    ->where('start_date', '<', now())
                    ->with(['semester' => function ($query) use ($semester) {
                        $query->where('id', $semester->id);
                    }]);
            })
            ->get();

        $busySlots = SemesterTimetableSlot::where("school_branch_id", $currentSchool->id)
            ->where("teacher_id", $teacher->id)
            ->whereHas('schoolSemester', function ($query) {
                $query->where('end_date', '>', now())
                    ->where('start_date', '<', now());
            })
            ->get();

        $busyByDay = $busySlots->groupBy('day_of_week');

        $suggestions = [];

        foreach ($teacherPreferredSlots as $preferredSlot) {
            $day = $preferredSlot->day_of_week;

            $generatedSlots = $this->generateAlignedSlots(
                $preferredSlot->start_time,
                $preferredSlot->end_time,
                $interval
            );

            $dayBusySlots = $busyByDay->get($day, collect());

            $availableSlots = array_filter($generatedSlots, function ($slot) use ($dayBusySlots) {
                return !$this->hasConflict($slot['start_time'], $slot['end_time'], $dayBusySlots);
            });

            if (!empty($availableSlots)) {
                $existingDayIndex = array_search($day, array_column($suggestions, 'day'));

                if ($existingDayIndex !== false) {
                    $suggestions[$existingDayIndex]['slots'] = array_merge(
                        $suggestions[$existingDayIndex]['slots'],
                        array_values($availableSlots)
                    );
                } else {
                    $suggestions[] = [
                        'day'   => $day,
                        'slots' => array_values($availableSlots),
                    ];
                }
            }
        }

        return $suggestions;
    }
    public function getJointCourseSlots(object $currentSchool, string $semesterJointCourseId)
    {
        return JointCourseSlot::where("school_branch_id", $currentSchool->id)
            ->where("semester_joint_course_id", $semesterJointCourseId)
            ->with(['hall', 'teacher', 'course'])
            ->get();
    }
    public function deleteJointCourseSlot(object $currentSchool, string $jointCourseSlotId)
    {
        $jointCourseSlot = JointCourseSlot::where("school_branch_id", $currentSchool->id)
            ->where("id", $jointCourseSlotId)
            ->first();
        if (!$jointCourseSlot) {
            throw new AppException(
                "Joint Course Slot Not Found",
                404,
                "Not Found",
                "The Joint Course Your Trying to delete does not exist or has already been deleted."
            );
        }

        $jointCourseSlot->delete();

        return $jointCourseSlot;
    }
    private function generateAlignedSlots(string $rangeStart, string $rangeEnd, int $interval): array
    {
        $slots = [];

        [$startHour, $startMin] = array_map('intval', explode(':', $rangeStart));
        [$endHour, $endMin]     = array_map('intval', explode(':', $rangeEnd));

        $rangeStartMinutes = $startHour * 60 + $startMin;
        $rangeEndMinutes   = $endHour   * 60 + $endMin;

        $firstBoundary = (int) ceil($rangeStartMinutes / $interval) * $interval;

        for ($slotStart = $firstBoundary; $slotStart + $interval <= $rangeEndMinutes; $slotStart += $interval) {
            $slotEnd = $slotStart + $interval;

            $slots[] = [
                'start_time' => $this->minutesToTime($slotStart),
                'end_time'   => $this->minutesToTime($slotEnd),
            ];
        }

        return $slots;
    }
    private function hasConflict(string $startTime, string $endTime, Collection $busySlots): bool
    {
        foreach ($busySlots as $busy) {
            $busyStart = $this->timeToMinutes($busy->start_time);
            $busyEnd   = $this->timeToMinutes($busy->end_time);
            $slotStart = $this->timeToMinutes($startTime);
            $slotEnd   = $this->timeToMinutes($endTime);

            if ($slotStart < $busyEnd && $slotEnd > $busyStart) {
                return true;
            }
        }

        return false;
    }
    private function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', $time));
        return $hours * 60 + $minutes;
    }
    private function minutesToTime(int $minutes): string
    {
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return sprintf('%02d:%02d', $h, $m);
    }
    protected function validateNoSelfConflicts(array $slots): void
    {
        $byDay = [];

        foreach ($slots as $index => $slot) {
            $day = strtolower(trim($slot['day'] ?? ''));
            $startStr = $slot['start_time'] ?? null;
            $endStr   = $slot['end_time']   ?? null;

            if (!$day || !$startStr || !$endStr) {
                continue;
            }

            $start = Carbon::createFromTimeString($startStr);
            $end   = Carbon::createFromTimeString($endStr);

            if (!isset($byDay[$day])) {
                $byDay[$day] = [];
            }

            $byDay[$day][] = [
                'index'  => $index,
                'start'  => $start,
                'end'    => $end,
                'start_str' => $startStr,
                'end_str'   => $endStr,
            ];
        }

        foreach ($byDay as $day => $daySlots) {
            usort($daySlots, fn($a, $b) => $a['start'] <=> $b['start']);

            for ($i = 0; $i < count($daySlots); $i++) {
                for ($j = $i + 1; $j < count($daySlots); $j++) {
                    $s1 = $daySlots[$i];
                    $s2 = $daySlots[$j];

                    if ($s1['start']->lessThan($s2['end']) && $s1['end']->greaterThan($s2['start'])) {
                        throw new AppException(
                            "Self Conflict in Submitted Slots",
                            400,
                            "Invalid Request",
                            "Slot #" . ($s1['index'] + 1) . " ({$s1['start_str']}–{$s1['end_str']}) on {$day} "
                                . "overlaps with slot #" . ($s2['index'] + 1) . " ({$s2['start_str']}–{$s2['end_str']})."
                        );
                    }
                }
            }
        }
    }
}
