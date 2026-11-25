<?php

namespace App\Http\Requests\GradeScale;

use App\Http\Requests\Import\ImportFileRequest;

class ImportGradeScaleRequest extends ImportFileRequest
{
    protected function requiredFields(): array
    {
        return [
            'grade_title' => 'grade title',
            'grade_max_score' => 'grade max score',
            'grade' => 'grade',
            'min_score' => 'min score',
            'max_score' => 'max score',
            'grade_points' => 'grade points',
            'result' => 'result',
            'resit_result' => 'resit result',
            'performance' => 'performance',
        ];
    }
}
