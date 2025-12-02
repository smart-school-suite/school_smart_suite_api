<?php

namespace App\Builders\Pdf\Teacher;

use App\Builders\Pdf\BasePdfBuilder;
use App\Models\Teacher;

class TeacherPdfBuilder extends BasePdfBuilder
{
    public function getData(): array
    {
        $query = Teacher::query()
            ->select([
                'id',
                'school_branch_id',
                'email',
                'name',
                'phone_one',
                'phone_two',
                'first_name',
                'last_name',
                'address',
                'gender'
            ]);

        if (!empty($this->context['school_branch_id'])) {
            $query->where('school_branch_id', $this->context['school_branch_id']);
        }

        $teachers = $query->get();

        $teachers = $teachers->map(fn($teacher) => [
            'id'              => $teacher->id,
            'name'            => $teacher->name,
            'first_name'      => $teacher->first_name,
            'last_name'       => $teacher->last_name,
            'email'           => $teacher->email,
            'phone_one'       => $teacher->phone_one,
            'gender'          => $teacher->gender,
            'address'             => $teacher->address,
        ]);

        $columns = $this->options['columns'] ?? [
            'name',
            'email',
            'gender',
            'phone_one'
        ];

        return [
            'title'    => $this->title ?? 'Teacher List',
            'columns'  => $columns,
            'teachers' => $teachers,
        ];
    }

    public function getView(): string
    {
        return 'pdf.teacher';
    }
}
