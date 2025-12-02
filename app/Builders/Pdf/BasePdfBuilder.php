<?php

namespace App\Builders\Pdf;

abstract class BasePdfBuilder
{
    protected ?string $title = null;
    protected array $filters = [];
    protected array $options = [];
    protected array $context = [];

    public function setTitle(?string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function setFilters(array $filters): static
    {
        $this->filters = $filters;
        return $this;
    }

    public function setOptions(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function setContext(array $context): static
    {
        $this->context = $context;
        return $this;
    }

    abstract public function getData(): array;

    abstract public function getView(): string;
}
