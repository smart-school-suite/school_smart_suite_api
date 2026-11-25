<?php

namespace App\Http\Requests\JointCourse;

use App\Http\Requests\Import\ImportFileRequest;

class ImportJointCourseRequest extends ImportFileRequest
{
    protected function requiredFields(): array
    {
        return [
            'course_code' => 'course code',
            'course_title' => 'course title',
            'course_credit' => 'course credit',
            'semester' => 'semester',
        ];
    }

    protected function requiredGroups(): array
    {
        return [
            'course_types' => 'course types',
            'specialties' => 'specialties',
        ];
    }
}
