<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Graph;

use App\Constant\Violation\SemesterTimetable\Hall\HallBusy;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherBusy;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherUnavailable;

class Node
{
    public string $id;
    public string $type;
    public array $meta;
    public string $category;
    public function __construct(string $id, string $type, array $meta = [])
    {
        $this->id = $id;
        $this->type = $type;
        $this->meta = $meta;
        $this->category = $this->resolveCategory($type);
    }

    protected function resolveCategory($type): string {
         return match ($type) {
            TeacherBusy::KEY, HallBusy::KEY, TeacherUnavailable::KEY => 'resource',
            default => 'structural',
        };
    }
}
