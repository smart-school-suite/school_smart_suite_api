<?php

namespace App\Constant\Constraint\Builders\SemesterTimetable;

use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintGuide;

class ConstraintBuilder
{
    private string $name;
    private string $program_name;
    private string $type;
    private string $code;
    private string $description;
    private array $examples = [];

    public static function make(): self
    {
        $instance = new self();
        return $instance;
    }

    public function name(string $value): self
    {
        $this->name = $value;
        return $this;
    }

    public function programName(string $value): self
    {
        $this->program_name = $value;
        return $this;
    }

    public function type(string $value): self
    {
        $this->type = $value;
        return $this;
    }

    public function code(string $value): self
    {
        $this->code = $value;
        return $this;
    }

    public function description(string $value): self
    {
        $this->description = $value;
        return $this;
    }


    public function examples(array $examples): self
    {
        $this->examples = $examples;
        return $this;
    }

    public function build(): ConstraintGuide
    {
        return new ConstraintGuide(
            $this->name,
            $this->program_name,
            $this->type,
            $this->code,
            $this->description,
            $this->examples
        );
    }
}
