<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Normalization\Contracts;

interface ResolverContract
{
    public function supports(string $type): bool;
    public function normalize($scenario);
}
