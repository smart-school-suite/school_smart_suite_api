<?php

namespace App\Constant\Timetable\Constraints\Guides;

class ConstraintGuideBuilder
{
    private string $key;
    private string $intent;
    private string $whenToUse;
    private array $requiredFields = [];
    private array $optionalFields = [];
    private array $howToUse = [];
    private array $examples = [];

    public static function make(string $key): self
    {
        $instance = new self();
        $instance->key = $key;
        return $instance;
    }

    public function intent(string $value): self
    {
        $this->intent = $value;
        return $this;
    }

    public function whenToUse(string $value): self
    {
        $this->whenToUse = $value;
        return $this;
    }

    public function requiredFields(array $fields): self
    {
        $this->requiredFields = $fields;
        return $this;
    }

    public function optionalFields(array $fields): self
    {
        $this->optionalFields = $fields;
        return $this;
    }

    public function howToUse(array $rules): self
    {
        $this->howToUse = $rules;
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
            $this->key,
            $this->intent,
            $this->whenToUse,
            $this->requiredFields,
            $this->optionalFields,
            $this->howToUse,
            $this->examples
        );
    }
}
