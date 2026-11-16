<?php

namespace App\Events\Student;

use App\Models\Student;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * A new student was admitted to a school branch. Dispatched inside the
 * transaction that created the student; its listeners run synchronously in
 * that transaction, so the student and their fee records are committed or
 * rolled back together.
 */
class StudentRegistered
{
    use Dispatchable;

    public function __construct(public readonly Student $student) {}
}
