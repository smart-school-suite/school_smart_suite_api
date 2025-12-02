<?php

namespace App\Builders\Pdf;

use Exception;
use Illuminate\Support\Str;
class PdfBuilderFactory
{
    protected static $builders = [
        'student' => Student\StudentPdfBuilder::class,
        'teacher' => Teacher\TeacherPdfBuilder::class,
        // 'parent'  => ParentBuilder\ParentPdfBuilder::class,
        // add more here as needed
    ];

    public static function make(string $type): BasePdfBuilder
    {
        $type = strtolower(trim($type));

        // Option 1: Explicit mapping (recommended for clarity & performance)
        if (isset(static::$builders[$type])) {
            return new static::$builders[$type];
        }

        // Option 2: Fallback to dynamic discovery (optional)
        $studly = Str::studly($type);
        $class = "App\\Builders\\Pdf\\{$studly}\\{$studly}PdfBuilder";

        if (!class_exists($class, true)) {
            throw new Exception("PDF Builder not found for type: '{$type}'. Looked for: {$class}");
        }

        return new $class;
    }
}
