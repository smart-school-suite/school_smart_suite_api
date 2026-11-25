<?php

namespace App\Http\Requests\Course;

use App\Http\Requests\Import\ImportFileRequest;

class ImportCourseRequest extends ImportFileRequest
{
    protected function requiredFields(): array
    {
        return [
            'course_code' => 'course code',
            'course_title' => 'course title',
            'course_credit' => 'course credit',
            'semester' => 'semester',
            'specialty' => 'specialty',
        ];
    }

    protected function requiredGroups(): array
    {
        return ['course_types' => 'course types'];
    }
}
