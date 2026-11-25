<?php

namespace App\Http\Requests\TeacherCourse;

use App\Http\Requests\Import\ImportFileRequest;

class ImportTeacherCourseRequest extends ImportFileRequest
{
    protected function requiredFields(): array
    {
        return [
            'teacher' => 'teacher name',
            'course' => 'course',
        ];
    }
}
