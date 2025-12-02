<?php

namespace App\Builders\Pdf\Student;

use App\Builders\Pdf\BasePdfBuilder;
use App\Models\Student;

class StudentPdfBuilder extends BasePdfBuilder
{
public function getData(): array
    {
        $query = Student::query()
            ->with(['specialty', 'level', 'guardian', 'department', 'studentBatch'])
            ->select([
                'id', 'name', 'first_name', 'last_name', 'DOB', 'gender',
                'phone_one', 'phone_two', 'email', 'level_id', 'specialty_id',
                'department_id', 'guardian_id', 'student_batch_id',
                'payment_format', 'school_branch_id'
            ]);

        if (!empty($this->context['school_branch_id'])) {
            $query->where('school_branch_id', $this->context['school_branch_id']);
        }

        if (!empty($this->filters['level_ids'])) {
            $query->whereIn('level_id', $this->filters['level_ids']);
        }

        if (!empty($this->filters['department_ids'])) {
            $query->whereIn('department_id', $this->filters['department_ids']);
        }

        if (!empty($this->filters['specialty_ids'])) {
            $query->whereIn('specialty_id', $this->filters['specialty_ids']);
        }

        if (!empty($this->filters['student_batch_ids'])) {
            $query->whereIn('student_batch_id', $this->filters['student_batch_ids']);
        }

        $students = $query->get();

        $students = $students->map(fn ($student) => [
                'id'              => $student->id,
                'name'            => $student->name,
                'first_name'      => $student->first_name,
                'last_name'       => $student->last_name,
                'email'           => $student->email,
                'phone_one'       => $student->phone_one,
                'phone_two'       => $student->phone_two,
                'gender'          => $student->gender,
                'DOB'             => $student->DOB,
                'specialty_name'  => $student->specialty?->specialty_name ?? '—',
                'level_name'      => $student->level?->name ?? '—',
                'level_number'      => $student->level?->level ?? '—',
                'guardian_name'   => $student->guardian?->name ?? '—',
                'department_name' => $student->department?->name ?? '—',
                'batch_name'      => $student->studentBatch?->name ?? '—',
            ]);

        $columns = $this->options['columns'] ?? [
            'name',
            'email',
            'specialty_name',
            'level_name',
            'guardian_name',
        ];

        return [
            'title'    => $this->title ?? 'Student List',
            'columns'  => $columns,
            'students' => $students,
        ];
    }

    public function getView(): string
    {
        return 'pdf.student';
    }
}
